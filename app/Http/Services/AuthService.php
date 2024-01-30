<?php

namespace App\Http\Services;

use App\Http\Repository\AuthRepositories;
use App\Models\UserVerificationCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

use function PHPSTORM_META\type;

class AuthService extends BaseService
{
    public $repository;
    public $emailService;
    public function __construct()
    {
        $this->repository =  new AuthRepositories;
        $this->emailService = new MailService;
        parent::__construct($this->repository);

    }

    // register process
    public function registerProcess($request){
        DB::beginTransaction();
        $response = responseData(false);
        try {
            
            $inputData = $request->except(['_token','confirm_password']);
            $inputData['password'] = Hash::make($request->password);
            $inputData['unique_code'] = randomNumber(14);
            $mail_key = randomNumber(6);
            $user = User::create($inputData);
            if($user) {
                $userVerificationData = [
                    'user_id' => $user->id,
                    'code' => $mail_key,
                    'expired_at' => date('Y-m-d', strtotime('+15 days'))
                ];
                UserVerificationCode::create($userVerificationData);
                DB::commit();
                $this->sendVerifyemail($user, $mail_key);
                $response = responseData(true, __('Registration successful. Please verify your email'), $user);
            } else {
                $response = responseData(false, __('Registration failed'));
            }
            
        } catch(\Exception $e) {
            DB::rollBack();
            storeException('registerProcess', $e->getMessage());
        }
        
        return $response;
    }

