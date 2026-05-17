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
use Illuminate\Support\Facades\Route;

Route::middleware(['auth','admin'])->prefix('clubpoint')->group(function() {
    Route::get('/set-product-point/{hash?}', 'ClubPointController@index')->name('clubpoint.set-product-point')->middleware(['permission']);
    Route::get('/product-get-data', 'ClubPointController@getData')->name('clubpoint.get-point')->middleware(['auth','seller']);
    Route::get('/history-get-data', 'ClubPointController@getHistoryData')->name('clubpoint.get-history')->middleware(['auth','seller']);

    // User Points
    Route::get('/user-product-point', 'ClubPointController@userPoint')->name('clubpoint.user-product-point')->middleware(['permission']);
    Route::get('/product-point-get-data', 'ClubPointController@total_sales_get_data')->name('clubpoint.product-point-get-data');
    Route::get('/clubpoint-details/{id}', 'ClubPointController@show_details')->name('clubpoint.show_details')->middleware(['permission']);

    // Point create
    Route::post('/multiple-Point-Create', 'ClubPointController@store')->name('clubpoint.multiple-Point-Create')->middleware(['permission','prohibited_demo_mode']);
    Route::get('/multiple/edit/{id}', 'ClubPointController@edit')->name('clubpoint.multiple.edit');
    Route::post('/multiple/update/{id}', 'ClubPointController@update')->name('clubpoint.multiple.update');
    
    Route::post('/set-Point-Create', 'ClubPointController@storeSetPoint')->name('clubpoint.set-Point-Create')->middleware(['permission','prohibited_demo_mode']);
    Route::get('/clubpoint-customer', 'ClubPointController@customer')->name('clubpoint.clubpoint-customer')->middleware(['permission','prohibited_demo_mode']);
    Route::get('/clubpoint-customer/get-data', 'ClubPointController@get_data')->name('clubpoint.clubpoint-customer.get-data');
    
    Route::get('/clubpoint/modal-data/{code}', 'ClubPointFrontendController@getModalData')->name('clubpoint.modal-data');
    
    // History of point change
    Route::get('/history/{id}', 'ClubPointController@history')->name('clubpoint.history');
    Route::get('/history-chart-data', 'ClubPointController@getHistoryChartData')->name('clubpoint.get-history-chart');

});

Route::middleware(['auth','customer'])->prefix('clubpoint')->group(function() {
Route::get('/earning-points', 'ClubPointFrontendController@index')->name('clubpoint.frontend.earning-points');
Route::post('/wallet-point-Create', 'ClubPointFrontendController@store')->name('clubpoint.wallet.point.Create');
Route::get('/point/{id}', 'ClubPointFrontendController@point')->name('clubpoint.point');

});

