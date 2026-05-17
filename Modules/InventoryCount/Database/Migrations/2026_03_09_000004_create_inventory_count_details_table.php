<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryCountDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_count_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_count_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('system_stock')->default(0)->comment('Snapshot del stock del sistema al momento del conteo');
            $table->integer('physical_quantity')->nullable()->comment('Cantidad reportada por el asesor');
            $table->unsignedBigInteger('observation_type_id')->nullable();
            $table->boolean('is_draft')->default(true)->comment('true=borrador auto-guardado, false=guardado definitivo');
            $table->timestamps();

            $table->foreign('inventory_count_id')->references('id')->on('inventory_counts')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('observation_type_id')->references('id')->on('system_catalogs')->onDelete('set null');

            $table->unique(['inventory_count_id', 'product_id']);
            $table->index('is_draft');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_count_details');
    }
}
