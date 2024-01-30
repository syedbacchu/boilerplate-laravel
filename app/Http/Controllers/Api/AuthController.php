<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\VerifyEmailRequest;
use App\Http\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    private $service;
    public function __construct()
    {
        $this->service = new AuthService();
    }
    
    // common setting
    public function commonSetting()
    {
        try{
            $setting = allsetting();

            $data['app_title']              = $setting['app_title'] ?? '';
            $data['tag_title']              = $setting['tag_title'] ?? '';
            $data['company_email']          = $setting['company_email'] ?? '';
            $data['company_address']        = $setting['company_address'] ?? '';
            $data['helpline']               = $setting['helpline'] ?? '';
            $data['logo']                   = (isset($setting['logo']) && !empty($setting['logo'])) ? asset($setting['logo']) : '';
            $data['favicon']                = (isset($setting['favicon']) && !empty($setting['favicon'])) ? asset($setting['favicon']) : '';
            $data['copyright_text']         = $setting['copyright_text'] ?? '';
            $data['pagination_count']       = $setting['pagination_count'] ?? '';
            $data['currency']               = $setting['currency'] ?? '';
            $data['lang']                   = $setting['lang'] ?? '';
            
            return responseJsonData(true,__('Data get successfully'),$data);
        } catch(\Exception $e) {
            return responseJsonData(false);
        } 
    }

    // register
    public function register(RegisterRequest $request){
        try {
            $response = $this->service->registerProcess($request);
            return responseJsonData($response['success'],$response['message'],$response['data']);
        } catch(\Exception $e) {
            storeException('register', $e->getMessage());
            return responseJsonData(false,__('Something went wrong'));
        }
    }

    // login
    public function login(LoginRequest $request){
        try {
            $response = $this->service->loginProcess($request);
            return responseJsonData($response['success'],$response['message'],$response['data']);
        } catch(\Exception $e) {
            storeException('login', $e->getMessage());
            return responseJsonData(false,__('Something went wrong'));
        }
    }

    // verify email
    public function verifyEmail(VerifyEmailRequest $request){
        try {
            $response = $this->service->verifyEmailProcess($request);
            return responseJsonData($response['success'],$response['message'],$response['data']);
        } catch(\Exception $e) {
            storeException('verifyEmail', $e->getMessage());
            return responseJsonData(false,__('Something went wrong'));
        }
    }

    // forgot Password
    public function forgotPassword(ForgotPasswordRequest $request){
        try {
            $response = $this->service->sendForgotMailProcess($request);
            return responseJsonData($response['success'],$response['message'],$response['data']);
        } catch(\Exception $e) {
            storeException('forgotPassword', $e->getMessage());
            return responseJsonData(false,__('Something went wrong'));
        }
    }
    // reset Password
    public function resetPassword(ResetPasswordRequest $request){
        try {
            $response = $this->service->passwordResetProcess($request);
            return responseJsonData($response['success'],$response['message'],$response['data']);
        } catch(\Exception $e) {
            storeException('resetPassword', $e->getMessage());
            return responseJsonData(false,__('Something went wrong'));
        }
    }

    // reset Password
    public function logout(Request $request){
        try {
            Session::flush();
            Cookie::queue(Cookie::forget('accesstokenvalue'));
            $user = Auth::user()->token();
            $user->revoke();
            return responseJsonData(true,__('Logout successful'),[]);
        } catch(\Exception $e) {
            storeException('logout', $e->getMessage());
            return responseJsonData(false,__('Something went wrong'));
        }
    }

    
}
