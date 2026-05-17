<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCustomerRestorePermission extends Migration
{
    public function up()
    {
        // Consultar dinámicamente el Parent ID y Module ID basados en 'customer_panel'
        $parentPermission = DB::table('permissions')
            ->where('route', 'cusotmer.list_active')
            ->first();

        if ($parentPermission) {
            $parentId = $parentPermission->id;
            $moduleId = $parentPermission->module_id;

            // 2. Obtener el siguiente ID manual (simulando autoincremento)
            $nextId = DB::table('permissions')->max('id') + 1;

            // Insertar el nuevo permiso de tipo Sub-módulo (Type 2)
            DB::table('permissions')->insert([
                'id'          => $nextId,
                'module_id'   => $moduleId,
                'parent_id'   => $parentId,
                'name'        => 'Restore Customer',
                'translation' => 'permission.customer_restore',
                'route'       => 'customer.restore',
                'type'        => 2,
                'status'      => 1,
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('permissions')
            ->where('route', 'customer.restore')
            ->delete();
    }
}