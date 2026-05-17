<?php

use Illuminate\Support\Facades\Route;

Route::prefix('cashmanager')->middleware(['web', 'auth'])->name('cash_manager.')->group(function () {

    // Dashboard / redirect por rol
    Route::get('/', 'CashManagerController@index')->name('index');

    // ─── Operaciones (Cierres y Arqueos) ─────────────────────────────────────
    Route::prefix('operations')->name('operations.')->group(function () {
        Route::get('/',       'OperationsController@index')->name('index');
        Route::post('/close', 'OperationsController@close')->name('close');
    });

    // ─── Asignaciones (Gestión de Personal) ──────────────────────────────────
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/',                          'AssignmentsController@index')->name('index');
        Route::post('/store',                    'AssignmentsController@store')->name('store');
        Route::post('/sessions/{id}/confirm',    'AssignmentsController@confirmReceipt')->name('confirm_receipt');
        Route::post('/boxes/{id}/submit',        'AssignmentsController@submitToParent')->name('submit_to_parent');
        Route::post('/{id}/revoke',              'AssignmentsController@revoke')->name('revoke');
    });

    // ─── Configuraciones (Administración) ────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'SettingsController@index')->name('index');

        // Denominaciones
        Route::post('/denominations',              'SettingsController@storeDenomination')->name('store_denomination');
        Route::post('/denominations/status',       'SettingsController@updateDenominationStatus')->name('update_denomination_status');
        Route::delete('/denominations/{id}',       'SettingsController@destroyDenomination')->name('destroy_denomination');

        // Cajas
        Route::get('/boxes/next-type',  'SettingsController@nextBoxType')->name('next_box_type');
        Route::post('/boxes',           'SettingsController@storeBox')->name('store_box');

        // Roles de operador
        Route::post('/operator-roles', 'SettingsController@saveOperatorRoles')->name('save_operator_roles');
    });
});
