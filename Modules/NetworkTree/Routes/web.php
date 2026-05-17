<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| NetworkTree Web Routes
|--------------------------------------------------------------------------
| Endpoints AJAX para el árbol de red y sus métricas.
| Accesibles tanto desde el perfil del empresario (auth) como
| desde el panel de administrador (auth + admin).
*/

// --- Empresario autenticado ---
Route::middleware(['auth', 'customer'])->prefix('network')->as('network.')->group(function () {
    // Árbol descendente del empresario autenticado
    Route::get('/tree', 'NetworkTreeController@tree')->name('tree');
    // Métricas de su red
    Route::get('/stats', 'NetworkTreeController@stats')->name('stats');
    Route::get('/plans', 'NetworkTreeController@plans')->name('plans');
    // Buscar empresario dentro de la red del usuario autenticado
    Route::get('/search', 'NetworkTreeController@search')->name('search');
    Route::get('/panel', 'NetworkTreeController@panel')->name('panel');
    Route::get('/node-children', 'NetworkTreeController@children')->name('children');
});

// --- Administrador (ve el árbol de cualquier empresario por ID) ---
Route::middleware(['auth', 'admin'])->prefix('network')->as('network.admin.')->group(function () {
    // Árbol descendente de un empresario específico
    Route::get('/tree/{userId}', 'NetworkTreeController@tree')->name('tree');
    // Métricas de la red de un empresario específico
    Route::get('/stats/{userId}', 'NetworkTreeController@stats')->name('stats');
    Route::get('/plans/{userId}', 'NetworkTreeController@plans')->name('plans');
    // Buscar empresario dentro de la red de un root especifico
    Route::get('/search/{userId}', 'NetworkTreeController@search')->name('search');
    Route::get('/panel/{userId}', 'NetworkTreeController@panel')->name('panel');
    Route::get('/node-children/{userId}', 'NetworkTreeController@children')->name('children');
    
    // NUEVA RUTA: Vista global del árbol (Superadmin)
    Route::get('/global-tree', 'NetworkTreeController@globalTree')->name('global_tree');
});


