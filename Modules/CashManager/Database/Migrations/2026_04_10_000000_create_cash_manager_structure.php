<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCashManagerStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // =========================================================
        // 1. LÓGICA PARA LA TABLA 'permissions' (Permisos)
        // =========================================================

        $nextModuleId = (DB::table('permissions')->max('module_id') ?? 0) + 1;
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $parentPermissionId   = $nextId++;
        $operationsPermissionId = $nextId++;
        $assignmentsPermissionId = $nextId++;
        $settingsPermissionId = $nextId++;

        DB::table('permissions')->insert([
            // A. Permiso Padre (Raíz del Módulo)
            ['id' => $parentPermissionId, 'module_id' => $nextModuleId, 'parent_id' => null, 'name' => 'Cash Management', 'translation' => 'cash_manager.cash_management', 'route' => 'cash_manager.index', 'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],

            // B. Sub-módulos (Corresponden a las vistas del sidebar)
            ['id' => $operationsPermissionId, 'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId, 'name' => 'Operations', 'translation' => 'cash_manager.operations', 'route' => 'cash_manager.operations.index', 'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $assignmentsPermissionId, 'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId, 'name' => 'Assignments', 'translation' => 'cash_manager.assignments', 'route' => 'cash_manager.assignments.index', 'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $settingsPermissionId, 'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId, 'name' => 'Settings', 'translation' => 'cash_manager.settings', 'route' => 'cash_manager.settings.index', 'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],

            // C. Permisos de Acción (Los que se validarán en el código)
            // Permiso para ver y operar cierres (Cajero)
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $operationsPermissionId, 'name' => 'view_cash_operations', 'translation' => 'cash_manager.view_operations', 'route' => 'cash_manager.operations.index', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // Permiso para gestionar asignaciones (Líder/Admin)
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $assignmentsPermissionId, 'name' => 'manage_cash_assignments', 'translation' => 'cash_manager.manage_assignments', 'route' => 'cash_manager.assignments.index', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // Permiso para administrar configuraciones (Admin)
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $settingsPermissionId, 'name' => 'admin_cash_settings', 'translation' => 'cash_manager.admin_settings', 'route' => 'cash_manager.settings.index', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =========================================================
        // 2. LÓGICA PARA LA TABLA 'backendmenus' (Menú Lateral)
        // =========================================================

        // Buscamos el parent_id dinámicamente (common.finance), fallback a un ID seguro
        $globalParentId = DB::table('backendmenus')
            ->where('name', 'common.finance')
            ->value('id') ?? 88;

        // Buscamos la posición del menú de ciclos para ubicarnos debajo
        $costCenterPosition = DB::table('backendmenus')
            ->where('name', 'cycleclosure::menu.cycles')
            ->value('position') ?? 99; // Fallback a una posición alta

        // A. Menú Padre (Gestión de Cajas)
        $parentMenuId = DB::table('backendmenus')->insertGetId([
            'name'        => 'cashmanager::cash_manager.cash_management',
            'icon'        => 'ti-wallet',
            'user_id'     => null,
            'parent_id'   => $globalParentId,
            'is_admin'    => 1,
            'is_seller'   => 0,
            'route'       => 'cash_manager.index',
            'position'    => $costCenterPosition + 1,
            'module'      => 'CashManager',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // B. Menús Hijos
        DB::table('backendmenus')->insert([
            [
                'name'        => 'cashmanager::cash_manager.operations',
                'icon'        => 'ti-clipboard',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'cash_manager.operations.index',
                'position'    => 1,
                'module'      => 'CashManager',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'cashmanager::cash_manager.assignments',
                'icon'        => 'ti-user',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'cash_manager.assignments.index',
                'position'    => 2,
                'module'      => 'CashManager',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'cashmanager::cash_manager.settings',
                'icon'        => 'ti-settings',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'cash_manager.settings.index',
                'position'    => 3,
                'module'      => 'CashManager',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Limpiamos los permisos buscando por una ruta única del módulo
        $module_id = DB::table('permissions')->where('route', 'cash_manager.index')->value('module_id');
        if ($module_id) {
            DB::table('permissions')->where('module_id', $module_id)->delete();
        }

        // Limpiamos los menús
        DB::table('backendmenus')->whereIn('name', [
            'cashmanager::cash_manager.cash_management',
            'cashmanager::cash_manager.operations',
            'cashmanager::cash_manager.assignments',
            'cashmanager::cash_manager.settings'
        ])->delete();
    }
}
