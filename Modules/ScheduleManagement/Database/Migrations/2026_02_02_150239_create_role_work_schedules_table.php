<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_work_schedules', function (Blueprint $table) {
            $table->id();
            // Identificador único generado por el sistema (ej: SCH-001)
            $table->string('schedule_code')->unique();
            $table->enum('day_type', ['WEEKDAYS', 'SATURDAY', 'SUNDAY'])->default('WEEKDAYS');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(1);
            // Campos de Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            // Relaciones de Auditoría
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            // Registra created_at y updated_at automáticamente
            $table->timestamps();
        });

        // Insertar registros iniciales
        $now = date('Y-m-d H:i:s');
        $schedules = [
            // Turno Partido Mañana
            [
                'schedule_code' => 'SCH-001', 'day_type' => 'WEEKDAYS',
                'start_time' => '08:00:00', 'end_time' => '12:30:00',
                'is_active' => 1, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now
            ],
            // Turno Partido Tarde
            [
                'schedule_code' => 'SCH-002', 'day_type' => 'WEEKDAYS',
                'start_time' => '14:00:00', 'end_time' => '18:00:00',
                'is_active' => 1, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now
            ],
            // Turno Continuo
            [
                'schedule_code' => 'SCH-003', 'day_type' => 'WEEKDAYS',
                'start_time' => '08:00:00', 'end_time' => '17:00:00',
                'is_active' => 1, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now
            ],
            // Turno Sábado
            [
                'schedule_code' => 'SCH-004', 'day_type' => 'SATURDAY',
                'start_time' => '09:00:00', 'end_time' => '13:00:00',
                'is_active' => 1, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now
            ],
        ];

        DB::table('role_work_schedules')->insert($schedules);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_work_schedules');
    }
};
