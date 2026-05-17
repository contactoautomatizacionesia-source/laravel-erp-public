<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrepreneurPlanHistoryTable extends Migration
{
    public function up(): void
    {
        Schema::create('entrepreneur_plan_history', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')
                ->comment('Empresario al que se le asignó el plan');

            $table->unsignedBigInteger('plan_child_id')
                ->comment('Plan asignado');

            $table->unsignedBigInteger('assigned_by')
                ->nullable()
                ->comment('User que realizó la asignación (admin/sistema)');

            // Razón de la asignación — string libre, no enum en BD.
            // Constantes en el modelo: REASON_INITIAL, REASON_UPGRADE, REASON_DOWNGRADE, REASON_MANUAL
            $table->string('assigned_reason', 50)
                ->nullable()
                ->comment('Razón: initial_registration, upgrade, admin_manual, downgrade, etc.');

            $table->timestamp('started_at')
                ->comment('Desde cuándo tiene este plan');

            $table->timestamp('ended_at')
                ->nullable()
                ->comment('Hasta cuándo tuvo este plan. NULL = plan activo actualmente');

            $table->timestamps();

            // Índice principal: obtener plan activo del empresario
            $table->index(['user_id', 'ended_at'], 'idx_eph_user_active');

            // Índice para historial cronológico
            $table->index(['user_id', 'started_at'], 'idx_eph_user_history');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('plan_child_id')
                ->references('id')->on('plan_child')
                ->onDelete('restrict'); // No eliminar un plan que tiene historial

            $table->foreign('assigned_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrepreneur_plan_history');
    }
}
