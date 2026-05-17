<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryCountAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_count_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_count_id');
            $table->unsignedBigInteger('user_id')->comment('Asesor que realizó el intento');
            $table->unsignedTinyInteger('attempt_number');
            $table->enum('result', ['correct', 'incorrect']);
            $table->json('device_info')->nullable()->comment('IP, browser, OS, coordenadas, timestamp');
            $table->timestamp('attempted_at')->useCurrent();

            $table->foreign('inventory_count_id')->references('id')->on('inventory_counts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            $table->index(['inventory_count_id', 'attempt_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_count_attempts');
    }
}
