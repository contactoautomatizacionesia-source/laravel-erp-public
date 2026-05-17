<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixSanctionsPermissions extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. CORREGIR TRADUCCIÓN DEL PERMISO RAÍZ (type 1)
        //    'permission.sanctions' no existe → usar 'sanctions.sanctions'
        // =========================================================
        DB::table('permissions')
            ->where('route', 'sanctions.index')
            ->where('type', 1)
            ->update(['translation' => 'sanctions.sanctions']);

        // =========================================================
        // 2. AGREGAR PERMISOS GRANULARES (type 3) POR SECCIÓN
        // =========================================================

        // Recuperar los IDs de los type 2 ya insertados
        $casosActivosId  = DB::table('permissions')->where('route', 'sanctions.index')->where('type', 2)->value('id');
        $historialId     = DB::table('permissions')->where('route', 'sanctions.history.index')->where('type', 2)->value('id');
        $configuracionId = DB::table('permissions')->where('route', 'sanctions.settings.index')->where('type', 2)->value('id');

        $moduleId = DB::table('permissions')->where('route', 'sanctions.index')->where('type', 1)->value('module_id');

        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $rows = [];

        // --- CASOS ACTIVOS ---
        $rows[] = ['id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $casosActivosId,  'name' => 'Ver casos activos',    'translation' => 'sanctions.perm_view_cases',   'route' => 'sanctions.index',        'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()];
        $rows[] = ['id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $casosActivosId,  'name' => 'Crear caso',           'translation' => 'sanctions.perm_create_case',  'route' => 'sanctions.cases.store',  'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()];

        // --- HISTORIAL DE FALLOS ---
        $rows[] = ['id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $historialId,     'name' => 'Ver historial',        'translation' => 'sanctions.perm_view_history', 'route' => 'sanctions.history.index', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()];

        // --- CONFIGURACIÓN ---
        $rows[] = ['id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $configuracionId, 'name' => 'Ver configuración',    'translation' => 'sanctions.perm_view_settings',   'route' => 'sanctions.settings.index', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()];
        $rows[] = ['id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $configuracionId, 'name' => 'Gestionar catálogos', 'translation' => 'sanctions.perm_manage_settings', 'route' => 'sanctions.settings.index', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()];

        DB::table('permissions')->insert($rows);
    }

    public function down()
    {
        // Revertir la traducción del tipo 1
        DB::table('permissions')
            ->where('route', 'sanctions.index')
            ->where('type', 1)
            ->update(['translation' => 'permission.sanctions']);

        // Eliminar los type 3 agregados
        DB::table('permissions')->whereIn('translation', [
            'sanctions.perm_view_cases',
            'sanctions.perm_create_case',
            'sanctions.perm_view_history',
            'sanctions.perm_view_settings',
            'sanctions.perm_manage_settings',
        ])->delete();
    }
}
