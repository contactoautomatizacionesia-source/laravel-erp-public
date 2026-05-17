<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $sourceCategoryId = DB::table('rule_category')->where('key', 'POINTS_SOURCE')->value('id');
            $thresholdCategoryId = DB::table('rule_category')->where('key', 'POINTS_THRESHOLD')->value('id');
            $rangeCategoryId = DB::table('rule_category')->where('key', 'POINTS_RANGE')->value('id');

            if ($thresholdCategoryId) {
                $this->ensurePointsSourceSection(
                    'POINTS_THRESHOLD',
                    'Fuente de puntos',
                    'Points source',
                    'POINTS_SOURCE_CONFIG',
                    2,
                );
            }

            if ($rangeCategoryId) {
                $this->ensurePointsSourceSection(
                    'POINTS_RANGE',
                    'Fuente de puntos',
                    'Points source',
                    'POINTS_SOURCE_CONFIG',
                    2,
                );
            }

            $this->backfillPointsSourceAnswers('POINTS_THRESHOLD');
            $this->backfillPointsSourceAnswers('POINTS_RANGE');

            if (!$sourceCategoryId) {
                return;
            }

            $sourceRuleIds = DB::table('rule')
                ->where('rule_category_id', $sourceCategoryId)
                ->pluck('id');

            if ($sourceRuleIds->isNotEmpty()) {
                DB::table('plan_rules')->whereIn('rule_id', $sourceRuleIds)->delete();
                DB::table('rule_dependencies')->whereIn('parent_rule_id', $sourceRuleIds)->delete();
                DB::table('rule_dependencies')->whereIn('child_rule_id', $sourceRuleIds)->delete();
                DB::table('form_answers')
                    ->where('formable_type', 'rule')
                    ->whereIn('formable_id', $sourceRuleIds)
                    ->delete();
                DB::table('rule')->whereIn('id', $sourceRuleIds)->delete();
            }

            $sourceSectionIds = DB::table('form_sections')
                ->where('owner_key', 'POINTS_SOURCE')
                ->pluck('id');

            if ($sourceSectionIds->isNotEmpty()) {
                $sourceFieldIds = DB::table('form_fields')
                    ->whereIn('form_section_id', $sourceSectionIds)
                    ->pluck('id');

                if ($sourceFieldIds->isNotEmpty()) {
                    DB::table('form_answers')->whereIn('form_field_id', $sourceFieldIds)->delete();
                    DB::table('form_fields')->whereIn('id', $sourceFieldIds)->delete();
                }

                DB::table('form_sections')->whereIn('id', $sourceSectionIds)->delete();
            }

            DB::table('rule_category')->where('id', $sourceCategoryId)->delete();
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Migration 2026_03_31_000023_remove_points_source_rule_category is irreversible.');
    }

    private function ensurePointsSourceSection(
        string $ownerKey,
        string $labelEs,
        string $labelEn,
        string $sectionKey,
        int $sectionOrder,
    ): void {
        $sectionId = DB::table('form_sections')
            ->where('owner_key', $ownerKey)
            ->where('section_key', $sectionKey)
            ->value('id');

        if (!$sectionId) {
            $sectionId = DB::table('form_sections')->insertGetId([
                'owner_key' => $ownerKey,
                'section_label' => json_encode(['es' => $labelEs, 'en' => $labelEn]),
                'section_key' => $sectionKey,
                'section_order' => $sectionOrder,
                'is_repeatable' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->ensureBooleanField(
            (int) $sectionId,
            'INCLUDE_PERSONAL',
            'Incluir compras personales',
            'Include personal purchases',
            'Indica si las compras propias del empresario cuentan en el cálculo.',
            "Indicates whether the entrepreneur's own purchases count in the calculation.",
        );

        $this->ensureBooleanField(
            (int) $sectionId,
            'INCLUDE_CHILDREN',
            'Incluir compras de hijos',
            "Include children's purchases",
            'Indica si las compras de los referidos directos e indirectos cuentan.',
            'Indicates whether purchases from direct and indirect referrals count.',
        );
    }

    private function ensureBooleanField(
        int $sectionId,
        string $fieldKey,
        string $labelEs,
        string $labelEn,
        string $helpEs,
        string $helpEn,
    ): void {
        $exists = DB::table('form_fields')
            ->where('form_section_id', $sectionId)
            ->where('field_key', $fieldKey)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('form_fields')->insert([
            'form_section_id' => $sectionId,
            'field_label' => json_encode(['es' => $labelEs, 'en' => $labelEn]),
            'field_key' => $fieldKey,
            'field_type' => 'boolean',
            'is_required' => false,
            'help_text' => json_encode(['es' => $helpEs, 'en' => $helpEn]),
            'validation_rules' => json_encode(['required' => false]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function backfillPointsSourceAnswers(string $categoryKey): void
    {
        $fieldIds = DB::table('form_fields')
            ->join('form_sections', 'form_sections.id', '=', 'form_fields.form_section_id')
            ->where('form_sections.owner_key', $categoryKey)
            ->whereIn('form_fields.field_key', ['INCLUDE_PERSONAL', 'INCLUDE_CHILDREN'])
            ->pluck('form_fields.id', 'form_fields.field_key');

        $personalFieldId = $fieldIds['INCLUDE_PERSONAL'] ?? null;

        if (!$personalFieldId) {
            return;
        }

        $ruleIds = DB::table('rule')
            ->join('rule_category', 'rule_category.id', '=', 'rule.rule_category_id')
            ->where('rule_category.key', $categoryKey)
            ->pluck('rule.id');

        foreach ($ruleIds as $ruleId) {
            $hasSourceAnswer = DB::table('form_answers')
                ->where('formable_type', 'rule')
                ->where('formable_id', $ruleId)
                ->whereIn('form_field_id', $fieldIds->values())
                ->exists();

            if ($hasSourceAnswer) {
                continue;
            }

            DB::table('form_answers')->insert([
                'formable_type' => 'rule',
                'formable_id' => $ruleId,
                'form_field_id' => $personalFieldId,
                'answer' => '1',
                'repeat_index' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
