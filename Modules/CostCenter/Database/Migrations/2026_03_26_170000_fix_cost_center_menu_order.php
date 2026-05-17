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
        // 1. Buscamos el menú padre (Centro de Costos)
        $parentMenu = DB::table('backendmenus')
            ->where('name', 'cost_center.cost_centers')
            ->first();

        if ($parentMenu) {
            // 2. Forzamos el orden de los hijos
            // Divisiones -> Posición 1
            DB::table('backendmenus')
                ->where('parent_id', $parentMenu->id)
                ->where('name', 'cost_center.divisions')
                ->update(['position' => 1]);

            // Inventario -> Posición 2
            DB::table('backendmenus')
                ->where('parent_id', $parentMenu->id)
                ->where('name', 'cost_center.inventory')
                ->update(['position' => 2]);

            // Transacciones -> Posición 3 (Probamos ambos nombres posibles en los entornos)
            DB::table('backendmenus')
                ->where('parent_id', $parentMenu->id)
                ->whereIn('name', ['costcenter::inventory.transactions', 'costcenter::inventory.all_transactions'])
                ->update(['position' => 3]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $parentMenu = DB::table('backendmenus')
            ->where('name', 'cost_center.cost_centers')
            ->first();

        if ($parentMenu) {
            // Resetear posiciones a un valor genérico o revertir según la lógica deseada.
            // Aquí los regresamos a 1, 2, 3 que es el estándar en local.
            DB::table('backendmenus')->where('parent_id', $parentMenu->id)->where('name', 'cost_center.divisions')->update(['position' => 1]);
            DB::table('backendmenus')->where('parent_id', $parentMenu->id)->where('name', 'cost_center.inventory')->update(['position' => 2]);
            DB::table('backendmenus')->where('parent_id', $parentMenu->id)->whereIn('name', ['costcenter::inventory.transactions', 'costcenter::inventory.all_transactions'])->update(['position' => 3]);
        }
    }
};
