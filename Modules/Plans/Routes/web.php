<?php

use Illuminate\Support\Facades\Route;

Route::prefix('plans')->middleware(['auth', 'admin'])->as('plans.')->group(function () {

    // ==========================================
    // PLAN PADRES (Plan)
    // ==========================================
    Route::get('/', 'PlansController@index')->name('index');
    Route::get('/get-data', 'PlansController@get_data')->name('get-data');
    Route::post('/store', 'PlansController@store')->name('store');
    Route::post('/reorder', 'PlansController@reorder')->name('reorder');
    Route::get('/{id}/edit', 'PlansController@edit')->name('edit');
    Route::post('/{id}/update', 'PlansController@update')->name('update');
    Route::post('/{id}/destroy', 'PlansController@destroy')->name('destroy');
    Route::get('/get-list', 'PlansController@get_list')->name('get-list');

    // ==========================================
    // PLAN HIJOS (PlanChild)
    // ==========================================
    Route::prefix('{planId}/children')->as('children.')->group(function () {
        Route::get('/', 'PlanChildController@index')->name('index');
        Route::get('/get-data', 'PlanChildController@get_data')->name('get-data');
        Route::post('/store', 'PlanChildController@store')->name('store');
        Route::post('/reorder', 'PlanChildController@reorder')->name('reorder');
        Route::get('/available-rules', 'PlanChildController@get_available_rules')->name('available-rules');
        Route::get('/available-benefits', 'PlanChildController@get_available_benefits')->name('available-benefits');
        Route::get('/{id}/edit', 'PlanChildController@edit')->name('edit');
        Route::post('/{id}/update', 'PlanChildController@update')->name('update');
        Route::post('/{id}/destroy', 'PlanChildController@destroy')->name('destroy');
        Route::get('/{id}/assignments', 'PlanChildController@get_assignments')->name('assignments');
        Route::post('/{id}/assign-rules', 'PlanChildController@assign_rules')->name('assign-rules');
        Route::post('/{id}/assign-benefits', 'PlanChildController@assign_benefits')->name('assign-benefits');
    });

    // ==========================================
    // REGLAS
    // ==========================================
    Route::prefix('rules')->as('rules.')->group(function () {
        Route::get('/', 'RuleController@index')->name('index');
        Route::get('/next-code', 'RuleController@next_code')->name('next-code');
        Route::get('/get-data', 'RuleController@get_data')->name('get-data');
        Route::post('/store', 'RuleController@store')->name('store');
        Route::get('/get-list', 'RuleController@get_list')->name('get-list');
        Route::get('/plan-children-list', 'RuleController@get_plan_children_list')->name('plan-children-list');
        Route::get('/form-structure/{categoryId}', 'RuleController@get_form_structure')->name('form-structure');
        Route::get('/{id}/edit', 'RuleController@edit')->name('edit');
        Route::post('/{id}/update', 'RuleController@update')->name('update');
        Route::post('/{id}/destroy', 'RuleController@destroy')->name('destroy');
        Route::get('/{id}/show', 'RuleController@show')->name('show');
    });

    // ==========================================
    // BENEFICIOS
    // ==========================================
    Route::prefix('benefits')->as('benefits.')->group(function () {
        Route::get('/', 'BenefitController@index')->name('index');
        Route::get('/next-code', 'BenefitController@next_code')->name('next-code');
        Route::get('/get-data', 'BenefitController@get_data')->name('get-data');
        Route::post('/store', 'BenefitController@store')->name('store');
        Route::get('/get-list', 'BenefitController@get_list')->name('get-list');
        Route::get('/form-structure/{categoryId}', 'BenefitController@get_form_structure')->name('form-structure');
        Route::get('/{id}/edit', 'BenefitController@edit')->name('edit');
        Route::post('/{id}/update', 'BenefitController@update')->name('update');
        Route::post('/{id}/destroy', 'BenefitController@destroy')->name('destroy');
        Route::get('/{id}/show', 'BenefitController@show')->name('show');
    });
});
