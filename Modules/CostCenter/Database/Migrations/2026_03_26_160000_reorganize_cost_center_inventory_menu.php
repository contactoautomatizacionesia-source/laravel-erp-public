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
        // 1. Buscamos el item de menú "Inventario" de Centro de Costos
        $inventoryMenu = DB::table('backendmenus')
            ->where('name', 'cost_center.inventory')
            ->first();

        if ($inventoryMenu) {
            // A. Convertir "Inventario" en un padre (limpiar ruta)
            DB::table('backendmenus')
                ->where('id', $inventoryMenu->id)
                ->update(['route' => null]);

            // B. Insertar sub-items
            DB::table('backendmenus')->insert([
                [
                    'name'        => 'costcenter::inventory.principal',
                    'icon'        => 'ti-home',
                    'user_id'     => null,
                    'parent_id'   => $inventoryMenu->id,
                    'is_admin'    => 1,
                    'is_seller'   => 0,
                    'route'       => 'inventory_entry.index',
                    'position'    => 1,
                    'module'      => '',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'name'        => 'costcenter::inventory.cost_center',
                    'icon'        => 'ti-archive',
                    'user_id'     => null,
                    'parent_id'   => $inventoryMenu->id,
                    'is_admin'    => 1,
                    'is_seller'   => 0,
                    'route'       => 'cost_centers.inventory.index',
                    'position'    => 2,
                    'module'      => '',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $inventoryMenu = DB::table('backendmenus')
            ->where('name', 'cost_center.inventory')
            ->first();

        if ($inventoryMenu) {
            // Eliminar hijos
            DB::table('backendmenus')
                ->where('parent_id', $inventoryMenu->id)
                ->whereIn('name', ['costcenter::inventory.principal', 'costcenter::inventory.cost_center'])
                ->delete();

            // Restaurar ruta original
            DB::table('backendmenus')
                ->where('id', $inventoryMenu->id)
                ->update(['route' => 'cost_centers.inventory.index']);
        }
    }
};
