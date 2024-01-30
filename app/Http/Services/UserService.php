<?php

namespace App\Http\Services;

use App\Http\Repository\UserRepository;
use App\Model\ThirdPartyKycDetails;
use App\Model\UserVerificationCode;
use App\Model\VerificationDetails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

class UserService
{
    private $logger;
    private $repository;
    private $smsService;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    // user profile
    public function userProfile($userId)
    {
        $response = $this->repository->userProfile($userId);
        return $response;
    }

    // user profile update
    public function userProfileUpdate($request,$userId)
    {
        $response = $this->repository->profileUpdate($request,$userId);
        return $response;
    }

    public function userProfileUpdateApi($request,$userId)
    {
        $response = $this->repository->profileUpdate($request,$userId);
        return $response;
    }
    // user change password
    public function userChangePassword($request,$userId)
    {
        $response = $this->repository->passwordChange($request,$userId);
        return $response;
    }

    // send phone verification sms
    public function sendPhoneVerificationSms($user)
    {
        if (env('APP_MODE') == 'demo') {
                return ['success' => false, 'message' => __('Currently disable only for demo')];
            }
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            if (!empty($user->phone)) {
                $key = randomNumber(6);
                $code = UserVerificationCode::create([
                    'user_id' => $user->id,
                    'code' => $key,
                    'expired_at' => date('Y-m-d', strtotime('+1 days')),
                    'status' => STATUS_PENDING,
                    'type' => CODE_TYPE_PHONE
                ]);

                $text = __('Your verification code id ') . ' ' . $code->code;
                $number = $user->phone;
                $sendSms =$this->smsService->sendSMS($number, $text);
                // $sendSms = $this->smsService->send("+".$number, $text);
                $response = [
                    'success' => true,
                    'message' => __('We sent a verification code in your phone please input this code in this box')
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Before verify please add your mobile number first')
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('sendPhoneVerificationSms', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }

    // send phone verification sms
    public function phoneVerifyProcess($request, $user)
    {
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        DB::beginTransaction();
        try {
            if(isset($request->verify_code)) {
                $verify = UserVerificationCode::where(['user_id' => $user->id])
                    ->where('code', $request->verify_code)
                    ->where(['status' => STATUS_PENDING, 'type' => CODE_TYPE_PHONE])
                    ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                    ->first();
                if ($verify) {
                    $user->phone_verified = 1;
                    $user->save();
                    UserVerificationCode::where(['id' => $verify->id])->delete();
                    $response = [
                        'success' => true,
                        'message' => __('Phone verified successful')
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => __('Verify code expired or not found')
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => __('Verify code can not be empty')
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('sendPhoneVerificationSms', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }

        DB::commit();
        return $response;
    }

  

    // google 2fa setup process
    public function setupGoogle2fa($request)
    {
        if (env('APP_MODE') == 'demo') {
            return ['success' => false, 'message' => __('Currently disable only for demo')];
        }
        $response['success'] = false;
        $response['data'] = '';
        $response['message'] = __('Invalid Request');
        try {
            if(empty($request->code)) {
                $response = [
                    'success' => false,
                    'data' => '',
                    'message' => __('Google authentication code can not be empty')
                ];
                return $response;
            }

            $user = Auth::user();
            if($request->setup == 'remove') {
                if(empty($user->google2fa_secret)) {
                    $response = [
                        'success' => false,
                        'data' => '',
                        'message' => __('Your gAuth is not setup yet, so before remove you must setup gauth first')
                    ];
                } else {
                    $valid = $this->checkGoogle2fa($user->google2fa_secret,$request->code);
                    if ($valid['success'] == false) {
                        $response = [
                            'success' => false,
                            'data' => '',
                            'message' => $valid['message']
                        ];
                    } else {
                        $user->google2fa_secret = null;
                        $user->g2f_enabled = '0';
                        $user->save();
                        $response = [
                            'success' => false,
                            'data' => $user,
                            'message' => __('Google authentication code removed successfully')
                        ];
                    }
                }
            } else {
                if(!empty($user->google2fa_secret)) {
                    $response = [
                        'success' => false,
                        'data' => '',
                        'message' => __('Your gAuth is already setup')
                    ];
                    return $response;
                } else {
                    $valid = $this->checkGoogle2fa($request->google2fa_secret,$request->code);
                    if ($valid['success'] == false) {
                        $response = [
                            'success' => false,
                            'data' => '',
                            'message' => $valid['message']
                        ];
                    } else {
                        $user->google2fa_secret = $request->google2fa_secret;
                        $user->save();
                        $response = [
                            'success' => true,
                            'data' => $user,
                            'message' => __('Google authentication code added successfully')
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->log('setupGoogle2fa', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

    // check google 2fa
    public function checkGoogle2fa($google2fa_secret,$code)
    {
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($google2fa_secret, $code);
        if ($valid) {
            $data['success'] = true;
            $data['message'] = __('Success');
        } else {
            $data['success'] = false;
            $data['message'] = __('Google authentication code is invalid');
        }
        return $data;
    }

    // language list
    public function languageList()
    {
        $response['success'] = true;
        $response['message'] = __('Success');
        $list = [];
        foreach (language() as $val) {
            $list[] = [
                'key' => $val,
                'lang' => langName($val)
            ];
        }
        $response['data'] = $list;

        return $response;
    }

    // language save
    public function languageSetup($request)
    {
        try {
            $user =  Auth::user();
            if ($request->language) {
                $user->language = $request->language;
                $user->save();
                $response = [
                    'success' => true,
                    'data' => $user,
                    'message' => __('Language changed successfully')
                ];
            } else {
                $response = [
                    'success' => false,
                    'data' => '',
                    'message' => __('Please select a language')
                ];
            }
        } catch (\Exception $e) {
            $this->logger->log('languageSetup', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

    // setup Google2fa Login
    public function setupGoogle2faLogin($user)
    {
        try {
            if (!empty($user->google2fa_secret)) {
                if ($user->g2f_enabled == 0) {
                    $user->g2f_enabled = '1';
                    Session::put('g2f_checked', true);
                    $message = __('Google two factor authentication is enabled');
                } else {
                    $user->g2f_enabled = '0';
                    Session::forget('g2f_checked');
                    $message = __('Google two factor authentication is enabled');
                }
                $user->update();
                $response = [
                    'success' => true,
                    'data' => $user,
                    'message' => $message
                ];
            } else {
                $response = [
                    'success' => false,
                    'data' => '',
                    'message' => __('For using google two factor authentication,please setup your authentication')
                ];
            }
        } catch (\Exception $e) {
            $this->logger->log('setupGoogle2faLogin', $e->getMessage());
            $response = [
                'success' => false,
                'data' => '',
                'message' => __('Something went wrong')
            ];
        }

        return $response;
    }

   

    public function deleteData($id){
        try {
            $item = User::where(['unique_code' => $id])->first();
            if ($item) {
                $delete = User::where(['unique_code' => $id])->delete();
                return responseData(true, __('Data deleted successfully'));
            } else {
                return responseData(false, __('Data not found'));
            }
        } catch(\Exception $e) {
            storeException('delete user ex', $e->getMessage());
            return responseData(false, __('Something went wrong'));
        }
    }

    // save data
    public function saveItemData($request)
    {
        $response = responseData(false);
        try {
            $item='';
            $oldImg = '';
            if($request->edit_id) {
                $data = $request->except(['_token', 'photo', 'edit_id','password_confirmation','password','user_type']);
                $item = User::where(['id' => $request->edit_id])->first();
                if(empty($item)) {
                    return responseData(false,__('Data not found'));
                }
                if(!empty($item->photo)) {
                    $oldImg = $item->photo;
                }
            } else {
                $data = $request->except(['_token', 'photo','password_confirmation','password','user_type']);
                $data['unique_code'] = randomNumber(14);
                if ($request->user_type == 'admin') {
                    $data['role_module'] = ROLE_ADMIN;
                } 
                $data['email_verified'] = STATUS_ACTIVE;
            }

            if($request->photo) {
                $data['photo'] = uploadFileStorage($request->photo,IMAGE_PATH_USER,$oldImg,100,100);
            }
            if(!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }
            if($request->edit_id) {
                User::where(['id' => $request->edit_id])->update($data);
                if ($request->user_type == 'admin') {
                    $response = responseData(true,__('Admin updated successfully'));
                } else {
                    $response = responseData(true,__('User updated successfully'));
                }
            } else {
                User::create($data);
                if ($request->user_type == 'admin') {
                    $response = responseData(true,__('New admin added successfully'));
                } else {
                    $response = responseData(true,__('New user added successfully'));
                }  
            }
        } catch (\Exception $e) {
            storeException('save user', $e->getMessage());
        }
        return $response;
    }
}
