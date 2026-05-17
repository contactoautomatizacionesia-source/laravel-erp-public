<?php

use App\Constants\RouteConstants;
use Illuminate\Support\Facades\Route;
use Modules\CycleClosure\Http\Controllers\CycleClosureController;
use Modules\CycleClosure\Http\Controllers\CycleSettingController;

Route::prefix('cycle-closure')
    ->middleware(['auth', 'admin'])
    ->as('cycle_closure.')
    ->namespace('Modules\CycleClosure\Http\Controllers')
    ->group(function () {

        // ── Configuración ────────────────────────────────────────────────────
        Route::prefix('settings')->as('settings.')->group(function () {
            Route::get(RouteConstants::HOME, [CycleSettingController::class, 'index'])->name('index');
            Route::get(RouteConstants::DATA, [CycleSettingController::class, 'getData'])->name('data');
            Route::post(RouteConstants::HOME, [CycleSettingController::class, 'store'])->name('store');
        });

        // ── Ciclos — rutas fijas primero ─────────────────────────────────────
        Route::get(RouteConstants::HOME, [CycleClosureController::class, 'index'])->name('index');
        Route::get(RouteConstants::DATA, [CycleClosureController::class, 'getData'])->name('data');

        // ── Ciclos — rutas con {id} AL FINAL ─────────────────────────────────
        Route::get('/{id}/logs-data', [CycleClosureController::class, 'getLogsData'])->name('logs-data');
        Route::get('/{id}/acta', [CycleClosureController::class, 'downloadActa'])->name('acta');

        // Acciones del ejecutor (needs_review)
        Route::post('/{id}/executor-approve', [CycleClosureController::class, 'approveByExecutor'])->name('executor-approve');
        Route::post('/{id}/executor-cancel', [CycleClosureController::class, 'cancelByExecutor'])->name('executor-cancel');

        // Acciones del co-aprobador (pending_approval)
        Route::post('/{id}/coapprover-approve', [CycleClosureController::class, 'approveByCoapprover'])->name('coapprover-approve');
        Route::post('/{id}/coapprover-reject', [CycleClosureController::class, 'rejectByCoapprover'])->name('coapprover-reject');

        Route::get('/{id}', [CycleClosureController::class, 'show'])->name('show');
    });
