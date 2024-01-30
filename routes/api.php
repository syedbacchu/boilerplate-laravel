<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['namespace'=>'Api','middleware' => ['apiCheck']], function (){
    Route::group(['prefix'=>'auth'], function (){
        Route::get('common-setting',[AuthController::class, 'commonSetting']);
        Route::post('sign-up',[AuthController::class, 'register']);
        Route::post('login',[AuthController::class, 'login']);
        Route::post('verify-email',[AuthController::class, 'verifyEmail']);
        Route::post('forgot-password',[AuthController::class, 'forgotPassword']);
        Route::post('reset-password',[AuthController::class, 'resetPassword']);
    });

    Route::group(['middleware' => ['auth:api']], function () {
        //logout
        Route::get('logout',[AuthController::class, 'logout']);
        Route::group(['prefix' => 'user'], function () {
            Route::get('profile',[UserController::class, 'profile']);
            Route::post('update-profile',[UserController::class, 'updateProfile']);
            Route::post('change-password',[UserController::class, 'changePassword']);
        });
        
    });
});