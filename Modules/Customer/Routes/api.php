<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|--------------------------------------------------------------------------
| ProtecData — Callback de firma electrónica
|--------------------------------------------------------------------------
| ProtecData llama a este endpoint (POST) cuando el usuario completa o
| rechaza la firma de un documento. No lleva autenticación de sesión —
| es una petición entrante de un servicio externo.
|
| Para habilitar/deshabilitar el servicio completo: PROTECDATA_ENABLED en .env
|
*/
Route::post('/protecdata/callback', 'ProtecdataCallbackController@handle')
    ->name('protecdata.callback')
    ->withoutMiddleware(['auth:sanctum']);

