<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateInventoryCountMenuAndPermissions extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. PERMISOS
        // =========================================================
        $nextModuleId = (DB::table('permissions')->max('module_id') ?? 0) + 1;

        // NOTA: Usamos MAX(id)+N en lugar de insertGetId() porque la columna `id`
        // de `permissions` puede no tener AUTO_INCREMENT activo en todos los entornos.
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $parentPermissionId = $nextId++;  // Permiso raíz del módulo
        $settingsPermId     = $nextId++;  // Sub-módulo: Configuración
        $countsPermId       = $nextId++;  // Sub-módulo: Conteos
        $auditsPermId       = $nextId++;  // Sub-módulo: Auditorías

        DB::table('permissions')->insert([
            // Raíz
            ['id' => $parentPermissionId, 'module_id' => $nextModuleId, 'parent_id' => null,              'name' => 'Inventory Count',     'translation' => 'inventorycount::menu.inventory_count', 'route' => 'inventory_count.index',          'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // Sub-módulos
            ['id' => $settingsPermId,     'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,'name' => 'Count Settings', 'translation' => 'inventorycount::menu.settings', 'route' => 'inventory_count.settings.index', 'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $countsPermId,       'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,'name' => 'Counts',         'translation' => 'inventorycount::menu.counts',   'route' => 'inventory_count.index',          'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $auditsPermId,       'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,'name' => 'Count Audits',   'translation' => 'inventorycount::menu.audits',   'route' => 'inventory_count.audits.index',   'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // Acciones CRUD
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $settingsPermId,'name' => 'Count Settings Save','translation' => 'inventorycount::menu.settings_save', 'route' => 'inventory_count.settings.store',  'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $countsPermId,  'name' => 'Count List',         'translation' => 'inventorycount::menu.count_list',     'route' => 'inventory_count.index',           'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $countsPermId,  'name' => 'Count Create',       'translation' => 'inventorycount::menu.count_create',   'route' => 'inventory_count.create',          'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $countsPermId,  'name' => 'Count Detail',       'translation' => 'inventorycount::menu.count_detail',   'route' => 'inventory_count.show',            'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $auditsPermId,  'name' => 'Count Audit List',   'translation' => 'inventorycount::menu.audit_list',     'route' => 'inventory_count.audits.index',    'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++, 'module_id' => $nextModuleId, 'parent_id' => $auditsPermId,  'name' => 'Count Audit Review', 'translation' => 'inventorycount::menu.audit_review',   'route' => 'inventory_count.audits.store',    'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =========================================================
        // 2. MENÚ LATERAL (backendmenus)
        // =========================================================

        // Mismo padre que CostCenter: product.product_manage
        $globalParentId = DB::table('backendmenus')
            ->where('name', 'product.product_manage')
            ->value('id');

        if (! $globalParentId) {
            throw new \LogicException('backendmenus: parent "product.product_manage" not found. Cannot insert InventoryCount menu.');
        }

        // Posición: después del último hijo de product.product_manage
        $lastPosition = DB::table('backendmenus')
            ->where('parent_id', $globalParentId)
            ->max('position') ?? 0;

        // Menú padre: Conteo de Inventario
        $parentMenuId = DB::table('backendmenus')->insertGetId([
            'name'       => 'inventorycount::menu.inventory_count',
            'icon'       => 'ti-clipboard',
            'user_id'    => null,
            'parent_id'  => $globalParentId,
            'is_admin'   => 1,
            'is_seller'  => 0,
            'route'      => 'inventory_count.index',
            'position'   => $lastPosition + 1,
            'module'     => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Menú hijos
        DB::table('backendmenus')->insert([
            [
                'name'       => 'inventorycount::menu.settings',
                'icon'       => 'ti-settings',
                'user_id'    => null,
                'parent_id'  => $parentMenuId,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'route'      => 'inventory_count.settings.index',
                'position'   => 1,
                'module'     => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'inventorycount::menu.counts',
                'icon'       => 'ti-check-box',
                'user_id'    => null,
                'parent_id'  => $parentMenuId,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'route'      => 'inventory_count.index',
                'position'   => 2,
                'module'     => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'inventorycount::menu.audits',
                'icon'       => 'ti-eye',
                'user_id'    => null,
                'parent_id'  => $parentMenuId,
                'is_admin'   => 1,
                'is_seller'  => 0,
                'route'      => 'inventory_count.audits.index',
                'position'   => 3,
                'module'     => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        // Obtener module_id primero para evitar subconsulta sobre la misma tabla (MySQL)
        $moduleId = DB::table('permissions')
            ->where('route', 'inventory_count.index')
            ->value('module_id');

        if ($moduleId) {
            DB::table('permissions')->where('module_id', $moduleId)->delete();
        }

        DB::table('backendmenus')->whereIn('name', [
            'inventorycount::menu.inventory_count',
            'inventorycount::menu.settings',
            'inventorycount::menu.counts',
            'inventorycount::menu.audits',
        ])->delete();
    }
}
