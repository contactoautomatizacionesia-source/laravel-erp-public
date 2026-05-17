<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Declaramos la constante para evitar código duplicado (SonarQube fix)
    private const SET_NULL = 'set null';

    public function up()
    {
        Schema::create('cost_center_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movement_type_id')->nullable();

            // Origen
            $table->enum('source_type', ['main', 'cost_center'])->default('main');
            $table->unsignedBigInteger('source_id')->nullable();

            // Destino
            $table->enum('destination_type', ['main', 'cost_center'])->default('cost_center');
            $table->unsignedBigInteger('destination_id')->nullable();

            // Totales
            $table->integer('total_products')->default(0);
            $table->decimal('total_qty', 16, 2)->default(0);

            // Detalles y Auditoría
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('dispatched_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Relaciones
            $table->foreign('movement_type_id')->references('id')->on('system_catalogs')->onDelete('restrict');

            // Usamos la constante en lugar del string quemado
            $table->foreign('dispatched_by')->references('id')->on('users')->onDelete(self::SET_NULL);
            $table->foreign('received_by')->references('id')->on('users')->onDelete(self::SET_NULL);
            $table->foreign('created_by')->references('id')->on('users')->onDelete(self::SET_NULL);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cost_center_transfers');
    }
};
