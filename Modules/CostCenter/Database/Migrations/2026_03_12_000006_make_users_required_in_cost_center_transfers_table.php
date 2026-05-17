<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // PASO 1: Eliminar las relaciones restrictivas actuales
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->dropForeign(['dispatched_by']);
            $table->dropForeign(['received_by']);
        });

        // PASO 2: Ahora que no hay relación que moleste, cambiamos la columna a obligatoria
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('dispatched_by')->nullable(false)->change();
            $table->unsignedBigInteger('received_by')->nullable(false)->change();
        });

        // PASO 3: Volver a crear las relaciones con la nueva regla
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->foreign('dispatched_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down()
    {
        // Quitamos las relaciones restrictivas
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->dropForeign(['dispatched_by']);
            $table->dropForeign(['received_by']);
        });

        // Volvemos a permitir nulos
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('dispatched_by')->nullable()->change();
            $table->unsignedBigInteger('received_by')->nullable()->change();
        });

        // Restauramos el comportamiento original
        Schema::table('cost_center_transfers', function (Blueprint $table) {
            $table->foreign('dispatched_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
