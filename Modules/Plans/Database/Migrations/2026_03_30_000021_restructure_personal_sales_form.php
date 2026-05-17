<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // ──────────────────────────────────────────────────────────────────────────
    // PERSONAL_SALES restructure
    //
    // BEFORE:
    //   Section 5  (id=5)  PERSONAL_SALES_CONFIG  — non-repeatable
    //     Field 10  TOTAL_POINTS  number
    //   Section 6  (id=6)  CYCLE_COMBINATIONS     — repeatable
    //     Field 11  CYCLE_1_POINTS  number
    //     Field 12  CYCLE_2_POINTS  number
    //
    // AFTER:
    //   Section 5 deleted entirely (along with field 10 and its answers).
    //   Section 6 (CYCLE_COMBINATIONS, repeatable) keeps its id/key.
    //     Field 11 deleted (replaced).
    //     Field 12 deleted (replaced).
    //     New field A  CYCLE_SELECTED   select      options [12, 13]   (Ciclo 1 / Ciclo 2)
    //     New field B  CYCLE_POINTS     number
    //     New field C  POINTS_SOURCES   multiselect options [3, 4, 5]  (Personal / Life / No Life)
    //
    // New form_options:
    //   id=12  CYCLE_1  "Ciclo 1" / "Cycle 1"
    //   id=13  CYCLE_2  "Ciclo 2" / "Cycle 2"
    // ──────────────────────────────────────────────────────────────────────────

    public function up(): void
    {
        $now = now();

        // ── 1. Drop answers for fields 10, 11, 12 ────────────────────────────
        DB::table('form_answers')
            ->whereIn('form_field_id', [10, 11, 12])
            ->delete();

        // ── 2. Drop fields 10, 11, 12 ────────────────────────────────────────
        DB::table('form_fields')
            ->whereIn('id', [10, 11, 12])
            ->delete();

        // ── 3. Drop section 5 (PERSONAL_SALES_CONFIG, non-repeatable) ────────
        DB::table('form_sections')
            ->where('id', 5)
            ->delete();

        // ── 4. Insert form_options: Ciclo 1 (id=12) and Ciclo 2 (id=13) ──────
        DB::table('form_options')->insertOrIgnore([
            [
                'id'           => 12,
                'option_label' => json_encode(['en' => 'Cycle 1', 'es' => 'Ciclo 1']),
                'option_key'   => 'CYCLE_1',
                'help_text'    => json_encode(['en' => 'First cycle of the plan.', 'es' => 'Primer ciclo del plan.']),
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => 13,
                'option_label' => json_encode(['en' => 'Cycle 2', 'es' => 'Ciclo 2']),
                'option_key'   => 'CYCLE_2',
                'help_text'    => json_encode(['en' => 'Second cycle of the plan.', 'es' => 'Segundo ciclo del plan.']),
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        // ── 5. Insert three new fields for section 6 (CYCLE_COMBINATIONS) ────
        DB::table('form_fields')->insert([
            [
                'form_section_id'  => 6,
                'field_label'      => json_encode(['en' => 'Cycle to select', 'es' => 'Ciclo a seleccionar']),
                'field_key'        => 'CYCLE_SELECTED',
                'field_type'       => 'select',
                'is_required'      => true,
                'help_text'        => json_encode(['en' => 'Choose which cycle this combination applies to.', 'es' => 'Elige a qué ciclo aplica esta combinación.']),
                'validation_rules' => json_encode(['required' => true, 'options' => [12, 13]]),
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'form_section_id'  => 6,
                'field_label'      => json_encode(['en' => 'Points', 'es' => 'Puntos']),
                'field_key'        => 'CYCLE_POINTS',
                'field_type'       => 'number',
                'is_required'      => true,
                'help_text'        => json_encode(['en' => 'Number of points required in this cycle.', 'es' => 'Cantidad de puntos requeridos en este ciclo.']),
                'validation_rules' => json_encode(['min' => 0, 'decimals' => 2]),
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'form_section_id'  => 6,
                'field_label'      => json_encode(['en' => 'Points source', 'es' => 'Fuente de puntos']),
                'field_key'        => 'POINTS_SOURCES',
                'field_type'       => 'multiselect',
                'is_required'      => true,
                'help_text'        => json_encode(['en' => 'Select which networks contribute points for this cycle combination.', 'es' => 'Selecciona qué redes aportan puntos para esta combinación de ciclo.']),
                'validation_rules' => json_encode(['required' => true, 'options' => [3, 4, 5]]),
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);
    }

    public function down(): void
    {
        // ── 1. Remove the three new fields ───────────────────────────────────
        DB::table('form_answers')
            ->whereIn('form_field_id', function ($q) {
                $q->select('id')
                    ->from('form_fields')
                    ->where('form_section_id', 6)
                    ->whereIn('field_key', ['CYCLE_SELECTED', 'CYCLE_POINTS', 'POINTS_SOURCES']);
            })
            ->delete();

        DB::table('form_fields')
            ->where('form_section_id', 6)
            ->whereIn('field_key', ['CYCLE_SELECTED', 'CYCLE_POINTS', 'POINTS_SOURCES'])
            ->delete();

        // ── 2. Remove form_options 12 and 13 ─────────────────────────────────
        DB::table('form_options')->whereIn('id', [12, 13])->delete();

        // ── 3. Restore section 5 (PERSONAL_SALES_CONFIG) ─────────────────────
        DB::table('form_sections')->insert([
            'id'            => 5,
            'owner_key'     => 'PERSONAL_SALES',
            'section_label' => json_encode(['en' => 'Total Points Configuration', 'es' => 'Configuración de puntos totales']),
            'section_key'   => 'PERSONAL_SALES_CONFIG',
            'section_order' => 1,
            'is_repeatable' => false,
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // ── 4. Restore fields 10, 11, 12 ─────────────────────────────────────
        DB::table('form_fields')->insert([
            [
                'id'               => 10,
                'form_section_id'  => 5,
                'field_label'      => json_encode(['en' => 'Required total personal points', 'es' => 'Total de puntos personales requeridos']),
                'field_key'        => 'TOTAL_POINTS',
                'field_type'       => 'number',
                'is_required'      => true,
                'help_text'        => json_encode(['en' => 'Total points that must be accumulated across all cycles to meet the rule.', 'es' => 'Suma total de puntos que deben acumularse entre todos los ciclos para cumplir la regla.']),
                'validation_rules' => json_encode(['min' => 0, 'decimals' => 2]),
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => 11,
                'form_section_id'  => 6,
                'field_label'      => json_encode(['en' => 'Points in cycle 1', 'es' => 'Puntos en ciclo 1']),
                'field_key'        => 'CYCLE_1_POINTS',
                'field_type'       => 'number',
                'is_required'      => true,
                'help_text'        => json_encode(['en' => 'Points required in the first cycle for this combination.', 'es' => 'Puntos requeridos en el primer ciclo para esta combinación.']),
                'validation_rules' => json_encode(['min' => 0, 'decimals' => 2]),
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => 12,
                'form_section_id'  => 6,
                'field_label'      => json_encode(['en' => 'Points in cycle 2', 'es' => 'Puntos en ciclo 2']),
                'field_key'        => 'CYCLE_2_POINTS',
                'field_type'       => 'number',
                'is_required'      => true,
                'help_text'        => json_encode(['en' => 'Points required in the second cycle. Can be 0.', 'es' => 'Puntos requeridos en el segundo ciclo. Puede ser 0.']),
                'validation_rules' => json_encode(['min' => 0, 'decimals' => 2]),
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }
};
