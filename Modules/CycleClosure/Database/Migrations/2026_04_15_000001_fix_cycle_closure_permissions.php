<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCycleClosurePermissions extends Migration
{
    public function up()
    {
        // Localizar el module_id de CycleClosure por su ruta raíz
        $moduleId = DB::table('permissions')
            ->where('route', 'cycle_closure.index')
            ->where('type', 1)
            ->value('module_id');

        if (! $moduleId) {
            return;
        }

        // Eliminar todos los registros del módulo (están corruptos con id=0
        // porque insertGetId() retorna 0 en tablas sin AUTO_INCREMENT)
        DB::table('permissions')->where('module_id', $moduleId)->delete();

        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $rootPermId     = $nextId++;
        $closuresPermId = $nextId++;
        $settingsPermId = $nextId++;

        // ── Type 1: Raíz ─────────────────────────────────────────────
        DB::table('permissions')->insert([
            'id' => $rootPermId, 'module_id' => $moduleId, 'parent_id' => null,
            'name' => 'Cycle Closure', 'translation' => 'cycleclosure::menu.cycle_closure',
            'route' => 'cycle_closure.index', 'type' => 1, 'status' => 1,
            'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── Type 2: Secciones ─────────────────────────────────────────
        DB::table('permissions')->insert([
            [
                'id' => $closuresPermId, 'module_id' => $moduleId, 'parent_id' => $rootPermId,
                'name' => 'Cycle Closures', 'translation' => 'cycleclosure::menu.closures',
                'route' => 'cycle_closure.index', 'type' => 2, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => $settingsPermId, 'module_id' => $moduleId, 'parent_id' => $rootPermId,
                'name' => 'Cycle Settings', 'translation' => 'cycleclosure::menu.settings',
                'route' => 'cycle_closure.settings.index', 'type' => 2, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // ── Type 3: Acciones bajo Cierres ─────────────────────────────
        DB::table('permissions')->insert([
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure List', 'translation' => 'cycleclosure::menu.closure_list',
                'route' => 'cycle_closure.index', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Run', 'translation' => 'cycleclosure::menu.closure_run',
                'route' => 'cycle_closure.executor-approve', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Approve', 'translation' => 'cycleclosure::menu.closure_approve',
                'route' => 'cycle_closure.coapprover-approve', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Detail', 'translation' => 'cycleclosure::menu.closure_detail',
                'route' => 'cycle_closure.show', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Acta', 'translation' => 'cycleclosure::menu.closure_acta',
                'route' => 'cycle_closure.acta', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Status', 'translation' => 'cycleclosure::menu.closure_status',
                'route' => 'cycle_closure.status', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            // ── Type 3: Acciones bajo Configuración ──────────────────
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $settingsPermId,
                'name' => 'Settings View', 'translation' => 'cycleclosure::menu.settings_view',
                'route' => 'cycle_closure.settings.index', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => $nextId++, 'module_id' => $moduleId, 'parent_id' => $settingsPermId,
                'name' => 'Settings Save', 'translation' => 'cycleclosure::menu.settings_save',
                'route' => 'cycle_closure.settings.store', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        // Esta migración corrige datos corruptos (permisos con id=0 causados por
        // insertGetId() en tablas sin AUTO_INCREMENT). No existe rollback significativo
        // porque restaurar los registros corruptos empeoraría el estado del sistema.
    }
}
