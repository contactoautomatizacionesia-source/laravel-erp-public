<?php

use Illuminate\Support\Facades\Route;
use App\Constants\RouteConstants;
use Modules\InventoryCount\Http\Controllers\InventoryCountAuditController;
use Modules\InventoryCount\Http\Controllers\InventoryCountController;
use Modules\InventoryCount\Http\Controllers\InventoryCountSettingController;

Route::prefix('inventory-count')
    ->middleware(['auth', 'admin'])
    ->as('inventory_count.')
    ->namespace('Modules\InventoryCount\Http\Controllers')
    ->group(function () {

        // ─── Configuración ────────────────────────────────────────────────
        Route::prefix('settings')->as('settings.')->group(function () {
            Route::get('/', [InventoryCountSettingController::class, 'index'])->name('index');
            Route::get(RouteConstants::DATA, [InventoryCountSettingController::class, 'getData'])->name('data');
            Route::get('/{costCenterId}/edit', [InventoryCountSettingController::class, 'edit'])->name('edit');
            Route::post('/', [InventoryCountSettingController::class, 'store'])->name('store');
        });

        // ─── Conteos ──────────────────────────────────────────────────────
        Route::get('/', [InventoryCountController::class, 'index'])->name('index');
        Route::get(RouteConstants::DATA, [InventoryCountController::class, 'getData'])->name('data');
        Route::get(RouteConstants::CREATE, [InventoryCountController::class, 'create'])->name('create');


        // ─── Auditorías ───────────────────────────────────────────────────
        Route::prefix('audits')->as('audits.')->group(function () {
            Route::get('/', [InventoryCountAuditController::class, 'index'])->name('index');
            Route::get(RouteConstants::DATA, [InventoryCountAuditController::class, 'getData'])->name('data');
            Route::get('/review-data/{countId}', [InventoryCountAuditController::class, 'getReviewData'])->name('review-data');
            Route::post('/', [InventoryCountAuditController::class, 'store'])->name('store');
            Route::get('/{id}', [InventoryCountAuditController::class, 'show'])->name('show');
        });

        // Ruta dinámica al final para no capturar rutas con nombre fijo
        Route::get('/{id}', [InventoryCountController::class, 'show'])->name('show');
    });
