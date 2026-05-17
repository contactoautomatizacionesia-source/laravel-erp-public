<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_denominations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Referencia a tabla existente 'countries'
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            
            $table->enum('type', ['BILLETE', 'MONEDA'])
                  ->comment('Clasificación física de la denominación');
                  
            $table->decimal('value', 18, 2)
                  ->comment('Valor nominal (ej. 50000.00)');
                  
            $table->string('image_url')->nullable()
                  ->comment('URL del recurso visual para el front-end');
                  
            $table->boolean('is_active')->default(true)
                  ->comment('Estado lógico de la denominación');

            $table->timestamps();
            $table->softDeletes(); // Integridad histórica si una moneda sale de circulación

            $table->index(['country_id', 'is_active'], 'idx_denominations_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_denominations');
    }
};