<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dian_settings', function (Blueprint $table) {
            // --- Información de la Resolución ---
            $table->string('resolution_number', 255)
                ->after('last_response')
                ->comment('Número de resolución DIAN')
                ->nullable();
            
            $table->date('resolution_date')
                ->after('resolution_number')
                ->comment('Fecha de expedición de la resolución')
                ->nullable();
            
            // --- Rango de Numeración Autorizado ---
            $table->bigInteger('invoice_number_from')
                ->after('resolution_date')
                ->comment('Número inicial del rango autorizado')
                ->nullable();
            
            $table->bigInteger('invoice_number_to')
                ->after('invoice_number_from')
                ->comment('Número final del rango autorizado')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dian_settings', function (Blueprint $table) {
            $table->dropColumn([
                'resolution_number',
                'resolution_date',
                'invoice_number_from',
                'invoice_number_to'
            ]);
        });
    }
};
