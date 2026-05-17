<?php

use Illuminate\Support\Facades\Route;

Route::prefix('inventory-entry')->middleware(['auth', 'admin'])->as('inventory_entry.')->group(function () {
    // Vista principal + DataTable
    Route::get('/', 'InventoryEntryController@index')->name('index');
    Route::get('/get-data', 'InventoryEntryController@getData')->name('get-data');

    // Creación de ingreso
    Route::get('/create', 'InventoryEntryController@create')->name('create');
    Route::post('/store', 'InventoryEntryController@store')->name('store');

    // Edición / Eliminación
    Route::get('/{id}/edit', 'InventoryEntryController@edit')->name('edit');
    Route::post('/{id}/update', 'InventoryEntryController@update')->name('update');
    Route::post('/{id}/destroy', 'InventoryEntryController@destroy')->name('destroy');

    // Detalle
    Route::get('/{id}/detail', 'InventoryEntryController@detail')->name('detail');

    // AJAX helpers — deben ir ANTES de rutas con /{id}
    Route::get('/products/search', 'InventoryEntryController@searchProducts')->name('products.search');
    Route::get('/products/{productId}/skus', 'InventoryEntryController@getProductSkus')->name('products.skus');
    Route::get('/lots/find', 'InventoryEntryController@findLot')->name('lots.find');
});
