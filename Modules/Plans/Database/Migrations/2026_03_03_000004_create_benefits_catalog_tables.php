<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBenefitsCatalogTables extends Migration
{
    public function up(): void
    {
        // =========================================================
        // TABLAS CATÁLOGO DE BENEFICIOS
        // =========================================================

        Schema::create('benefit_category_type', function (Blueprint $table) {
            $table->id();
            $table->json('label')->comment('Económico, Descuento, Permiso, Referidos, Premio');
            $table->string('key', 50)->unique();
            $table->timestamps();
        });

        Schema::create('benefit_category', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('key', 80)->unique();
            $table->json('description')->nullable();
            $table->foreignId('benefit_category_type_id')->nullable()->constrained('benefit_category_type')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('benefit_form_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefit_category_id')->constrained('benefit_category')->onDelete('cascade');
            $table->json('section_label');
            $table->string('section_key', 50);
            $table->integer('section_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('benefit_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefit_form_section_id')->constrained('benefit_form_sections')->onDelete('cascade');
            $table->json('field_label');
            $table->string('field_key', 50);
            $table->enum('field_type', ['number', 'select', 'boolean', 'text'])->default('text');
            $table->boolean('is_required')->default(true);
            $table->json('help_text')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_form_fields');
        Schema::dropIfExists('benefit_form_sections');
        Schema::dropIfExists('benefit_category');
        Schema::dropIfExists('benefit_category_type');
    }
}
