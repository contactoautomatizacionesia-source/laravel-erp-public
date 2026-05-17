<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class ReorderInventoryAlertsToCostCenter extends Migration
{
    public function up()
    {
        // 1. Tabla PERMISSIONS
        // Buscamos el ID de Cost Centers
        $costCenterPermission = DB::table('permissions')->where('name', 'Cost Centers')->first();

        if ($costCenterPermission) {
            DB::table('permissions')
                ->where('name', 'Alerts Inventory')
                ->update([
                    'module_id' => $costCenterPermission->module_id,
                    'parent_id' => $costCenterPermission->id,
                    'type' => 2
                ]);
        }

        // 2. Tabla BACKENDMENUS
        // Obtener los IDs de los menús base
        $parentMenu = DB::table('backendmenus')->where('name', 'cost_center.cost_centers')->first();
        $alertsMenu = DB::table('backendmenus')->where('name', 'common.alerts_inventory')->first();

        if ($parentMenu && $alertsMenu) {
            DB::table('backendmenus')
                ->where('id', $alertsMenu->id)
                ->update([
                    'parent_id' => $parentMenu->id,
                    'position' => 3
                ]);

            // 3. Tabla BACKENDMENU_USERS
            // Buscamos los registros de usuarios vinculados al menú asociado al usuario
            $parentMenuUser = DB::table('backendmenu_users')->where('backendmenu_id', $parentMenu->id)->first();

            if ($parentMenuUser) {
                DB::table('backendmenu_users')
                    ->where('backendmenu_id', $alertsMenu->id)
                    ->update([
                        'parent_id' => $parentMenuUser->id
                    ]);
            }
        }
    }

    public function down()
    {
        // Revertir los cambios (opcional, devuelve a parent_id = null o valores originales)
        DB::table('permissions')->where('name', 'Alerts Inventory')->update(['parent_id' => null, 'type' => 1]);
        DB::table('backendmenus')->where('name', 'common.alerts_inventory')->update(['parent_id' => null, 'position' => 1]);
        
        $alertsMenu = DB::table('backendmenus')->where('name', 'common.alerts_inventory')->first();
        if ($alertsMenu) {
            DB::table('backendmenu_users')->where('backendmenu_id', $alertsMenu->id)->update(['parent_id' => null]);
        }
    }
}
