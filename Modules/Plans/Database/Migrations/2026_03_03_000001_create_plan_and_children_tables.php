<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanAndChildrenTables extends Migration
{
    public function up(): void
    {
        // =========================================================
        // CATÁLOGOS DE ESCALA Y TIPO DE CICLO
        // =========================================================

        Schema::create('plan_scale', function (Blueprint $table) {
            $table->id();
            $table->json('label')->comment('Nombre visible: Diario, Semanal, Mensual, Ciclo');
            $table->string('key', 50)->unique()->comment('Clave interna: DAILY, WEEKLY, MONTHLY, CYCLE');
            $table->timestamps();
        });

        Schema::create('plan_cycle_type', function (Blueprint $table) {
            $table->id();
            $table->json('label')->comment('Nombre visible: Quincenal, Mensual, Personalizado');
            $table->string('key', 50)->unique()->comment('Clave interna: BIWEEKLY, MONTHLY, CUSTOM');
            $table->timestamps();
        });

        // =========================================================
        // PLAN PADRE
        // =========================================================

        Schema::create('plan', function (Blueprint $table) {
            $table->id();
            $table->json('title')->comment('Nombre del plan padre. Ej: Life, Platino');
            $table->json('description')->nullable();
            $table->foreignId('plan_scale_id')->constrained('plan_scale')->onDelete('restrict')
                ->comment('Escala de medición del ciclo');
            $table->foreignId('plan_cycle_type_id')->nullable()->constrained('plan_cycle_type')->onDelete('restrict')
                ->comment('Solo aplica si scale = CYCLE');
            $table->integer('custom_days')->nullable()
                ->comment('Solo aplica si cycle_type = CUSTOM');
            $table->integer('order')->unique()->comment('Orden de aparición entre planes padre');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =========================================================
        // PLAN HIJO
        // =========================================================

        Schema::create('plan_child', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plan')->onDelete('cascade');
            $table->json('title')->comment('Nombre del subplan. Ej: Life Platino');
            $table->json('description')->nullable();
            $table->integer('level_order')->comment('Orden jerárquico dentro del plan padre');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['plan_id', 'level_order'], 'plan_child_plan_id_level_order_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_child');
        Schema::dropIfExists('plan');
        Schema::dropIfExists('plan_cycle_type');
        Schema::dropIfExists('plan_scale');
    }
}
