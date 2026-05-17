<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_boxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Referencia a tabla existente de Centros de Costos
            // Nullable: el VAULT (caja madre del sistema) no pertenece a ningún CC
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->onDelete('set null');
            $table->uuid('parent_id')->nullable()
                  ->comment('Referencia jerárquica para flujo de efectivo');
            
            $table->string('code')->unique()->comment('Código de inventario único');
            $table->string('name')->comment('Nombre descriptivo');
            
            $table->enum('type', ['VAULT', 'PRINCIPAL', 'AUXILIARY'])
                  ->comment('Nivel: Caja Fuerte, Principal de Sucursal o Auxiliar');
                  
            $table->decimal('base_amount', 18, 2)->default(0)
                  ->comment('Monto inicial de apertura (no contable)');
                  
            $table->decimal('alert_threshold', 18, 2)->nullable()
                  ->comment('Monto máximo de seguridad en caja');
                  
            $table->string('status', 20)->default('AVAILABLE')
                  ->comment('AVAILABLE, OPEN, MAINTENANCE, INACTIVE');

            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('cash_boxes')->onDelete('set null');
            $table->index(['cost_center_id', 'type', 'status'], 'idx_boxes_operational');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_boxes');
    }
};