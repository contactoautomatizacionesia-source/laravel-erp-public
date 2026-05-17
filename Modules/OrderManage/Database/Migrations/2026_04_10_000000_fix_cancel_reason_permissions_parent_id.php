<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCancelReasonPermissionsParentId extends Migration
{
    /**
     * Los permisos Create/Edit/Delete de cancel_reason tenían parent_id = 465 (List)
     * en lugar de 464 (Cancel Reason), lo que los ocultaba en la UI de roles y permisos.
     * Se corrige el parent_id para que queden al mismo nivel que delivery_process.
     */
    public function up()
    {
        $parentId = DB::table('permissions')
            ->where('id', 464)
            ->where('route', 'order_manage.cancel_reason_index')
            ->value('id');

        if (! $parentId) {
            throw new \LogicException(
                'No se encontró el permiso padre Cancel Reason (id=464, route=order_manage.cancel_reason_index). Migración abortada.'
            );
        }

        $wrongParentId = DB::table('permissions')
            ->where('id', 465)
            ->where('route', 'order_manage.cancel_reason_list')
            ->value('id');

        if (! $wrongParentId) {
            throw new \LogicException(
                'No se encontró el permiso List de cancel_reason (id=465, route=order_manage.cancel_reason_list). Migración abortada.'
            );
        }

        $affected = DB::table('permissions')
            ->whereIn('id', [466, 467, 468])
            ->where('parent_id', 465)
            ->count();

        if ($affected === 0) {
            // Ya corregidos o no existen — no hay nada que hacer
            return;
        }

        DB::table('permissions')
            ->whereIn('id', [466, 467, 468])
            ->where('parent_id', 465)
            ->update(['parent_id' => 464]);
    }

    public function down()
    {
        DB::table('permissions')
            ->whereIn('id', [466, 467, 468])
            ->where('parent_id', 464)
            ->update(['parent_id' => 465]);
    }
}
