<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemCatalogsTable extends Migration
{
    public function up()
    {
        Schema::create('system_catalogs', function (Blueprint $table) {
            $table->id();

            // EL FILTRO MAESTRO: 'gender', 'profession', 'lead_source', etc.
            $table->string('type', 50)->index();

            // CÓDIGO INTERNO: Opcional, para lógica dura (Ej: 'CC' para Cédula)
            $table->string('code', 50)->nullable();

            // DATOS:
            $table->json('name'); // Traducible (Spatie)
            $table->json('meta')->nullable(); // Flexible (Color, Icono, Valor extra)

            // CONTROL:
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes(); // Integridad Referencial Histórica

            // ÍNDICES PARA VELOCIDAD EXTREMA:
            // 1. Búsqueda típica: "Dame todas las Profesiones activas ordenadas"
            $table->index(['type', 'is_active', 'sort_order']);
            
            // 2. Integridad de Datos: No permitir dos códigos iguales en el mismo tipo
            $table->unique(['type', 'code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_catalogs');
    }
}