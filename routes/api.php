<?php

use App\Http\Controllers\Seller\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('seller')->group(function(){
    Route::controller(AuthController::class)->group(function(){
        Route::post('/signup', 'store')->name('seller.signUp');
        Route::post('/login', 'login')->name('seller.logIn');
        Route::post('/forgot-password', 'forgot_password')->name('seller.forgotPassword');
        Route::post('/check-pin', 'check_pin')->name('seller.checkPin');
        Route::post('/reset-password', 'reset_password')->name('seller.resetPaosswrd');
    });

    Route::middleware('auth:seller-api')->group(function(){
        Route::controller(AuthController::class)->group(function(){
            Route::get('/me', 'me')->name('seller.me');
            Route::get('/resend-activation-pin', 'resend_pin')->name('seller.resendPin');
            Route::post('/activate', 'activate_account')->name('seller.activate');
        });
    });
});
