<?php

use Illuminate\Support\Facades\Route;

Route::prefix('cost-centers')->middleware(['auth', 'admin'])->as('cost_centers.')->group(function () {
    Route::get('/', 'CostCenterController@index')->name('index');
    Route::get('/get-data', 'CostCenterController@get_data')->name('get-data');
    Route::post('/store', 'CostCenterController@store')->name('store');
    Route::get('/{id}/edit', 'CostCenterController@edit')->name('edit');
    Route::post('/{id}/update', 'CostCenterController@update')->name('update');
    Route::post('/{id}/destroy', 'CostCenterController@destroy')->name('destroy');
    Route::post('/{id}/restore', 'CostCenterController@restore')->name('restore');

    // Endpoints para selects
    Route::get('/get-cities', 'CostCenterController@get_cities')->name('get-cities');
    Route::get('/get-brands', 'CostCenterController@get_brands')->name('get-brands');
    Route::get('/get-payment-methods', 'CostCenterController@get_payment_methods')->name('get-payment-methods');
    Route::get('/get-users', 'CostCenterController@get_users')->name('get-users');
    Route::post('/update-status', 'CostCenterController@update_status')->name('update-status');
    Route::post('/update-default', 'CostCenterController@update_default')->name('update-default');

    Route::prefix('inventory')->as('inventory.')->group(function () {
        Route::get('/', 'CostCenterInventoryController@index')->name('index');
        Route::get('/get-data', 'CostCenterInventoryController@get_data')->name('get-data');

        // Gestión de inventario (asignaciones y devoluciones)
        Route::get('/manage', 'CostCenterInventoryController@showTransferForm')->name('manage');
        Route::post('/manage', 'CostCenterInventoryTransactionsController@transferToCenter')->name('process');

        // API endpoints para selects (usados en formularios)
        Route::get('/warehouse-skus', 'CostCenterInventoryController@listMainWarehouseSkus')->name('warehouse-skus');
        Route::get('/center-skus/{centerId}', 'CostCenterInventoryController@getCenterSkus')->name('center-skus');
        Route::get('/center-inventory/{centerId}', 'CostCenterInventoryController@getCenterInventory')->name('center-inventory');
        Route::get('/location-lots', 'CostCenterInventoryController@getLocationLots')->name('location-lots');
        Route::post('/product-alert', 'CostCenterInventoryController@updateStockAlert')->name('product-alert');
        Route::get('/get-location-users/{locationId}', 'CostCenterInventoryController@getLocationUsers')->name('get-location-users');
        Route::get('/carriers', 'CostCenterInventoryTransactionsController@getCarriers')->name('carriers');
        Route::get('/center/{centerId}/products', 'CostCenterInventoryController@showCenterProducts')->name('show-products');
        Route::post('/product-detail', 'CostCenterInventoryController@showProductDetail')->name('product-detail');

        // Historial de Transacciones
        Route::get('/all-transactions', 'CostCenterInventoryTransactionsController@allTransactions')->name('all-transactions');
        Route::get('/all-transactions-data', 'CostCenterInventoryTransactionsController@allTransactionsData')->name('all-transactions-data');
        Route::get('/center/{centerId}/transactions', 'CostCenterInventoryTransactionsController@transactions')->name('transactions');
        Route::get('/center/{centerId}/transactions-data', 'CostCenterInventoryTransactionsController@transactionsData')->name('transactions-data');
        Route::get('/transactions/{id}/detail', 'CostCenterInventoryTransactionsController@transactionDetail')->name('transaction-detail');
        // --- RUTAS CORREGIDAS ---
        // Al estar dentro de los grupos "cost_centers." e "inventory.", el nombre final será: cost_centers.inventory.transactions.show
        Route::get('/transactions/{id}/show', 'CostCenterInventoryTransactionsController@show')->name('transactions.show');

        // El nombre final será: cost_centers.inventory.transactions.receive
        Route::post('/transactions/{id}/receive', 'CostCenterInventoryTransactionsController@receive')->name('transactions.receive');
    });
});
