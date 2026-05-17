<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create rule_form_options ──────────────────────────────────────
        Schema::create('rule_form_options', function (Blueprint $table) {
            $table->id();
            $table->json('option_label');          // {"en": "Fixed", "es": "Fijo"}
            $table->string('option_key', 50)->unique();  // e.g. "FIXED"
            $table->json('help_text')->nullable();  // {"en": "...", "es": "..."}
            $table->timestamps();
        });

        // ── 2. Create benefit_form_options ───────────────────────────────────
        Schema::create('benefit_form_options', function (Blueprint $table) {
            $table->id();
            $table->json('option_label');
            $table->string('option_key', 50)->unique();
            $table->json('help_text')->nullable();
            $table->timestamps();
        });

        // ── 3. Seed shared options (id=1 FIXED, id=2 PERCENTAGE) ─────────────
        $now = now();

        DB::table('rule_form_options')->insert([
            [
                'id'           => 1,
                'option_label' => json_encode(['en' => 'Fixed',      'es' => 'Fijo']),
                'option_key'   => 'FIXED',
                'help_text'    => json_encode(['en' => 'A fixed amount regardless of the product or base value.', 'es' => 'Un monto fijo independiente del valor del producto o base.']),
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => 2,
                'option_label' => json_encode(['en' => 'Percentage', 'es' => 'Porcentaje']),
                'option_key'   => 'PERCENTAGE',
                'help_text'    => json_encode(['en' => 'A percentage applied over the product or base value.', 'es' => 'Un porcentaje aplicado sobre el valor del producto o base.']),
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        DB::table('benefit_form_options')->insert([
            [
                'id'           => 1,
                'option_label' => json_encode(['en' => 'Fixed',      'es' => 'Fijo']),
                'option_key'   => 'FIXED',
                'help_text'    => json_encode(['en' => 'A fixed amount regardless of the product or base value.', 'es' => 'Un monto fijo independiente del valor del producto o base.']),
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => 2,
                'option_label' => json_encode(['en' => 'Percentage', 'es' => 'Porcentaje']),
                'option_key'   => 'PERCENTAGE',
                'help_text'    => json_encode(['en' => 'A percentage applied over the product or base value.', 'es' => 'Un porcentaje aplicado sobre el valor del producto o base.']),
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        // ── 4. Update benefit_form_fields: replace inline option arrays with IDs ──
        // Fields 2 (DISCOUNT_TYPE) and 4 (BENEFIT_TYPE) had:
        //   {"required":true,"options":[{"en":"Fixed","es":"Fijo"},{"en":"Percentage","es":"Porcentaje"}]}
        // Now they reference option IDs from benefit_form_options.
        DB::table('benefit_form_fields')
            ->whereIn('id', [2, 4])
            ->update(['validation_rules' => json_encode(['required' => true, 'options' => [1, 2]])]);

        // ── 5. Truncate answers tables ───────────────────────────────────────
        // Old answers stored text values ("Fixed", "Percentage"); they must be
        // rebuilt using the new option IDs (1, 2) to avoid conflicts.
        DB::table('rule_form_answers')->truncate();
        DB::table('benefit_form_answers')->truncate();
    }

    public function down(): void
    {
        // Restore benefit_form_fields inline options (best-effort rollback)
        $inlineOptions = json_encode([
            'required' => true,
            'options'  => [
                ['en' => 'Fixed',      'es' => 'Fijo'],
                ['en' => 'Percentage', 'es' => 'Porcentaje'],
            ],
        ]);
        DB::table('benefit_form_fields')
            ->whereIn('id', [2, 4])
            ->update(['validation_rules' => $inlineOptions]);

        Schema::dropIfExists('benefit_form_options');
        Schema::dropIfExists('rule_form_options');
    }
};
