<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashClosingIncidentsTable extends Migration
{
    public function up()
    {
        Schema::create('cash_closing_incidents', function (Blueprint $table) {
            $table->id();

            // FK a cash_closings sin constraint explícita — la tabla pertenece a otro módulo
            // y puede no existir en todos los entornos. Agregar FK manualmente si se desea.
            $table->unsignedBigInteger('cash_closing_id');

            // Una novedad solo puede pertenecer a un cierre (UNIQUE)
            $table->uuid('incident_id')->unique();
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('restrict');

            // Snapshot del valor al momento de vincular
            $table->decimal('value_snapshot', 18, 2);

            $table->timestamp('included_at')->useCurrent();
            $table->foreignId('included_by')->constrained('users')->onDelete('restrict');

            $table->index('cash_closing_id', 'idx_cci_cash_closing');
            // incident_id ya tiene índice por el UNIQUE
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_closing_incidents');
    }
}
