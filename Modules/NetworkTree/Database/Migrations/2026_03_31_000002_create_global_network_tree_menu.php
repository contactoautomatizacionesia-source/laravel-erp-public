<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateGlobalNetworkTreeMenu extends Migration
{
    public function up()
    {
        // =========================================================
        // 1. LÓGICA PARA LA TABLA 'permissions' (Permisos)
        // =========================================================

        // Buscamos el ID del módulo de Customer/Users para anidarlo ahí (o creamos uno nuevo)
        $customerModuleId = DB::table('permissions')
            ->where('name', 'Customer') // o el nombre base que uses para clientes
            ->value('module_id') ?? ((DB::table('permissions')->max('module_id') ?? 0) + 1);

        $customerParentId = DB::table('permissions')
            ->where('name', 'Customer') // o el nombre base que uses para clientes
            ->value('id');

        $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;

        DB::table('permissions')->insert([
            [
                'id'          => $nextId,
                'module_id'   => $customerModuleId,
                'parent_id'   => $customerParentId, // O el ID del permiso padre de clientes si lo prefieres
                'name'        => 'Global Network Tree',
                'translation' => 'tree.network', // Usamos tu traducción
                'route'       => 'network.admin.global_tree',
                'type'        => 2, // 2 suele ser para sub-módulos/vistas
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now()
            ]
        ]);

        // =========================================================
        // 2. LÓGICA PARA LA TABLA 'backendmenus' (Menú Lateral)
        // =========================================================

        // Buscamos el parent_id dinámicamente (common.customer)
        $globalParentId = DB::table('backendmenus')
            ->where('name', 'common.customer')
            ->value('id');

        if ($globalParentId) {
            DB::table('backendmenus')->insert([
                'name'        => 'tree.network',
                'icon'        => 'ti-vector', // Un icono de red/árbol (puedes cambiarlo)
                'user_id'     => null,
                'parent_id'   => $globalParentId,
                'is_admin'    => 1,
                'is_seller'   => 0,
                'route'       => 'network.admin.global_tree',
                'position'    => 99, // Lo ponemos al final de la lista de clientes
                'module'      => '',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down()
    {
        // Limpiamos los permisos
        DB::table('permissions')->where('route', 'network.admin.global_tree')->delete();

        // Limpiamos los menús
        DB::table('backendmenus')->where('route', 'network.admin.global_tree')->delete();
    }
}
