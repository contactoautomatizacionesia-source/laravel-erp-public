<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostCenterInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cost_center_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cost_center_id');
            $table->unsignedBigInteger('product_sku_id'); // ID autonumerado de product_sku
            $table->decimal('qty', 16, 2)->default(0); // Cantidad en el centro de costo
            $table->timestamps();

            // Relaciones
            $table->foreign('cost_center_id')
                ->references('id')->on('cost_centers')
                ->onDelete('cascade');

            $table->foreign('product_sku_id')
                ->references('id')->on('product_sku')
                ->onDelete('cascade');

            // Un SKU solo puede estar una vez por centro de costo
            $table->unique(['cost_center_id', 'product_sku_id'], 'unique_center_sku');

            // Índices para búsquedas rápidas
            $table->index('cost_center_id');
            $table->index('product_sku_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_center_inventories');
    }
}
