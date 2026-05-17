<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCycleClosureMenuAndPermissions extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. PERMISOS
        // =========================================================
        $nextModuleId = (DB::table('permissions')->max('module_id') ?? 0) + 1;

        // Permiso raíz del módulo
        $rootPermId = DB::table('permissions')->insertGetId([
            'module_id'   => $nextModuleId,
            'parent_id'   => null,
            'name'        => 'Cycle Closure',
            'translation' => 'cycleclosure::menu.cycle_closure',
            'route'       => 'cycle_closure.index',
            'type'        => 1,
            'status'      => 1,
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Sub-módulo: Cierres
        $closuresPermId = DB::table('permissions')->insertGetId([
            'module_id'   => $nextModuleId,
            'parent_id'   => $rootPermId,
            'name'        => 'Cycle Closures',
            'translation' => 'cycleclosure::menu.closures',
            'route'       => 'cycle_closure.index',
            'type'        => 2,
            'status'      => 1,
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Sub-módulo: Configuración
        $settingsPermId = DB::table('permissions')->insertGetId([
            'module_id'   => $nextModuleId,
            'parent_id'   => $rootPermId,
            'name'        => 'Cycle Settings',
            'translation' => 'cycleclosure::menu.settings',
            'route'       => 'cycle_closure.settings.index',
            'type'        => 2,
            'status'      => 1,
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Acciones bajo Cierres
        DB::table('permissions')->insert([
            [
                'module_id' => $nextModuleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure List', 'translation' => 'cycleclosure::menu.closure_list',
                'route' => 'cycle_closure.index', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'module_id' => $nextModuleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Run', 'translation' => 'cycleclosure::menu.closure_run',
                'route' => 'cycle_closure.run', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'module_id' => $nextModuleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Approve', 'translation' => 'cycleclosure::menu.closure_approve',
                'route' => 'cycle_closure.approve', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'module_id' => $nextModuleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Detail', 'translation' => 'cycleclosure::menu.closure_detail',
                'route' => 'cycle_closure.show', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'module_id' => $nextModuleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Acta', 'translation' => 'cycleclosure::menu.closure_acta',
                'route' => 'cycle_closure.acta', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'module_id' => $nextModuleId, 'parent_id' => $closuresPermId,
                'name' => 'Closure Status', 'translation' => 'cycleclosure::menu.closure_status',
                'route' => 'cycle_closure.status', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            // Acciones bajo Configuración
            [
                'module_id' => $nextModuleId, 'parent_id' => $settingsPermId,
                'name' => 'Settings View', 'translation' => 'cycleclosure::menu.settings_view',
                'route' => 'cycle_closure.settings.index', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'module_id' => $nextModuleId, 'parent_id' => $settingsPermId,
                'name' => 'Settings Save', 'translation' => 'cycleclosure::menu.settings_save',
                'route' => 'cycle_closure.settings.store', 'type' => 3, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // =========================================================
        // 2. MENÚ LATERAL (backendmenus)
        // =========================================================
        $financeParentId = DB::table('backendmenus')
            ->where('name', 'common.finance')
            ->value('id');

        if (! $financeParentId) {
            throw new \LogicException('backendmenus: parent "common.finance" not found. Cannot insert CycleClosure menu.');
        }

        $lastPosition = DB::table('backendmenus')
            ->where('parent_id', $financeParentId)
            ->max('position') ?? 0;

        $cycleMenuId = DB::table('backendmenus')->insertGetId([
            'name'       => 'cycleclosure::menu.cycles',
            'icon'       => 'ti-reload',
            'user_id'    => null,
            'parent_id'  => $financeParentId,
            'is_admin'   => 1,
            'is_seller'  => 0,
            'route'      => 'cycle_closure.index',
            'position'   => $lastPosition + 1,
            'module'     => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('backendmenus')->insert([
            [
                'name'       => 'cycleclosure::menu.closures',
                'icon'       => 'ti-lock',
                'user_id'    => null,
                'parent_id'  => $cycleMenuId,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'route'      => 'cycle_closure.index',
                'position'   => 1,
                'module'     => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'cycleclosure::menu.settings',
                'icon'       => 'ti-settings',
                'user_id'    => null,
                'parent_id'  => $cycleMenuId,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'route'      => 'cycle_closure.settings.index',
                'position'   => 2,
                'module'     => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        $moduleId = DB::table('permissions')
            ->where('route', 'cycle_closure.index')
            ->value('module_id');

        if ($moduleId) {
            DB::table('permissions')->where('module_id', $moduleId)->delete();
        }

        DB::table('backendmenus')->whereIn('name', [
            'cycleclosure::menu.cycles',
            'cycleclosure::menu.closures',
            'cycleclosure::menu.settings',
        ])->delete();
    }
}
