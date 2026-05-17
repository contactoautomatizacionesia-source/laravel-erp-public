<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parameter_settings', function (Blueprint $table) {
            // 1. Eliminar la clave foránea anterior
            $table->dropForeign(['staff_id']);

            // 2. Volver a crearla apuntando a la tabla 'staff'
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('set null');
        });

        // 3. Actualizar los nombres de los parámetros a los nuevos slugs de traducción
        DB::table('parameter_settings')->where('slug', 'product-stock')->update(['parameter_name' => 'product_stock']);
        DB::table('parameter_settings')->where('slug', 'daily-count-failures')->update(['parameter_name' => 'daily_count_errors']);
        DB::table('parameter_settings')->where('slug', 'double-approval')->update(['parameter_name' => 'double_approval']);
        DB::table('parameter_settings')->where('slug', 'cash-opening')->update(['parameter_name' => 'cash_opening']);
    }

    public function down(): void
    {
        Schema::table('parameter_settings', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
        });
    }
};
