<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryCountsTable extends Migration
{
    public function up()
    {
        Schema::create('inventory_counts', function (Blueprint $table) {
            $table->id();
            $table->string('count_code', 191)->unique()->comment('Ej: CNT-2026-0001');
            $table->unsignedBigInteger('cost_center_id');
            $table->unsignedBigInteger('user_id')->comment('Asesor que realiza el conteo');
            $table->enum('status', ['pending', 'correct', 'incorrect'])->default('pending');
            $table->enum('audit_status', ['pending', 'rejected', 'approved'])->default('pending');
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('observation')->nullable();
            $table->json('device_info')->nullable()->comment('IP, browser, OS, coordenadas');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['cost_center_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_counts');
    }
}
