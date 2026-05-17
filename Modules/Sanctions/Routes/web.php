<?php

use Illuminate\Support\Facades\Route;

Route::prefix('sanctions')->middleware(['auth', 'admin'])->as('sanctions.')->group(function () {

    // ==========================================
    // CASOS ACTIVOS
    // ==========================================
    Route::get('/', 'SanctionsController@index')->name('index')->middleware('permission');
    Route::get('/cases/data', 'SanctionsController@get_data')->name('cases.data');
    Route::post('/cases', 'SanctionsController@store')->name('cases.store')->middleware('permission');

    // Temporal para buscar usuarios por EUI Code
    Route::get('/search-user', 'SanctionsController@get_investigated_customer')->name('search_user');

    // ==========================================
    // HISTORIAL DE FALLOS
    // ==========================================
    Route::get('/history', 'SanctionHistoryController@index')->name('history.index')->middleware('permission');

    // ==========================================
    // CONFIGURACIÓN
    // ==========================================
    Route::get('/settings', 'SanctionSettingsController@index')->name('settings.index')->middleware('permission');
});
