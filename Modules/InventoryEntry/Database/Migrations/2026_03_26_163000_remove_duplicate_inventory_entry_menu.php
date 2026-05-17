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
        // Buscamos el ID del padre "product.products" para asegurarnos de borrar el correcto
        $parentMenuId = DB::table('backendmenus')
            ->where('name', 'product.products')
            ->value('id');

        if ($parentMenuId) {
            DB::table('backendmenus')
                ->where('name', 'inventoryentry::inventory.menu_inventory_entry')
                ->where('parent_id', $parentMenuId)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Restaurar el ítem de menú en "Productos"
        $parentMenuId = DB::table('backendmenus')
            ->where('name', 'product.products')
            ->value('id') ?? 54;

        $lastPosition = DB::table('backendmenus')
            ->where('parent_id', $parentMenuId)
            ->max('position') ?? 0;

        DB::table('backendmenus')->insert([
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
};
