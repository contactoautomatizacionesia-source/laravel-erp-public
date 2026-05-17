<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Delete form answers for fields belonging to the NETWORK_SALES section (section id=7)
        DB::table('form_answers')
            ->whereIn('form_field_id', [13, 14, 15])
            ->delete();

        // Delete form fields for section 7 (NETWORK_CONFIG / NETWORK_SALES)
        DB::table('form_fields')
            ->whereIn('id', [13, 14, 15])
            ->delete();

        // Delete form section for NETWORK_SALES (id=7, owner_key='NETWORK_SALES')
        DB::table('form_sections')
            ->where('id', 7)
            ->delete();

        // Detach any rules that used category NETWORK_SALES from plan_rules,
        // then delete the rules themselves and their answers
        $networkCategoryId = DB::table('rule_category')
            ->where('key', 'NETWORK_SALES')
            ->value('id');

        if ($networkCategoryId) {
            $ruleIds = DB::table('rule')
                ->where('rule_category_id', $networkCategoryId)
                ->pluck('id');

            if ($ruleIds->isNotEmpty()) {
                DB::table('plan_rules')->whereIn('rule_id', $ruleIds)->delete();
                DB::table('rule_dependencies')->whereIn('parent_rule_id', $ruleIds)->delete();
                DB::table('rule_dependencies')->whereIn('child_rule_id', $ruleIds)->delete();
                DB::table('form_answers')
                    ->where('formable_type', 'rule')
                    ->whereIn('formable_id', $ruleIds)
                    ->delete();
                DB::table('rule')->whereIn('id', $ruleIds)->delete();
            }

            DB::table('rule_category')->where('id', $networkCategoryId)->delete();
        }
    }

    public function down(): void
    {
        // Re-insert rule_category
        DB::table('rule_category')->insert([
            'id'                    => 6,
            'name'                  => json_encode(['es' => 'Trabajo con Red', 'en' => 'Network Work']),
            'key'                   => 'NETWORK_SALES',
            'description'           => json_encode(['es' => 'Valida puntos mínimos entre compras personales y compras de la red No Life', 'en' => 'Validates minimum points between personal purchases and Non-Life network purchases']),
            'rule_category_type_id' => 2,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // Re-insert form_section
        DB::table('form_sections')->insert([
            'id'            => 7,
            'owner_key'     => 'NETWORK_SALES',
            'section_label' => json_encode(['es' => 'Configuración de red', 'en' => 'Network Configuration']),
            'section_key'   => 'NETWORK_CONFIG',
            'section_order' => 1,
            'is_repeatable' => false,
            'is_active'     => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Re-insert form_fields
        DB::table('form_fields')->insert([
            ['id' => 13, 'form_section_id' => 7, 'field_label' => json_encode(['es' => 'Total de puntos requeridos',      'en' => 'Required total points']),          'field_key' => 'TOTAL_POINTS',       'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Puntos totales que deben sumarse entre compras personales y red.',                   'en' => 'Total points that must be combined from personal purchases and network.']),    'validation_rules' => '{"min":0,"decimals":2}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'form_section_id' => 7, 'field_label' => json_encode(['es' => 'Mínimo de puntos personales',     'en' => 'Minimum personal points']),        'field_key' => 'MIN_PERSONAL_POINTS', 'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Puntos mínimos que deben provenir de compras propias del empresario.',            'en' => "Minimum points that must come from the entrepreneur's own purchases."]),       'validation_rules' => '{"min":0,"decimals":2}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'form_section_id' => 7, 'field_label' => json_encode(['es' => 'Mínimo de puntos de red No Life', 'en' => 'Minimum Non-Life network points']), 'field_key' => 'MIN_NETWORK_POINTS',  'field_type' => 'number', 'is_required' => true, 'help_text' => json_encode(['es' => 'Puntos mínimos que deben provenir de la red No Life.',                               'en' => 'Minimum points that must come from the Non-Life network.']),                   'validation_rules' => '{"min":0,"decimals":2}', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
