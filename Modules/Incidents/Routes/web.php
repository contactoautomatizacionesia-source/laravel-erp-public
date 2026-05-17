<?php

use Illuminate\Support\Facades\Route;

Route::prefix('incidents')->middleware(['auth', 'admin'])->as('incidents.')->group(function () {

    // ─── Lista y métricas ────────────────────────────────────────────────────
    Route::get('/',         'IncidentController@index')->name('index');
    Route::get('/get-data', 'IncidentController@get_data')->name('get-data');
    Route::get('/metrics',  'IncidentController@metrics')->name('metrics');

    // ─── Configuración (singleton) ───────────────────────────────────────────
    Route::get('/settings',  'IncidentSettingController@index')->name('settings');
    Route::post('/settings', 'IncidentSettingController@update')->name('settings.update');

    // ─── Detalle y acciones sobre una novedad ────────────────────────────────
    Route::get('/{id}',                   'IncidentController@show')->name('show');
    Route::post('/{id}/statement',        'IncidentController@submitStatement')->name('statement');
    Route::post('/{id}/resolve',          'IncidentController@resolve')->name('resolve');
    Route::post('/{id}/void',             'IncidentController@void')->name('void');
    Route::post('/{id}/link-closing',     'IncidentController@linkClosing')->name('link-closing');

    // ─── Evidencias ──────────────────────────────────────────────────────────
    Route::post('/{id}/evidence', 'EvidenceController@store')->name('evidence.store');
});
