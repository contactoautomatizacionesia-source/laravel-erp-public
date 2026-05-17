<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. LÓGICA PARA LA TABLA 'permissions' (Permisos)
        // Buscamos el permiso padre de Cost Centers
        $parentPermission = DB::table('permissions')
            ->where('route', 'cost_centers.index')
            ->whereNull('parent_id')
            ->first();

        if ($parentPermission) {
            // Usamos MAX(id)+1 para evitar errores de auto-increment en algunos entornos
            $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;
            
            DB::table('permissions')->insert([
                'id'          => $nextId,
                'module_id'   => $parentPermission->module_id,
                'parent_id'   => $parentPermission->id,
                'name'        => 'All Transactions',
                'translation' => 'costcenter::inventory.all_transactions',
                'route'       => 'cost_centers.inventory.all-transactions',
                'type'        => 2, // Sub-módulo
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 2. LÓGICA PARA LA TABLA 'backendmenus' (Menú Lateral)
        // Buscamos el menú padre de Cost Centers
        $parentMenu = DB::table('backendmenus')
            ->where('name', 'cost_center.cost_centers')
            ->first();

        if ($parentMenu) {
            // Buscamos la posición máxima actual para ponerlo al final
            $lastPosition = DB::table('backendmenus')
                ->where('parent_id', $parentMenu->id)
                ->max('position') ?? 2;

            DB::table('backendmenus')->insert([
                'name'        => 'costcenter::inventory.transactions',
                'icon'        => 'ti-list',
                'user_id'     => null,
                'parent_id'   => $parentMenu->id,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'cost_centers.inventory.all-transactions',
                'position'    => $lastPosition + 1,
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('permissions')->where('route', 'cost_centers.inventory.all-transactions')->delete();
        DB::table('backendmenus')->where('route', 'cost_centers.inventory.all-transactions')->delete();
    }
};
