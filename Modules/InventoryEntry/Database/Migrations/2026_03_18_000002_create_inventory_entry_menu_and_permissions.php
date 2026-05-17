<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateInventoryEntryMenuAndPermissions extends Migration
{
    public function up()
    {
        // =========================================================
        // 0. LIMPIEZA PREVENTIVA (por si se ejecutó manualmente antes)
        // =========================================================

        $staleMenu = DB::table('backendmenus')
            ->where('name', 'inventoryentry::inventory.menu_inventory_entry')
            ->first();

        if ($staleMenu) {
            DB::table('backendmenu_users')->where('backendmenu_id', $staleMenu->id)->delete();
            DB::table('backendmenus')->where('id', $staleMenu->id)->delete();
        }

        $staleModuleId = DB::table('permissions')
            ->where('route', 'inventory_entry.index')
            ->whereNull('parent_id')
            ->value('module_id');

        if ($staleModuleId) {
            DB::table('permissions')->where('module_id', $staleModuleId)->delete();
        }

        // =========================================================
        // 1. PERMISOS
        // =========================================================

        $nextModuleId = (DB::table('permissions')->max('module_id') ?? 0) + 1;

        // NOTA: Usamos MAX(id)+N en lugar de insertGetId() porque la columna `id`
        // de `permissions` puede no tener AUTO_INCREMENT activo en todos los entornos.
        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        $parentPermId = $nextId++;

        DB::table('permissions')->insert([
            [
                'id'          => $parentPermId,
                'module_id'   => $nextModuleId,
                'parent_id'   => null,
                'name'        => 'Inventory Entry',
                'translation' => 'inventoryentry::inventory.menu_inventory_entry',
                'route'       => 'inventory_entry.index',
                'type'        => 1,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $nextId++,
                'module_id'   => $nextModuleId,
                'parent_id'   => $parentPermId,
                'name'        => 'Inventory Entries List',
                'translation' => 'inventoryentry::inventory.inventory_entry_management',
                'route'       => 'inventory_entry.index',
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $nextId++,
                'module_id'   => $nextModuleId,
                'parent_id'   => $parentPermId,
                'name'        => 'Create Inventory Entry',
                'translation' => 'inventoryentry::inventory.new_entry',
                'route'       => 'inventory_entry.create',
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $nextId++,
                'module_id'   => $nextModuleId,
                'parent_id'   => $parentPermId,
                'name'        => 'View Inventory Entry Detail',
                'translation' => 'inventoryentry::inventory.entry_detail',
                'route'       => 'inventory_entry.detail',
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // =========================================================
        // 2. MENÚ LATERAL (backendmenus)
        // =========================================================

        // Mismo padre que los ítems de Productos: product.products (id 54)
        $parentMenuId = DB::table('backendmenus')
            ->where('name', 'product.products')
            ->value('id');

        if (!$parentMenuId) {
            throw new \LogicException('backendmenus: parent "product.products" not found. Cannot insert InventoryEntry menu.');
        }

        $lastPosition = DB::table('backendmenus')
            ->where('parent_id', $parentMenuId)
            ->max('position') ?? 0;

        DB::table('backendmenus')->insertGetId([
            'name'       => 'inventoryentry::inventory.menu_inventory_entry',
            'icon'       => 'fa fa-product-hunt',
            'user_id'    => null,
            'parent_id'  => $parentMenuId,
            'is_admin'   => 1,
            'is_seller'  => 0,
            'route'      => 'inventory_entry.index',
            'position'   => $lastPosition + 1,
            'module'     => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        $moduleId = DB::table('permissions')
            ->where('route', 'inventory_entry.index')
            ->where('parent_id', null)
            ->value('module_id');

        if ($moduleId) {
            DB::table('permissions')->where('module_id', $moduleId)->delete();
        }

        DB::table('backendmenus')
            ->where('name', 'inventoryentry::inventory.menu_inventory_entry')
            ->delete();
    }
}
