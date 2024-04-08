<?php

use Illuminate\Support\Facades\Route;

Route::group(['permission' => 'view'], function () {
    Route::any('/', 'HomeController@index')->name('front');
});
// Unauthenticated
Route::prefix('password')->group(function () {
    Route::group(['permission' => 'view'], function () {
        Route::get('reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::get('verify', 'ForgotPasswordController@showLinkRequestFormVerify')->name('password.verify');
        Route::get('test', 'ForgotPasswordController@showLinkRequestFormtest')->name('password.test');
        Route::post('checkotp', 'ForgotPasswordController@CheckOTP')->name('password.checkotp');
        Route::post('email', 'ForgotPasswordController@sendMail')->name('password.email');
        Route::post('reset-pass', 'ForgotPasswordController@sendOTP')->name('password.resetpass.post');
        Route::post('new-pass', 'ForgotPasswordController@newpass')->name('password.newpass.post');
        Route::get('reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('reset', 'ResetPasswordController@reset')->name('password.update');
        Route::get('customer-reset/success', 'ResetPasswordController@success')->name('customer.forgot');
        Route::get('reset-pass', 'ForgotPasswordController@showLinkRequestFormResetPass')->name('password.resetpass');
    });
});
