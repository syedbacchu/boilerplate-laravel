<?php
namespace App\Http\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public static function createUser($request){

        $data=[
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
            'role'=>USER_ROLE_USER,
        ];
       return User::create($data);
    }
    public static function updatePassword($request,$user_id){
       return User::where(['id'=>$user_id])->update(['password'=>bcrypt($request->password)]);
    }
    public static function apiUpdatePassword($request,$user_id){
        return User::where(['id'=>$user_id])->update(['password'=>bcrypt($request->new_password)]);
    }

    // update user profile
    public function profileUpdate($request, $user_id)
    {
        $response['success'] = false;
        $response['data'] = (object)[];
        $response['message'] = __('Invalid Request');
        try {
            $user = User::find($user_id);
            $userData = [];
            if ($user) {
                
                $userData = [
                    'email' => $request['email'],
                    'name' => $request['name'],
                    'phone' => $request['phone'],
                ];
               

                if (!empty($request['photo'])) {
                    $old_img = '';
                    if (!empty($user->photo)) {
                        $old_img = $user->photo;
                    }
                    $userData['photo'] = uploadFileStorage($request['photo'], IMAGE_PATH_USER, $old_img);
                }
                if ($user->phone != $request->phone){
                    $userData['phone'] =  $request->phone;
                    $userData['phone_verified'] = 0;
                }

                $affected_row = User::where('id', $user_id)->update($userData);
                if ($affected_row) {
                    $user = User::find($user_id);
                    $user->photo = showUserImage(VIEW_IMAGE_PATH_USER,$user->photo);
                    $response['success'] = true;
                    $response['data'] = $user;
                    $response['message'] = __('Profile updated successfully');
                }
            } else {
                $response['success'] = false;
                $response['data'] = (object)[];
                $response['message'] = __('Invalid User');
            }
        } catch (\Exception $e) {
            storeException('profileUpdate', $e->getMessage());
            $response = [
                'success' => false,
                'data' => (object)[],
                'message' => $e->getMessage()
            ];
            return $response;
        }

        return $response;
    }
    
    public function profileUpdateApi($request, $user_id)
    {
        $response['success'] = false;
        $response['data'] = (object)[];
        $response['message'] = __('Invalid Request');
        try {
            $user = User::find($user_id);
            $userData = [];
            if ($user) {
                
                $userData = [];

                if (!empty($request['photo'])) {
                    $old_img = '';
                    if (!empty($user->photo)) {
                        $old_img = $user->photo;
                    }
                    $userData['photo'] = uploadFileStorage($request['photo'], IMAGE_PATH_USER, $old_img);
                }
                if(empty($userData)) return responseData(false, __("Nothing to update!"));

                $affected_row = User::where('id', $user_id)->update($userData);
                if ($affected_row) {
                    $user = User::find($user_id);
                    $user->photo = showUserImage(VIEW_IMAGE_PATH_USER,$user->photo);
                    $response['success'] = true;
                    $response['data'] = $user;
                    $response['message'] = __('Profile updated successfully');
                }
            } else {
                $response['success'] = false;
                $response['data'] = (object)[];
                $response['message'] = __('Invalid User');
            }
        } catch (\Exception $e) {
            storeException('profileUpdateApi', $e->getMessage());
            $response = [
                'success' => false,
                'data' => (object)[],
                'message' => $e->getMessage()
            ];
            return $response;
        }

        return $response;
    }

    public function passwordChange($request, $user_id)
    {
        $response['data'] = [];
        $response['success'] = false;
        $response['message'] = __('Invalid Request');
        try {
            $user = User::find($user_id);
            if ($user) {
                $old_password = $request['old_password'];
                if (Hash::check($old_password, $user->password)) {
                    if(!Hash::check($request->password,$user->password)) {
                        $user->password = bcrypt($request['password']);
                        $user->save();
                        $affected_row = $user->save();

                        if (!empty($affected_row)) {
                            $response['success'] = true;
                            $response['message'] = __('Password changed successfully.');
                        }
                    } else {
                        $response['success'] = false;
                        $response['message'] = __('You already used password');
                    }
                } else {
                    $response['success'] = false;
                    $response['message'] = __('Incorrect old password');
                }
            } else {
                $response['success'] = false;
                $response['message'] = __('Invalid user');
            }
        } catch (\Exception $e) {
            storeException('passwordChange', $e->getMessage());
            $response = [
                'success' => false,
                'message' => __('Something went wrong')
            ];
        }
        return $response;
    }

    // user profile
    public function userProfile($user_id)
    {
        $response = responseData(false);
        try {
            if (isset($user_id)) {
                $user = User::with('roles')->select(
                    'id',
                    'username',
                    'name',
                    'email',
                    'unique_code',
                    'role',
                    'role_module',
                    'status',
                    'phone',
                    'photo',
                    'g2f_enabled',
                    'google2fa_secret',
                    'email_verified',
                    'language',
                    'created_at',
                    'updated_at',
                    'email_enabled',
                    'phone_enabled',
                    'push_notification_status',
                    'email_notification_status',
                    
                )->where('id',$user_id)->first();

                $data['user'] = $user;
                $data['user']->photo = showUserImage(VIEW_IMAGE_PATH_USER,$user->photo);
                $response = responseData(true,__('User get successfully'),$data['user']);
                
            } else {
                $response = responseData(false,__('User not found'));
            }
        } catch (\Exception $e) {
            storeException('userProfile', $e->getMessage());
        }
        return $response;
    }

   

}
