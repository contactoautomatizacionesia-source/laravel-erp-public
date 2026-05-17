<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostCenterInventoryMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_center_inventory_movements', function (Blueprint $table) {
            $table->id();

            // Tipo de movimiento (FK a system_catalogs para movement_type)
            $table->unsignedBigInteger('movement_type_id');

            // Origen del movimiento
            $table->enum('source_type', ['main', 'cost_center'])->default('main');
            $table->unsignedBigInteger('source_id')->nullable(); // ID del centro si viene de centro (cost_center_id)

            // Destino del movimiento
            $table->enum('destination_type', ['main', 'cost_center'])->default('cost_center');
            $table->unsignedBigInteger('destination_id')->nullable(); // ID del centro si va a centro (cost_center_id)

            // Producto y cantidad
            $table->unsignedBigInteger('product_sku_id'); // ID autonumerado de product_sku
            $table->decimal('qty', 16, 2); // Cantidad movida

            // Información adicional
            $table->text('reason')->nullable(); // Motivo del movimiento
            $table->string('reference_type')->nullable(); // 'order', 'adjustment', 'transfer', etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID de la orden, ajuste, etc.

            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Relaciones
            $table->foreign('movement_type_id')
                ->references('id')->on('system_catalogs')
                ->onDelete('restrict');

            $table->foreign('product_sku_id')
                ->references('id')->on('product_sku')
                ->onDelete('restrict');

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // Índices para búsquedas rápidas y análisis
            $table->index('movement_type_id');
            $table->index('product_sku_id');
            $table->index('source_type');
            $table->index('destination_type');
            $table->index('created_at'); // Para kardex por fecha
            $table->index(['product_sku_id', 'created_at'], 'idx_sku_created'); // Para historial de SKU
            $table->index(['source_type', 'source_id'], 'idx_source'); // Para buscar movimientos de un centro
            $table->index(['destination_type', 'destination_id'], 'idx_destination'); // Para buscar movimientos hacia un centro
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_center_inventory_movements');
    }
}
