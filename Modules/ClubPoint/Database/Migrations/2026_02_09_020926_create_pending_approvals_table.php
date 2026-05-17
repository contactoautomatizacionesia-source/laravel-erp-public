<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingApprovalsTable extends Migration
{
    public function up()
    {
        Schema::create('pending_approvals', function (Blueprint $table) {
            $table->id();

            // Identificador único para el link del correo
            $table->string('hash', 64)->unique();

            // Qué módulo es (ej: 'ClubPoint')
            $table->string('module');

            // Qué acción específica es (ej: 'set_range', 'set_global', 'convert_wallet')
            $table->string('action_type');

            // Aquí guardamos la DATA NUEVA que se quiere aplicar (JSON)
            // Ej: {"min_price": 0, "max_price": 100, "point": 5}
            $table->json('new_data');

            // Sirve para mostrar en el modal "Antes: 5 -> Ahora: 10"
            $table->json('original_data')->nullable();

            // Quién lo solicitó
            $table->unsignedBigInteger('requester_id');

            // Estado: 0: Pendiente, 1: Aprobado, 2: Rechazado
            $table->tinyInteger('status')->default(0)->comment('0: Pending, 1: Approved, 2: Rejected');

            // Para que el aprobador explique por qué rechazó (si aplica)
            $table->text('rejection_reason')->nullable();

            // Quién debe aprobar la solicitud
            $table->unsignedBigInteger('assigned_approver_id')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_approvals');
    }
}