    // login process
    public function loginProcess($request){
        try {
            $response = responseData(false);
            $message = '';
            $data = [];
            $data['email_verified'] = 1;
            $user = User::where('email', $request->email)->first();
            if (!empty($user)) {
                $data['email_verified'] = 1;
                if($user->role_module == MODULE_USER) {
                    if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                        
                        //Check email verification
                        if ($user->status == STATUS_SUCCESS) {
                            if (!empty($user->email_verified)) {
                                $data['token'] = $user->createToken($request->email)->accessToken;
                                $message = __('Login successful');
                                $data['email_verified'] = $user->email_verified;
                                
                                $data['user'] = $user;
                                $data['user']->photo = showUserImage(VIEW_IMAGE_PATH_USER,$user->photo);
                                $response = responseData(true,$message,$data);
                                return $response;
                            } else {
                                $existsToken = User::join('user_verification_codes','user_verification_codes.user_id','users.id')
                                    ->where('user_verification_codes.user_id',$user->id)
                                    ->whereDate('user_verification_codes.expired_at' ,'>=', Carbon::now()->format('Y-m-d'))
                                    ->first();
                                if(!empty($existsToken)) {
                                    $mail_key = $existsToken->code;
                                } else {
                                    $mail_key = randomNumber(6);
                                    UserVerificationCode::create(['user_id' => $user->id, 'code' => $mail_key, 'status' => STATUS_PENDING, 'expired_at' => date('Y-m-d', strtotime('+15 days'))]);
                                }
                                $data['email_verified'] = $user->email_verified;
                                $this->sendVerifyemail($user, $mail_key);

                                $response['success'] = false;
                                $response['message'] = __('Your email is not verified yet. Please verify your mail.');
                                Auth::logout();

                                $response['data'] = $data;
                                return $response;
                                
                            }
                        } else {
                            $response['success'] = false;
                            $response['message'] = __("Your account has been deactivated. please contact support team to active again");
                            $response['data'] = $data;
                            return $response;
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = __("Email or Password doesn't match");
                        $response['data'] = $data;
                        return $response;
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = __("You have no login access");
                    Auth::logout();
                    $response['data'] = $data;
                    return $response;
                }
            } else {
                $response['success'] = false;
                $response['message'] =  __("Email or Password doesn't match");
                $response['data'] = $data;
                return $response;
            }
        } catch(\Exception $e) {
            storeException('loginProcess', $e->getMessage());
            return responseData(false);
        }
        return $response;
    }

    // send verify email
    public function sendVerifyemail($user, $mail_key)
    {
        try {
            $userName = $user->name;
            $userEmail = $user->email;
            $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
            $subject = __('Email Verification | :companyName', ['companyName' => $companyName]);
            $data['user'] = $user;
            $data['token'] = $mail_key;
            $template = 'email.verifyapp';
            
            $this->emailService->send($template, $data, $userEmail, $userName, $subject);
        } catch (\Exception $e) {
            storeException('sendVerifyemail', $e->getMessage());
        }
    }

    // send forgot mail process
    public function sendForgotMailProcess($request)
    {
        $response = responseData(false);
        $user = User::where(['email' => $request->email])->first();

        if ($user) {
            DB::beginTransaction();
            try {
                $key = randomNumber(6);
                $existsToken = User::join('user_verification_codes','user_verification_codes.user_id','users.id')
                    ->where('user_verification_codes.user_id',$user->id)
                    ->whereDate('user_verification_codes.expired_at' ,'>=', Carbon::now()->format('Y-m-d'))
                    ->first();
                if(!empty($existsToken)) {
                    $token = $existsToken->code;
                } else {
                    UserVerificationCode::create(['user_id' => $user->id, 'code'=>$key,'expired_at' => date('Y-m-d', strtotime('+15 days')), 'status' => STATUS_PENDING]);
                    $token = $key;
                }

                $this->sendEmail($user,$token);
                $data['message'] = __('Mail sent successfully with password reset code, if mail is correct');
                $data['success'] = true;
                Session::put(['resend_email'=>$user->email]);
                DB::commit();

                $response = responseData(true,$data['message']);
            } catch (\Exception $e) {
                DB::rollBack();
                storeException('sendForgotMailProcess', $e->getMessage());
                $response = responseData(false);
            }
        } else {
            $response = responseData(false,__('You should get email for reseting password, if mail is correct'));
        }

        return $response;
    }

    // send forgot mail
    public function sendEmail($user, $mail_key,$type=null)
    {
        try {
            $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
            $user_data = [
                'user' => $user,
                'token' => $mail_key,
            ];
            $userName = $user->name;
            $userEmail = $user->email;
            if (!empty($type) && $type == 'verify') {
                $subject = __('Email Verify | :companyName', ['companyName' => $companyName]);
                $template = 'email.verifyWeb';
                
                $this->emailService->send($template, $user_data, $userEmail, $userName, $subject);
            } else {
                $subject = __('Forgot Password | :companyName', ['companyName' => $companyName]);
                $template = 'email.password_reset';
                $this->emailService->send($template, $user_data, $userEmail, $userName, $subject);
            }

        } catch (\Exception $e) {
            storeException('sendEmail '.$type, $e->getMessage());
        }
    }

    // reset password process
    public function passwordResetProcess($request)
    {
        $response = responseData(false);
        try {
            $vf_code = UserVerificationCode::where(['code' => $request->token, 'status' => STATUS_PENDING, 'type' => CODE_TYPE_EMAIL])
                ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                ->first();

            if (!empty($vf_code)) {
                $user = User::where(['id'=> $vf_code->user_id, 'email'=>$request->email])->first();
                if (empty($user)) {
                    $response = responseData(false,__('User not found'));
                }
                $data_ins['password'] = hash::make($request->password);
                $data_ins['email_verified'] = STATUS_SUCCESS;
                if(!Hash::check($request->password,User::find($vf_code->user_id)->password)) {

                    User::where(['id' => $vf_code->user_id])->update($data_ins);
                    UserVerificationCode::where(['id' => $vf_code->id])->delete();

                    $data['success'] = 'success';
                    $data['message'] = __('Password Reset Successfully');

                    $response = responseData(true,$data['message']);
                } else {
                    $data['success'] = 'dismiss';
                    $data['message'] = __('You already used this password');
                    $response = responseData(false,$data['message']);
                }
            } else {
                $data['success'] = 'dismiss';
                $data['message'] = __('Invalid code');

                $response = responseData(false,$data['message']);
            }
        } catch (\Exception $e) {
            storeException('passwordResetProcess', $e->getMessage());
        }

        return $response;
    }

  
    //resend verification code to mail
    public function resendVerifyEmailCode($request)
    {
        try{
            $mail_key = $this->repository->generate_email_verification_key();
            $user = User::where(['email' => $request->email])->first();
            if ($user) {
                $userVerificationData = [
                    'user_id' => $user->id,
                    'code' => $mail_key,
                    'expired_at' => date('Y-m-d', strtotime('+15 days'))
                ];

                $this->repository->createUserVerification($userVerificationData);

                $this->sendVerifyemail($user, $mail_key);

            }
            $response = ['success'=>true, 'message'=>'Verification Code send to your mail'];
        } catch (\Exception $e) {
            storeException('resendVerifyEmailCode', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    // verify email
    public function verifyEmailProcess($request)
    {
        $response = responseData(false);
        try {
            if($request->token) {
                $token = explode('email', $request->token);
                $user = User::where(['email' => decrypt($token[1])])->first();
            } else {
                $user = User::where(['email' => $request->email])->first();
            }
            if (!empty($user)) {
                if($request->token) {
                    $verify = UserVerificationCode::where(['user_id' => $user->id])
                        ->where('code', decrypt($token[0]))
                        ->where(['status'=> STATUS_PENDING,'type' => CODE_TYPE_EMAIL])
                        ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                        ->first();
                } else {
                    $verify = UserVerificationCode::where(['user_id' => $user->id])
                        ->where('code', $request->verify_code)
                        ->where(['status' => STATUS_PENDING, 'type' => CODE_TYPE_EMAIL])
                        ->whereDate('expired_at', '>', Carbon::now()->format('Y-m-d'))
                        ->first();
                }

                if ($verify) {
                    $check = $user->update(['email_verified' => STATUS_SUCCESS]);
                    if ($check) {
                        UserVerificationCode::where(['user_id' => $user->id, 'id' => $verify->id])->delete();
                        $response = responseData(true,__('Verify successful,you can login now'));
                    }
                } else {
                    Auth::logout();
                    $response = responseData(false,__('Your verify code was expired,you can generate new one'));
                }
            } else {
                $response = responseData(false,__('Your email not found or token expired'));
            }
        } catch (\Exception $e) {
            storeException('verifyEmailProcess', $e->getMessage());
        }
        return $response;
    }

    // g2fa verify process
    public function g2fVerifyProcess($request)
    {
        try {
            $user = User::where('id',$request->user_id)->first();
            if ($request->code) {
                $google2fa = new Google2FA();
                $google2fa->setAllowInsecureCallToGoogleApis(true);
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code, 8);

                if ($valid){
                    Session::put('g2f_checked',true);
                    $token = $user->createToken($user->email)->accessToken;
                    $data['access_token'] = $token;
                    $data['access_type'] = 'Bearer';
                    $data['user'] = $user;
                    $data = ['success' => true, 'message' => __('Code verify success'), 'data' => $data];
                } else {
                    $data = ['success' => false, 'message' => __('Code doesn\'t match') , 'data' => []];
                }
            } else {
                $data = ['success' => false, 'message' => __('Code is required'), 'data' => []];
            }

        } catch (\Exception $e) {
            storeException('g2fVerifyProcess', $e->getMessage());
            $data = ['success' => false, 'message' => __('Something went wrong'), 'data' => []];
        }
        return $data;
    }
}
