<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Esta migración corrige:
     * 1. La traducción incorrecta de Event (ID 453)
     * 2. Los IDs duplicados reales en la tabla permissions
     */
    public function up(): void
    {
        // 1. Corregir la traducción de Event (ID 453)
        // Estaba usando 'permission.holiday_setup' cuando debería ser 'permission.event'
        DB::table('permissions')
            ->where('id', 453)
            ->update(['translation' => 'permission.event']);

        // 2. Obtener el ID máximo actual para reasignar duplicados
        $maxId = DB::table('permissions')->max('id');
        $newId = $maxId + 1;

        // 3. Eliminar y reasignar Event Delete (ID 457 duplicado)
        DB::table('permissions')
            ->where('id', 457)
            ->where('route', 'events.delete')
            ->delete();

        DB::table('permissions')->insert([
            'id' => $newId++,
            'module_id' => 30,
            'parent_id' => 453,
            'name' => 'Delete',
            'route' => 'events.delete',
            'type' => 3,
            'translation' => 'permission.delete',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 4. Eliminar duplicado de ID 482 (mantener solo Category CSV)
        DB::table('permissions')
            ->where('id', 482)
            ->where('route', 'review.seller.index')
            ->delete();

        // 5. Eliminar y reasignar Approve All (ID 483 duplicado)
        DB::table('permissions')
            ->where('id', 483)
            ->where('route', 'review.seller.approve-all')
            ->delete();

        DB::table('permissions')->insert([
            'id' => $newId++,
            'module_id' => null,
            'parent_id' => null,
            'name' => 'Approve All',
            'route' => 'review.seller.approve-all',
            'type' => 3,
            'translation' => 'permission.approve_all',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 6. Eliminar y reasignar Seller Reviews Report (ID 537 duplicado)
        DB::table('permissions')
            ->where('id', 537)
            ->where('route', 'report.seller_review')
            ->delete();

        DB::table('permissions')->insert([
            'id' => $newId++,
            'module_id' => null,
            'parent_id' => 516,
            'name' => 'Seller Reviews',
            'route' => 'report.seller_review',
            'type' => 2,
            'translation' => 'permission.seller_reviews',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 7. Eliminar y reasignar Remove Visitor (ID 734 duplicado)
        DB::table('permissions')
            ->where('id', 734)
            ->where('route', 'utilities.remove_visitor')
            ->delete();

        DB::table('permissions')->insert([
            'id' => $newId++,
            'module_id' => null,
            'parent_id' => 631,
            'name' => 'Remove Visitor',
            'route' => 'utilities.remove_visitor',
            'type' => 2,
            'translation' => 'permission.remove_visitor',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir ya que estamos corrigiendo datos inconsistentes
    }
};
