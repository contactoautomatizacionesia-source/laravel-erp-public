<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleResult;

class PointsPerCycleChecker extends AbstractRuleChecker
{
    // Maps POINTS_SOURCES option keys to their snapshot field per cycle entry.
    private const SOURCE_MAP = [
        'PERSONAL_POINTS' => 'personal_points',
        'LIFE_NETWORK'    => 'life_points',
        'NO_LIFE_NETWORK' => 'no_life_points',
    ];

    // Maps CYCLE_SELECTED option keys to their 0-based index in closed_cycles (most recent first).
    private const CYCLE_INDEX_MAP = [
        'CYCLE_1' => 0,
        'CYCLE_2' => 1,
    ];

    public function categoryKey(): string
    {
        return 'POINTS_PER_CYCLE';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            $combinations = $this->repeatedAnswers($rule);

            if (empty($combinations)) {
                return RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, 'Sin combinaciones configuradas.', [], $isRequired);
            }

            $closedCycles = collect($context['closed_cycles'] ?? [])->sortByDesc('closed_at')->values();

            $failedCombos = [];

            foreach ($combinations as $repeatIndex => $combo) {
                $cycleKey   = $combo['CYCLE_SELECTED'] ?? null;
                $required   = (float) ($combo['CYCLE_POINTS'] ?? 0);
                $sourcesRaw = $combo['POINTS_SOURCES'] ?? '[]';
                $sourceKeys = is_array($sourcesRaw) ? $sourcesRaw : (json_decode($sourcesRaw, true) ?? []);

                $cycleIndex = self::CYCLE_INDEX_MAP[$cycleKey] ?? null;
                $cycleData  = $cycleIndex !== null ? ($closedCycles->get($cycleIndex) ?? []) : [];

                $actual = $this->sumPoints($sourceKeys, $cycleData);

                if ($actual < $required) {
                    $failedCombos[] = [
                        'combo'    => $repeatIndex,
                        'cycle'    => $cycleKey,
                        'sources'  => $sourceKeys,
                        'required' => $required,
                        'actual'   => $actual,
                    ];
                }
            }

            $passed = empty($failedCombos);

            $detail = $passed
                ? 'Puntos por ciclo OK: todas las combinaciones cumplidas.'
                : 'Puntos por ciclo insuficientes: ' . count($failedCombos) . ' combinación(es) no cumplida(s).';

            return $passed
                ? RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, $detail, ['combinations_checked' => count($combinations)], $isRequired)
                : RuleResult::fail($rule->id, $this->categoryKey(), $rule->title, $detail, ['failed_combinations' => $failedCombos], $isRequired);
        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error evaluando regla: ' . $e->getMessage(), [], $isRequired);
        }
    }

    private function sumPoints(array $sourceKeys, array $cycleData): float
    {
        $actual = 0.0;
        foreach ($sourceKeys as $sourceKey) {
            $field   = self::SOURCE_MAP[$sourceKey] ?? null;
            $actual += $field ? (float) ($cycleData[$field] ?? 0) : 0.0;
        }
        return $actual;
    }

    public function render(array $data, bool $passed): array
    {
        $failed = $data['failed_combinations'] ?? [];
        $checked = $data['combinations_checked'] ?? 0;

        if ($passed) {
            $summary = __('common.rule_card_ppc_passed', ['checked' => $checked]);
            $details = [['label' => __('common.rule_card_label_combos_checked'), 'value' => (string) $checked]];
        } else {
            $summary = __('common.rule_card_ppc_failed', ['count' => count($failed)]);
            $details = [];
            foreach ($failed as $f) {
                $details[] = [
                    'label' => "Ciclo {$f['cycle']}",
                    'value' => number_format($f['actual']) . ' / ' . number_format($f['required']) . ' pts',
                ];
            }
        }

        return [
            'icon'     => 'ti-pulse',
            'title'    => __('common.rule_card_ppc_title'),
            'summary'  => $summary,
            'details'  => $details ?: [['label' => __('common.rule_card_label_status'), 'value' => __('common.rule_card_label_no_data')]],
            'progress' => null,
        ];
    }
}
