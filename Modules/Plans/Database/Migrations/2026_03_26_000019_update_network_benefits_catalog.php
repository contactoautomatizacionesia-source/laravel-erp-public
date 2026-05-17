<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── PASO 1: Update benefit_category (id=8) ────────────────────────────
        DB::table('benefit_category')
            ->where('id', 8)
            ->update([
                'key'         => 'NETWORK_BENEFITS',
                'name'        => json_encode(['en' => 'Network Benefits', 'es' => 'Beneficios de Red']),
                'description' => json_encode(['en' => 'Earns a percentage of network earnings based on points accumulated from referral activity across defined networks and generations', 'es' => 'Obtiene un porcentaje de las ganancias de la red según los puntos acumulados por actividad de referidos en las redes y generaciones definidas']),
                'updated_at'  => now(),
            ]);

        // ── PASO 2.1: Update existing form_section (id=14) ───────────────────
        DB::table('form_sections')
            ->where('id', 14)
            ->update([
                'owner_key'     => 'NETWORK_BENEFITS',
                'section_label' => json_encode(['en' => 'Definition of profitability', 'es' => 'Definición de rentabilidad']),
                'section_key'   => 'PROFITABILITY_DATA',
                'section_order' => 2,
                'is_repeatable' => 1,
                'updated_at'    => now(),
            ]);

        // ── PASO 2.2: Insert new form_sections ────────────────────────────────
        // Registro A → PARAMETRIZATION_DATA (section_order=1)
        DB::table('form_sections')->insert([
            'owner_key'     => 'NETWORK_BENEFITS',
            'section_label' => json_encode(['en' => 'Profitability parameterization', 'es' => 'Parametrización de rentabilidad']),
            'section_key'   => 'PARAMETRIZATION_DATA',
            'section_order' => 1,
            'is_repeatable' => 0,
            'is_active'     => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Registro B → GENERATION_PARAMETRIZATION (section_order=3)
        DB::table('form_sections')->insert([
            'owner_key'     => 'NETWORK_BENEFITS',
            'section_label' => json_encode(['en' => 'Generation parameterization', 'es' => 'Parametrización de generación']),
            'section_key'   => 'GENERATION_PARAMETRIZATION',
            'section_order' => 3,
            'is_repeatable' => 0,
            'is_active'     => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Resolve IDs of the just-inserted sections
        $parametrizationSectionId = DB::table('form_sections')
            ->where('owner_key', 'NETWORK_BENEFITS')
            ->where('section_key', 'PARAMETRIZATION_DATA')
            ->value('id');

        $generationSectionId = DB::table('form_sections')
            ->where('owner_key', 'NETWORK_BENEFITS')
            ->where('section_key', 'GENERATION_PARAMETRIZATION')
            ->value('id');

        // ── PASO 3.1: Update existing form_field (id=30) ─────────────────────
        DB::table('form_fields')
            ->where('id', 30)
            ->update([
                'field_label'      => json_encode(['en' => 'Points', 'es' => 'Puntos']),
                'field_key'        => 'BENEFIT_POINTS',
                'field_type'       => 'number',
                'help_text'        => json_encode(['en' => 'Points of the indicated network.', 'es' => 'Puntos de la red indicada.']),
                'validation_rules' => json_encode(['min' => 1, 'nullable' => true]),
                'form_section_id'  => 14,
                'updated_at'       => now(),
            ]);

        // ── PASO 3.2: RENTABILITY_PERCENT → form_section_id=14 ───────────────
        DB::table('form_fields')->insert([
            'form_section_id'  => 14,
            'field_label'      => json_encode(['en' => 'Rentability percent', 'es' => 'Porcentaje de rentabilidad']),
            'field_key'        => 'RENTABILITY_PERCENT',
            'field_type'       => 'number',
            'is_required'      => 1,
            'help_text'        => json_encode(['en' => 'Enter the percentage of return.', 'es' => 'Ingrese el porcentaje de rentabilidad.']),
            'validation_rules' => json_encode(['min' => 1, 'nullable' => true]),
            'is_active'        => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // ── PASO 3.3: NETWORK_SELECTED → form_section_id=PARAMETRIZATION_DATA ─
        DB::table('form_fields')->insert([
            'form_section_id'  => $parametrizationSectionId,
            'field_label'      => json_encode(['en' => 'Network', 'es' => 'Red']),
            'field_key'        => 'NETWORK_SELECTED',
            'field_type'       => 'multiselect',
            'is_required'      => 1,
            'help_text'        => json_encode(['en' => 'Networks whose referral activity generates the points used to reach the profitability thresholds.', 'es' => 'Redes cuya actividad de referidos genera los puntos para alcanzar los umbrales de rentabilidad.']),
            'validation_rules' => json_encode(['options' => [3, 4, 5], 'required' => true]),
            'is_active'        => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // ── PASO 3.4: NETWORK_GENERATION_SELECTED → form_section_id=GENERATION ─
        DB::table('form_fields')->insert([
            'form_section_id'  => $generationSectionId,
            'field_label'      => json_encode(['en' => 'Network', 'es' => 'Red']),
            'field_key'        => 'NETWORK_GENERATION_SELECTED',
            'field_type'       => 'multiselect',
            'is_required'      => 1,
            'help_text'        => json_encode(['en' => 'Networks from which the earned percentage will be paid, based on the selected generation depth.', 'es' => 'Redes sobre las que se pagará el porcentaje obtenido, según la profundidad de generación seleccionada.']),
            'validation_rules' => json_encode(['options' => [3, 4, 5], 'required' => true]),
            'is_active'        => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // ── PASO 3.5: GENERATION_SELECTED → form_section_id=GENERATION ────────
        DB::table('form_fields')->insert([
            'form_section_id'  => $generationSectionId,
            'field_label'      => json_encode(['en' => 'Generations', 'es' => 'Generaciones']),
            'field_key'        => 'GENERATION_SELECTED',
            'field_type'       => 'multiselect',
            'is_required'      => 1,
            'help_text'        => json_encode(['en' => 'Select the generations available in the parameterization.', 'es' => 'Seleccione las generaciones disponibles en la parametrización.']),
            'validation_rules' => json_encode(['options' => [6, 7, 8, 9, 10, 11], 'required' => true]),
            'is_active'        => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        // Remove inserted form_fields
        DB::table('form_fields')
            ->whereIn('field_key', ['RENTABILITY_PERCENT', 'NETWORK_SELECTED', 'NETWORK_GENERATION_SELECTED', 'GENERATION_SELECTED'])
            ->delete();

        // Remove inserted form_sections
        DB::table('form_sections')
            ->where('owner_key', 'NETWORK_BENEFITS')
            ->whereIn('section_key', ['PARAMETRIZATION_DATA', 'GENERATION_PARAMETRIZATION'])
            ->delete();

        // Restore form_field id=30 to original values
        DB::table('form_fields')
            ->where('id', 30)
            ->update([
                'field_label'      => json_encode(['en' => 'Bonus amount', 'es' => 'Cantidad del bono']),
                'field_key'        => 'MONETARY_BONUS_QUANTITY',
                'field_type'       => 'number',
                'help_text'        => json_encode(['en' => 'Enter the monetary bonus amount to be received as a reward for this benefit.', 'es' => 'Diligencie la cantidad del bono monetario que obtendrá como bonificación por este beneficio.']),
                'validation_rules' => json_encode(['enabled' => true, 'maxLength' => 100]),
                'updated_at'       => now(),
            ]);

        // Restore form_section id=14 to original values
        DB::table('form_sections')
            ->where('id', 14)
            ->update([
                'owner_key'     => 'RESIDUAL_INCOME_BONUS',
                'section_label' => json_encode(['en' => 'Information Record', 'es' => 'Registro de información']),
                'section_key'   => 'REGISTER_DATA',
                'section_order' => 1,
                'is_repeatable' => 0,
                'updated_at'    => now(),
            ]);

        // Restore benefit_category id=8 to original values
        DB::table('benefit_category')
            ->where('id', 8)
            ->update([
                'key'         => 'RESIDUAL_INCOME_BONUS',
                'name'        => json_encode(['en' => 'Residual income bonus', 'es' => 'Bono de ingresos residuales']),
                'description' => json_encode(['en' => 'Receives a bonus from residual income generated by referrals', 'es' => 'Adquiere una bonificación por ingresos residuales de sus referidos']),
                'updated_at'  => now(),
            ]);
    }
};
