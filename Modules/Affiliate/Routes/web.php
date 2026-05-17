<?php

use Illuminate\Support\Facades\Route;

Route::prefix('affiliate')->as('affiliate.')->middleware(['auth'])->group(function () {

    //affiliate
    Route::resource('my_affiliate', 'AffiliateController')->except(['destroy']);
    Route::get('/delete-link/{id}', 'AffiliateController@deleteLink')->name('delete_link');
    //configuration
    Route::get('configurations', 'AffiliateController@configurationIndex')->name('configurations.index')->middleware(['admin']);
    Route::post('configurations', 'AffiliateController@configurationUpdate')->name('configurations.update')->middleware(['admin','prohibited_demo_mode','permission']);
    //add_paypal_account
    Route::post('add-or-update-paypal-account', 'AffiliateController@addOrUpdatePaypalAccount')->name('add_or_update_paypal_account')->middleware(['affiliate']);
    //withdraw_request
    Route::resource('withdraw_request', 'AffiliateTransactionController')->except(['destroy']);
    Route::post('destroy/withdraw_request', 'AffiliateTransactionController@destroy')->name('withdraw_request.destroy');
    Route::post('balance-transfer-to-wallet', 'AffiliateTransactionController@balanceTransferToWallet')->name('balance_transfer_to_wallet');
    Route::get('pending-withdraw', 'AffiliateTransactionController@pendingWithdraw')->name('pending_withdraw')->middleware(['permission','admin']);
    Route::get('pending-withdraw/datatable', 'AffiliateTransactionController@pendingWithdrawDatatable')->name('pending_withdraw.datatable');
    Route::get('confirm-withdraw/{id}', 'AffiliateTransactionController@confirmWithdraw')->name('confirm_withdraw')->middleware(['permission','admin']);
    Route::get('complete-withdraw', 'AffiliateTransactionController@completeWithdraw')->name('complete_withdraw')->middleware(['permission','admin']);
    Route::get('complete-withdraw/datatable', 'AffiliateTransactionController@completeWithdrawDatatable')->name('complete_withdraw.datatable');

    //affiliate user
    Route::get('users', 'AffiliateUserController@index')->name('users.index');
    Route::get('users/datatable', 'AffiliateUserController@datatable')->name('users.datatable');
    Route::get('user/approved/{id}', 'AffiliateUserController@approved')->name('users.approved')->middleware('permission');
    Route::get('user/disable-enabale/{id}', 'AffiliateUserController@disableEnable')->name('users.disable_enable')->middleware('permission');
    Route::get('user/request', 'AffiliateUserController@userRequest')->name('users.request');
    Route::get('/dashboard', 'AffiliateUserController@dashboard')->name('dashboard');
    Route::get('/user/show/{id}', 'AffiliateUserController@show')->name('user.show')->middleware('permission');

});


Route::prefix('affiliate')->as('affiliate.')->group(function () {
//    Route::get('/', 'AffiliateAuthController@indexPage')->name('index.page');
    //affiliate registration
    Route::get('registration', 'AffiliateAuthController@showRegistrationFrom')->name('registration');
    Route::post('register', 'AffiliateAuthController@register')->name('register');
    //cron job
    Route::get('pending-commission/approved', 'AffiliateTransactionController@pendingCommissionApproved')->name('pending_commission.approved');
    Route::get('join-affiliate/customer','AffiliateController@customerJoinAffiliate')->name('customerJoinAffiliate');
});

