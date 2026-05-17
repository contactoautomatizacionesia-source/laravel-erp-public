<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class AddInventoryAlertsToBackendmenusAndPermissions extends Migration
{
    public function up()
    {

        $nextPermissionId = DB::table('permissions')->max('id') + 1;

        DB::table('permissions')->insert([
            'id'          => $nextPermissionId,
            'module_id'   => 1,
            'parent_id'   => null,
            'name'        => 'Alerts Inventory',
            'translation' => 'permission.alerts_inventory',
            'route'       => 'product.inventory_alerts',
            'type'        => 1,
            'status'      => 1,
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $nextMenuId = DB::table('backendmenus')->max('id') + 1;

        DB::table('backendmenus')->insert([
            'id'          => $nextMenuId,
            'name'        => 'common.alerts_inventory',
            'icon'        => 'ti-bell',
            'user_id'     => null,
            'parent_id'   => 53,
            'is_admin'    => 1,
            'is_seller'   => 0,
            'route'       => 'product.inventory_alerts',
            'position'    => 2,
            'module'      => '',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down()
    {
        // Revertir los cambios
        DB::table('permissions')->where('route', 'product.inventory_alerts')->delete();
        DB::table('backendmenus')->where('route', 'product.inventory_alerts')->delete();
    }
}
