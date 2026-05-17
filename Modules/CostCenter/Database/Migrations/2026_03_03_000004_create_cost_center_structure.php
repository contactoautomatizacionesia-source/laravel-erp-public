<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCostCenterStructure extends Migration
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

        // NOTA: Usamos MAX(id)+N en lugar de insertGetId() porque la columna `id`
        // de `permissions` puede no tener AUTO_INCREMENT activo en todos los entornos.
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $parentPermissionId   = $nextId++;   // id del permiso raíz
        $divisionPermissionId = $nextId++;   // id del sub-módulo Divisions
        $inventoryPermissionId = $nextId++;  // id del sub-módulo Inventory

        DB::table('permissions')->insert([
            // A. Permiso Padre (Raíz)
            ['id' => $parentPermissionId,   'module_id' => $nextModuleId, 'parent_id' => null,                  'name' => 'Cost Centers',   'translation' => 'cost_center.cost_centers', 'route' => 'cost_centers.index',   'type' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // B. Sub-módulos
            ['id' => $divisionPermissionId,  'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,  'name' => 'Divisions',      'translation' => 'cost_center.divisions',     'route' => 'cost_centers.index',   'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $inventoryPermissionId, 'module_id' => $nextModuleId, 'parent_id' => $parentPermissionId,  'name' => 'Inventory',      'translation' => 'cost_center.inventory',     'route' => 'cost_centers.index',   'type' => 2, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            // C. CRUD de Divisiones
            ['id' => $nextId++,              'module_id' => $nextModuleId, 'parent_id' => $divisionPermissionId,'name' => 'Division List',  'translation' => 'cost_center.list',          'route' => 'cost_centers.index',   'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++,              'module_id' => $nextModuleId, 'parent_id' => $divisionPermissionId,'name' => 'Division Create','translation' => 'cost_center.create',         'route' => 'cost_centers.store',   'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++,              'module_id' => $nextModuleId, 'parent_id' => $divisionPermissionId,'name' => 'Division Edit',  'translation' => 'cost_center.edit',           'route' => 'cost_centers.update',  'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => $nextId++,              'module_id' => $nextModuleId, 'parent_id' => $divisionPermissionId,'name' => 'Division Delete','translation' => 'cost_center.delete',         'route' => 'cost_centers.destroy', 'type' => 3, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // =========================================================
        // 2. LÓGICA PARA LA TABLA 'backendmenus' (Menú Lateral)
        // =========================================================

        // Buscamos el parent_id dinámicamente (product.product_manage), fallback 53
        $globalParentId = DB::table('backendmenus')
            ->where('name', 'product.product_manage')
            ->value('id') ?? 53;

        // Buscamos la posición: debajo de product.products (id 235 de fallback)
        $productsPosition = DB::table('backendmenus')
            ->where('name', 'product.products')
            ->value('id') ?? 54;

        // A. Menú Padre (Cost Centers)
        $parentMenuId = DB::table('backendmenus')->insertGetId([
            'name'        => 'cost_center.cost_centers',
            'icon'        => 'ti-home',
            'user_id'     => null,
            'parent_id'   => $globalParentId,
            'is_admin'    => 1,
            'is_seller'   => 0,
            'route'       => 'cost_centers.index',
            'position'    => $productsPosition + 1,
            'module'      => '',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // B. Menú Hijos
        DB::table('backendmenus')->insert([
            [
                'name'        => 'cost_center.divisions',
                'icon'        => 'ti-layers',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'cost_centers.index',
                'position'    => 1,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'cost_center.inventory',
                'icon'        => 'ti-archive',
                'user_id'     => null,
                'parent_id'   => $parentMenuId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'cost_centers.inventory.index', // Apunta a index por ahora
                'position'    => 2,
                'module'      => '',
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
        // Limpiamos los permisos
        DB::table('permissions')->where('module_id', function($query) {
            $query->select('module_id')
                ->from('permissions')
                ->where('route', 'cost_centers.index')
                ->limit(1);
        })->delete();

        // Limpiamos los menús
        DB::table('backendmenus')->whereIn('name', [
            'cost_center.cost_centers',
            'cost_center.divisions',
            'cost_center.inventory'
        ])->delete();
    }
}
