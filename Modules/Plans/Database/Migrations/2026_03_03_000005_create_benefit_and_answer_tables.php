<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBenefitAndAnswerTables extends Migration
{
    public function up(): void
    {
        Schema::create('benefit', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->foreignId('benefit_category_id')->constrained('benefit_category')->onDelete('restrict');
            $table->integer('times_triggered')->nullable()
                ->comment('Cuántas veces puede dispararse este beneficio. Null = sin límite');
            $table->boolean('is_cumulative')->default(true)
                ->comment('Indica si se acumula con otros beneficios del mismo tipo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('benefit_form_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('benefit_id')->constrained('benefit')->onDelete('cascade');
            $table->foreignId('benefit_form_field_id')->constrained('benefit_form_fields')->onDelete('cascade');
            $table->text('answer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_form_answers');
        Schema::dropIfExists('benefit');
    }
}
