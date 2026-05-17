<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRuleAndAnswerTables extends Migration
{
    public function up(): void
    {
        Schema::create('rule', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->comment('Identificador de negocio: R1, R17...');
            $table->json('title');
            $table->json('description')->nullable();
            $table->foreignId('rule_category_id')->constrained('rule_category')->onDelete('restrict');
            $table->integer('times_triggered')->nullable()
                ->comment('Cuántas veces puede dispararse esta regla en la vida del empresario. Null = sin límite');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rule_form_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('rule')->onDelete('cascade');
            $table->foreignId('rule_form_field_id')->constrained('rule_form_fields')->onDelete('cascade');
            $table->text('answer')->nullable();
            $table->integer('repeat_index')->nullable()
                ->comment('null en campos normales; 0,1,2... en secciones repetibles');
            $table->timestamps();
        });

        Schema::create('rule_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_rule_id')->constrained('rule')->onDelete('cascade');
            $table->foreignId('child_rule_id')->constrained('rule')->onDelete('cascade');
            $table->enum('operator', ['AND', 'OR'])->default('AND')
                ->comment('Cómo se une este hijo con el siguiente en la evaluación');
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_dependencies');
        Schema::dropIfExists('rule_form_answers');
        Schema::dropIfExists('rule');
    }
}
