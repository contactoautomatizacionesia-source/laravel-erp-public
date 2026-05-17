<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCostCenterInventoryLotsTable extends Migration
{
    public function up()
    {
        Schema::create('cost_center_inventory_lots', function (Blueprint $table) {
            $table->id();
            $table->enum('location_type', ['main', 'cost_center'])->default('cost_center');
            $table->unsignedBigInteger('location_id')->nullable(); // cost_center_id when location_type=cost_center
            $table->unsignedBigInteger('product_sku_id');
            $table->unsignedBigInteger('lot_id');
            $table->decimal('qty', 16, 2)->default(0);
            $table->timestamps();

            $table->foreign('product_sku_id')
                ->references('id')->on('product_sku')
                ->onDelete('restrict');

            $table->foreign('lot_id')
                ->references('id')->on('product_lots')
                ->onDelete('restrict');

            $table->index(['location_type', 'location_id'], 'idx_ccil_location');
            $table->index(['product_sku_id', 'lot_id'], 'idx_ccil_sku_lot');
            $table->unique(['location_type', 'location_id', 'product_sku_id', 'lot_id'], 'uniq_ccil_location_sku_lot');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cost_center_inventory_lots');
    }
}
