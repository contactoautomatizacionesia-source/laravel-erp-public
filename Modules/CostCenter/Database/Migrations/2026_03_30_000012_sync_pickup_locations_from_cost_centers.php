<?php

use Illuminate\Database\Migrations\Migration;
use Modules\CostCenter\Actions\SyncPickupLocation;
use Modules\CostCenter\Entities\CostCenter;
use Modules\Shipping\Entities\PickupLocation;

/**
 * Migración de datos única: limpia pickup_locations huérfanas y regenera
 * una entrada por cada cost_center usando SyncPickupLocation.
 *
 * Se ejecuta una sola vez gracias al registro en la tabla migrations.
 * Después de esto, la sincronización es automática vía CostCenterService.
 */
class SyncPickupLocationsFromCostCenters extends Migration
{
    public function up(): void
    {
        // Guarda: si ya existe alguna pickup_location vinculada a un CC,
        // la sincronización ya fue ejecutada — no volver a correr.
        if (PickupLocation::whereNotNull('cost_center_id')->exists()) {
            return;
        }

        // 1. Eliminar pickup_locations huérfanas (sin cost_center_id — datos legacy)
        PickupLocation::whereNull('cost_center_id')->delete();

        // 2. Generar una pickup_location por cada cost_center (incluye soft-deleted)
        $action = new SyncPickupLocation();

        CostCenter::withoutGlobalScopes()
            ->with('city.state.country')
            ->get()
            ->each(function (CostCenter $costCenter) use ($action) {
                $action->execute($costCenter);
            });
    }

    public function down(): void
    {
        // Irreversible intencionalmente: los datos originales eran legacy sin valor.
        // El down() no restaura las pickup_locations huérfanas eliminadas.
    }
}
