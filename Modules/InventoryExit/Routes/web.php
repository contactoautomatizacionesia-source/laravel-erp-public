<?php

use Illuminate\Support\Facades\Route;

Route::prefix('inventory-exit')->middleware(['auth', 'admin'])->as('inventory_exit.')->group(function () {
    // Index + DataTable
    Route::get('/', 'InventoryExitController@index')->name('index');
    Route::get('/get-data', 'InventoryExitController@getData')->name('get-data');

    // AJAX helpers — ANTES de rutas con /{id}
    Route::get('/reasons', 'InventoryExitController@getReasons')->name('reasons');
    Route::get('/products/search', 'InventoryExitController@searchProducts')->name('products.search');
    Route::get('/skus-by-location', 'InventoryExitController@getSkusByLocation')->name('skus-by-location');
    Route::get('/lots', 'InventoryExitController@getLocationLots')->name('lots');

    // Crear solicitud
    Route::post('/store', 'InventoryExitController@store')->name('store');

    // Aprobar/Rechazar — /{id} al final
    Route::post('/{id}/approve', 'InventoryExitController@approve')->name('approve');
    Route::get('/{id}/detail', 'InventoryExitController@detail')->name('detail');
});
