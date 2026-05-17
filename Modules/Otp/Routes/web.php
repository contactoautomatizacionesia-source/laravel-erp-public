<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\MerchantRegisterController;
use Illuminate\Support\Facades\Route;
use Modules\Otp\Http\Controllers\FrontendOTPController;

Route::middleware(['admin','auth'])->group(function () {

    Route::prefix('otp')->group(function () {
        Route::get('/configuration', 'OtpController@configuration')->name('otp.configuration');
        Route::post('/configuration', 'OtpController@configuration_update')->name('opt.configuration_update');
    });
});

Route::post('/otp',[FrontendOTPController::class,'otp_check'])->name('otp_check');
Route::post('/seller-otp',[MerchantRegisterController::class,'otp_check_for_seller'])->name('otp_check_for_seller');
Route::get('/resend-otp',[FrontendOTPController::class,'resend_otp'])->name('resend_otp');
Route::get('/resend-otp-seller',[FrontendOTPController::class,'resend_otp_for_seller'])->name('resend_otp_for_seller');

Route::post('/login-otp',[FrontendOTPController::class,'login_otp_check'])->name('login_otp_check');
Route::get('/resend-login-otp',[FrontendOTPController::class,'resend_login_otp'])->name('resend_login_otp');

Route::post('/order-otp',[FrontendOTPController::class,'order_otp_check'])->name('order_otp_check');
Route::get('/order-resend-otp',[FrontendOTPController::class,'order_resend_otp'])->name('order_resend_otp');

//for reset password
Route::post('/send-password-reset-otp',[FrontendOTPController::class,'send_password_reset_otp'])->name('send_password_reset_otp');
Route::get('/resend-password-reset-otp',[FrontendOTPController::class,'resend_password_reset_otp'])->name('resend_password_reset_otp');
Route::post('/password-reset-otp',[FrontendOTPController::class,'password_reset_otp_check'])->name('password_reset_otp_check');
Route::get('/password-update-otp-form', function() {
    if (session()->has('password_reset_by_otp_user')) {
        return view(theme('auth.password_reset_form_otp'));
    }
        return redirect()->route('login')->with('error', __('auth.session_expired_or_processed'));
})->name('otp.password_reset_form');
Route::post('/password-update-otp',[FrontendOTPController::class,'otp_user_password_update'])->name('otp_user_password_update');
