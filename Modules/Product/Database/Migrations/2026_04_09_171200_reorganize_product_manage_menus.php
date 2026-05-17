<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Buscamos el menú padre original
        $parentMenu = DB::table('backendmenus')
            ->where('name', 'product.product_manage')
            ->first();

        if ($parentMenu) {
            // 2. Obtenemos los IDs originales de los submenús (hijos)
            $childrenIds = DB::table('backendmenus')
                ->where('parent_id', $parentMenu->id)
                ->pluck('id');

            if ($childrenIds->isNotEmpty()) {
                // 3. Empujamos TODOS los hijos en la tabla de usuarios al fondo
                DB::table('backendmenu_users')
                    ->whereIn('backendmenu_id', $childrenIds)
                    ->increment('position', 20);
            }

            // 4. Definimos el orden estricto de los prioritarios
            $orderedMenus = [
                'product.products'                     => 1,
                'cost_center.operations'               => 2,
                'inventorycount::menu.inventory_count' => 3,
                'clubpoint.club_point'                 => 4,
            ];

            // 5. Aplicamos el nuevo orden masivamente
            foreach ($orderedMenus as $name => $position) {
                $menuOriginal = DB::table('backendmenus')
                    ->where('parent_id', $parentMenu->id)
                    ->where('name', $name)
                    ->first();

                if ($menuOriginal) {
                    DB::table('backendmenu_users')
                        ->where('backendmenu_id', $menuOriginal->id)
                        ->update(['position' => $position]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Buscamos el menú padre original
        $parentMenu = DB::table('backendmenus')
            ->where('name', 'product.product_manage')
            ->first();

        if ($parentMenu) {
            // 2. Obtenemos los IDs de los submenús
            $childrenIds = DB::table('backendmenus')
                ->where('parent_id', $parentMenu->id)
                ->pluck('id');

            if ($childrenIds->isNotEmpty()) {
                // 3. Reseteamos la posición a 1 para todos los usuarios.
                // Esto anula nuestro orden forzado y hace que el sistema 
                // vuelva a ordenarlos por su comportamiento por defecto (por ID).
                DB::table('backendmenu_users')
                    ->whereIn('backendmenu_id', $childrenIds)
                    ->update(['position' => 1]);
            }
        }
    }
};
