<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryEntryTables extends Migration
{
    public function up()
    {
        // Tabla de lotes
        Schema::create('product_lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number')->unique();
            $table->date('manufacture_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('lot_number');
            $table->index('expiration_date');
        });

        // Tabla de ingresos de inventario (historial)
        Schema::create('product_inventory_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lot_id');
            $table->unsignedBigInteger('product_sku_id');
            $table->decimal('quantity', 16, 2);
            $table->decimal('unit_cost', 16, 2)->nullable();
            $table->string('warehouse_location')->default('Principal');
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('lot_id')->references('id')->on('product_lots');
            $table->foreign('product_sku_id')->references('id')->on('product_sku');

            $table->index('lot_id');
            $table->index('product_sku_id');
            $table->index(['lot_id', 'product_sku_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_inventory_entries');
        Schema::dropIfExists('product_lots');
    }
}
