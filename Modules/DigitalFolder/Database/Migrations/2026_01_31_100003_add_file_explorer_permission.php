<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

// NOTA: Esta migración ya fue ejecutada e insertó un permiso incorrecto
// (module_id apuntaba a Customer, parent_id tomado de 'cusotmer.list_active').
// La limpieza y el permiso correcto están en la migración 100004.
return new class extends Migration
{
    public function up(): void
    {
        // Migración obsoleta — ver 100004
    }

    public function down(): void
    {
        DB::table('permissions')->where('route', 'admin.file-explorer.index')->delete();
    }
};
