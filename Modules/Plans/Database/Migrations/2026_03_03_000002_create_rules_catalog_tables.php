<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRulesCatalogTables extends Migration
{
    public function up(): void
    {
        // =========================================================
        // TABLAS CATÁLOGO DE REGLAS
        // =========================================================

        Schema::create('rule_category_type', function (Blueprint $table) {
            $table->id();
            $table->json('label')->comment('Nombre visible: Puntos, Cumplimiento, Red');
            $table->string('key', 50)->unique()->comment('Clave interna: POINTS, COMPLIANCE, NETWORK');
            $table->timestamps();
        });

        Schema::create('rule_category', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('key', 50)->unique()->comment('Ej: POINTS_RANGE, PERSONAL_SALES');
            $table->json('description')->nullable();
            $table->foreignId('rule_category_type_id')->constrained('rule_category_type')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('rule_form_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_category_id')->constrained('rule_category')->onDelete('cascade');
            $table->json('section_label');
            $table->string('section_key', 50);
            $table->integer('section_order')->default(1);
            $table->boolean('is_repeatable')->default(false)
                ->comment('true permite agregar N instancias de esta sección');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rule_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_form_section_id')->constrained('rule_form_sections')->onDelete('cascade');
            $table->json('field_label');
            $table->string('field_key', 50);
            $table->enum('field_type', ['number', 'select', 'boolean', 'text'])->default('text');
            $table->boolean('is_required')->default(true);
            $table->json('help_text')->nullable();
            $table->json('validation_rules')->nullable()
                ->comment('JSON con min, max, decimals, options, etc. options puede ser array o METHOD[fn]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('rule_form_fields');
        Schema::dropIfExists('rule_form_sections');
        Schema::dropIfExists('rule_category');
        Schema::dropIfExists('rule_category_type');
    }
}
