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
        // 1. Actualizar "cost_center.cost_centers" a "cost_center.operations"
        DB::table('backendmenus')
            ->where('name', 'cost_center.cost_centers')
            ->update(['name' => 'cost_center.operations']);

        // 2. Actualizar "costcenter::inventory.transactions" a "costcenter::inventory.transfers"
        // Se incluye también el posible nombre 'costcenter::inventory.all_transactions' por consistencia con migraciones previas
        DB::table('backendmenus')
            ->whereIn('name', ['costcenter::inventory.transactions', 'costcenter::inventory.all_transactions'])
            ->update(['name' => 'costcenter::inventory.transfers']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir "cost_center.operations" a "cost_center.cost_centers"
        DB::table('backendmenus')
            ->where('name', 'cost_center.operations')
            ->update(['name' => 'cost_center.cost_centers']);

        // Revertir "costcenter::inventory.transfers" a "costcenter::inventory.transactions"
        DB::table('backendmenus')
            ->where('name', 'costcenter::inventory.transfers')
            ->update(['name' => 'costcenter::inventory.transactions']);
    }
};
