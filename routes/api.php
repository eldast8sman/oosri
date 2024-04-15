<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\SellerBusinessController as AdminSellerBusinessController;
use App\Http\Controllers\Seller\AuthController;
use App\Http\Controllers\Seller\SellerBusinessController;
use App\Http\Controllers\Seller\WalletController;
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
            Route::get('/refresh-token', 'refreshToken')->name('seller.refreshToken');
        });

        Route::controller(SellerBusinessController::class)->group(function(){
            Route::post('/businesses', 'store')->name('seller.business.store');
            Route::get('/businesses', 'index')->name('seller.business.index');
            Route::get('/businesses/{slug}', 'show')->name('seller.business.show');
            Route::get('/businesses/{slug}/switch', 'switch_business')->name('seller.switchBusiness');
        });

        Route::controller(WalletController::class)->group(function(){
            Route::post('bank-details', 'set_account_details')->name('seller.accountDetails');
        });
    });
});

Route::prefix('admin')->group(function(){
    Route::controller(AdminAuthController::class)->group(function(){
        Route::post('/add-first-admin', 'storeAdmin')->name('addFirstAdmin');
        Route::get('/by-token/{token}', 'byToken')->name('admin.byToken');
        Route::post('/activate', 'activate_account')->name('admin.activateAccount');
        Route::post('/login', 'login')->name('admin.login');
        Route::post('/forgot-password', 'forgot_password')->name('admin.forgotPassword');
        Route::post('/check-pin', 'check_pin')->name('admin.checkPin');
        Route::post('/reset-password', 'reset_password')->name('admin.resetPassword');
    });

    Route::middleware('auth:admin-api')->group(function(){
        Route::controller(AdminAuthController::class)->group(function(){
            Route::post('/change-password', 'change_password')->name('admin.changePassword');
            Route::get('/me', 'me')->name('admin.me');
            Route::get('/logout', 'logout')->name('admin.logout');
        });

        Route::controller(AdminController::class)->group(function(){
            Route::get('/admins', 'index')->name('admin.index');
            Route::post('/admins', 'store')->name('admin.store');
            Route::get('/admins/{admin}/resend-link', 'resend_activation_link')->name('admin.resendActivationLink');
            Route::get('/admins/{admin}', 'show')->name('admin.show');
            Route::put('/admins/{admin}', 'update')->name('admin.update');
            Route::get('/admins/{admin}/activation', 'account_activation')->name('admin.accountActivation');
            Route::delete('/admins/{admin}', 'destroy')->name('admin.delete');
        });

        Route::controller(AdminSellerBusinessController::class)->group(function(){
            Route::get('/businesses', 'index')->name('admin.busness.index');
            Route::get('/latest-businesses', 'new_businesses')->name('admin.latestBusiness');
            Route::get('/pending-businesses', 'pending_businesses')->name('admin.pendingBusiness');
            Route::get('/businesses/{business}/verify', 'verification')->name('admin.business.verification');
        });
    });
});
