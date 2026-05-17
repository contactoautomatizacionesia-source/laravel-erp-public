<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RegisterInventoryExitMenuAndPermissions extends Migration
{
    public function up()
    {
        // =========================================================
        // 0. LIMPIEZA PREVENTIVA (idempotente)
        // =========================================================
        $staleMenu = DB::table('backendmenus')
            ->where('name', 'inventoryexit::menu.inventory_exit')
            ->first();

        if ($staleMenu) {
            DB::table('backendmenu_users')->where('backendmenu_id', $staleMenu->id)->delete();
            DB::table('backendmenus')->where('id', $staleMenu->id)->delete();
        }

        $staleModuleId = DB::table('permissions')
            ->where('route', 'inventory_exit.index')
            ->whereNull('parent_id')
            ->value('module_id');

        if ($staleModuleId) {
            DB::table('permissions')->where('module_id', $staleModuleId)->delete();
        }

        // =========================================================
        // 1. PERMISOS
        // =========================================================
        $nextModuleId = (DB::table('permissions')->max('module_id') ?? 0) + 1;
        $nextId       = (DB::table('permissions')->max('id') ?? 0) + 1;
        $parentPermId = $nextId++;

        DB::table('permissions')->insert([
            [
                'id'          => $parentPermId,
                'module_id'   => $nextModuleId,
                'parent_id'   => null,
                'name'        => 'Inventory Exit',
                'translation' => 'inventoryexit::menu.inventory_exit',
                'route'       => 'inventory_exit.index',
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
                'name'        => 'Inventory Exit List',
                'translation' => 'inventoryexit::menu.exit_list',
                'route'       => 'inventory_exit.index',
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
                'name'        => 'Create Inventory Exit',
                'translation' => 'inventoryexit::menu.create_exit',
                'route'       => 'inventory_exit.store',
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
                'name'        => 'Approve/Reject Inventory Exit',
                'translation' => 'inventoryexit::menu.approve_exit',
                'route'       => 'inventory_exit.approve',
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
                'name'        => 'View Inventory Exit Detail',
                'translation' => 'inventoryexit::menu.detail_exit',
                'route'       => 'inventory_exit.detail',
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // =========================================================
        // 2. MENÚ LATERAL — bajo "Centros de Costos"
        // =========================================================
        $parentMenuId = DB::table('backendmenus')
            ->where('name', 'cost_center.cost_centers')
            ->value('id');

        if (!$parentMenuId) {
            throw new \LogicException('backendmenus: parent "cost_center.cost_centers" not found. Cannot insert InventoryExit menu.');
        }

        $lastPosition = DB::table('backendmenus')
            ->where('parent_id', $parentMenuId)
            ->max('position') ?? 0;

        DB::table('backendmenus')->insert([
            'name'       => 'inventoryexit::menu.inventory_exit',
            'icon'       => 'fa fa-sign-out',
            'user_id'    => null,
            'parent_id'  => $parentMenuId,
            'is_admin'   => 1,
            'is_seller'  => 0,
            'route'      => 'inventory_exit.index',
            'position'   => $lastPosition + 1,
            'module'     => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        $moduleId = DB::table('permissions')
            ->where('route', 'inventory_exit.index')
            ->whereNull('parent_id')
            ->value('module_id');

        if ($moduleId) {
            DB::table('permissions')->where('module_id', $moduleId)->delete();
        }

        $menuId = DB::table('backendmenus')
            ->where('name', 'inventoryexit::menu.inventory_exit')
            ->value('id');

        if ($menuId) {
            DB::table('backendmenu_users')->where('backendmenu_id', $menuId)->delete();
            DB::table('backendmenus')->where('id', $menuId)->delete();
        }
    }
}
