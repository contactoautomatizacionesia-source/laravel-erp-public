<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPointPriceHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('product_point_price_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('product_sku')->nullable();
            $table->decimal('previous_points', 15, 4)->nullable();
            $table->decimal('new_points', 15, 4)->nullable();
            $table->decimal('previous_price', 15, 4)->nullable();
            $table->decimal('new_price', 15, 4)->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_point_price_history');
    }
}
