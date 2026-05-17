<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('schedule')->group(function () {
    // Ruta principal
    Route::get('/role-work-schedule', 'RoleWorkScheduleController@index')->name('role_work_schedule.index');
    
    // Rutas para acciones CRUD
    Route::post('/store', 'RoleWorkScheduleController@store')->name('role_work_schedule.store');
    Route::put('/update/{id}', 'RoleWorkScheduleController@update')->name('role_work_schedule.update');
    Route::delete('/destroy/{id}', 'RoleWorkScheduleController@destroy')->name('role_work_schedule.destroy');

    //  Asignar horarios a un Rol
    Route::post('/assign', 'RoleWorkScheduleController@assign')->name('role_work_schedule.assign');
});