<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanRulesAndBenefitsTables extends Migration
{
    public function up(): void
    {
        Schema::create('plan_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_child_id')->constrained('plan_child')->onDelete('cascade');
            $table->foreignId('rule_id')->constrained('rule')->onDelete('cascade');
            $table->boolean('is_required')->default(true)
                ->comment('Si la regla es obligatoria para alcanzar o mantener el subplan');
            $table->timestamps();
            $table->unique(['plan_child_id', 'rule_id'], 'plan_rules_child_rule_unique');
        });

        Schema::create('plan_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_child_id')->constrained('plan_child')->onDelete('cascade');
            $table->foreignId('benefit_id')->constrained('benefit')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['plan_child_id', 'benefit_id'], 'plan_benefits_child_benefit_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_benefits');
        Schema::dropIfExists('plan_rules');
    }
}
