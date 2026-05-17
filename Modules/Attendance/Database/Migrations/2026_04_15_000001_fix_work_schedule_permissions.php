<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixWorkSchedulePermissions extends Migration
{
    public function up()
    {
        // =========================================================
        // ANCLAS SEMÁNTICAS — sin IDs hardcodeados
        // =========================================================

        // Módulo HR (type 1) — identificado por su ruta raíz
        $hrRootId = DB::table('permissions')
            ->where('route', 'human_resource')
            ->where('type', 1)
            ->value('id');

        // Módulo Work Schedule (type 1) — identificado por su ruta raíz
        $wsRootId = DB::table('permissions')
            ->where('route', 'role_work_schedule')
            ->where('type', 1)
            ->value('id');

        $hrModuleId = DB::table('permissions')
            ->where('id', $hrRootId)
            ->value('module_id');

        if (! $hrRootId || ! $wsRootId || ! $hrModuleId) {
            return;
        }

        // =========================================================
        // 1. SEPARAR el bloque de Holiday Setup
        //
        //    En algunos entornos los registros de Evento y Festivos
        //    comparten IDs porque se insertaron con insert() en lote
        //    en lugar de insertGetId(). Detectamos por route+type.
        // =========================================================

        // Solo actuar si holidays.index está duplicado (mismo id que events.index)
        $eventId   = DB::table('permissions')->where('route', 'events.index') ->where('type', 2)->value('id');
        $holidayId = DB::table('permissions')->where('route', 'holidays.index')->where('type', 2)->value('id');

        if ($eventId && $holidayId && $eventId === $holidayId) {
            // Eliminar las filas de holidays que comparten ID con Evento
            DB::delete("DELETE FROM permissions WHERE route = 'holidays.index' AND type = 2");
            DB::delete("DELETE FROM permissions WHERE route = 'holidays.index' AND type = 3");
            DB::delete("DELETE FROM permissions WHERE route = 'holidays.store'  AND type = 3");
            DB::delete("DELETE FROM permissions WHERE route = 'last.year.data'  AND type = 3");

            // Reinsertar Holiday Setup con IDs únicos
            $nextId        = (DB::table('permissions')->max('id') ?? 0) + 1;
            $holidayPermId = $nextId++;

            DB::table('permissions')->insert([
                'id' => $holidayPermId, 'module_id' => $hrModuleId, 'parent_id' => $hrRootId,
                'name' => 'Holiday Setup', 'translation' => 'permission.holiday_setup',
                'route' => 'holidays.index', 'type' => 2, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('permissions')->insert([
                [
                    'id' => $nextId++, 'module_id' => $hrModuleId, 'parent_id' => $holidayPermId,
                    'name' => 'List', 'translation' => 'permission.list',
                    'route' => 'holidays.index', 'type' => 3, 'status' => 1,
                    'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'id' => $nextId++, 'module_id' => $hrModuleId, 'parent_id' => $holidayPermId,
                    'name' => 'Store', 'translation' => 'permission.store',
                    'route' => 'holidays.store', 'type' => 3, 'status' => 1,
                    'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'id' => $nextId++, 'module_id' => $hrModuleId, 'parent_id' => $holidayPermId,
                    'name' => 'Copy', 'translation' => 'permission.copy',
                    'route' => 'last.year.data', 'type' => 3, 'status' => 1,
                    'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }

        // =========================================================
        // 2. MOVER Work Schedule: type 1 → type 2 hijo de HR
        // =========================================================
        DB::table('permissions')
            ->where('route', 'role_work_schedule')
            ->where('type', 1)
            ->update([
                'type'      => 2,
                'parent_id' => $hrRootId,
                'module_id' => $hrModuleId,
            ]);

        // =========================================================
        // 3. CONVERTIR hijos de Work Schedule: type 2 → type 3
        // =========================================================
        DB::table('permissions')
            ->where('parent_id', $wsRootId)
            ->where('type', 2)
            ->update([
                'type'      => 3,
                'module_id' => $hrModuleId,
            ]);
    }

    public function down()
    {
        $wsRootId = DB::table('permissions')
            ->where('route', 'role_work_schedule')
            ->where('type', 2)
            ->value('id');

        // Revertir hijos type 3 → type 2
        if ($wsRootId) {
            DB::table('permissions')
                ->where('parent_id', $wsRootId)
                ->where('type', 3)
                ->whereIn('route', [
                    'role_work_schedule.index',
                    'role_work_schedule.store',
                    'role_work_schedule.update',
                    'role_work_schedule.destroy',
                    'role_work_schedule.assign',
                ])
                ->update(['type' => 2]);
        }

        // Revertir Work Schedule type 2 → type 1
        DB::table('permissions')
            ->where('route', 'role_work_schedule')
            ->where('type', 2)
            ->update(['type' => 1, 'parent_id' => null]);

        // Eliminar Holiday Setup reinsertado (identificado por route+type, no por ID)
        $holidayId = DB::table('permissions')
            ->where('route', 'holidays.index')
            ->where('type', 2)
            ->value('id');

        if ($holidayId) {
            DB::table('permissions')->where('parent_id', $holidayId)->delete();
            DB::table('permissions')->where('id', $holidayId)->delete();
        }
    }
}
