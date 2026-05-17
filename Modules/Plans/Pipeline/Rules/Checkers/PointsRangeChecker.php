<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleResult;

class PointsRangeChecker extends AbstractRuleChecker
{
    public function categoryKey(): string
    {
        return 'POINTS_RANGE';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            $answers       = $this->answers($rule);
            $min           = $this->floatAnswer($answers, 'MIN_POINTS');
            $maxRaw        = $answers['MAX_POINTS'] ?? null;
            $max           = ($maxRaw !== null && $maxRaw !== '') ? (float) $maxRaw : null;
            $pointsContext = $this->resolvePointsSource($answers, $context);
            $points        = $pointsContext['effective_points'];

            $aboveMin = $points >= $min;
            $belowMax = ($max === null) || ($points <= $max);
            $passed   = $aboveMin && $belowMax;

            $rangeLabel = $max !== null ? "[{$min} - {$max}]" : "[{$min} - inf)";
            $detail     = $passed
                ? "Puntos {$points} dentro del rango {$rangeLabel}"
                : "Puntos {$points} fuera del rango {$rangeLabel}";

            $resultContext = [
                'user_points' => $points,
                'min' => $min,
                'max' => $max,
                'include_personal' => $pointsContext['include_personal'],
                'include_children' => $pointsContext['include_children'],
            ];

            return $passed
                ? RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, $detail, $resultContext, $isRequired)
                : RuleResult::fail($rule->id, $this->categoryKey(), $rule->title, $detail, $resultContext, $isRequired);
        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error evaluando regla: ' . $e->getMessage(), [], $isRequired);
        }
    }

    public function render(array $data, bool $passed): array
    {
        $current = (float) ($data['user_points'] ?? 0);
        $min     = (float) ($data['min'] ?? 0);
        $max     = isset($data['max']) && $data['max'] !== null ? (float) $data['max'] : null;

        $rangeLabel = $max !== null
            ? number_format($min) . ' - ' . number_format($max)
            : __('common.rule_card_range_from', ['min' => number_format($min)]);

        $pct = $min > 0 ? min(100.0, round($current / $min * 100, 1)) : 0.0;

        return [
            'icon'     => 'ti-bar-chart',
            'title'    => __('common.rule_card_range_title', ['range' => $rangeLabel]),
            'summary'  => $passed
                ? __('common.rule_card_range_in')
                : __('common.rule_card_range_out', ['current' => number_format($current), 'range' => $rangeLabel]),
            'details'  => array_values(array_filter([
                ['label' => __('common.rule_card_label_your_points'), 'value' => number_format($current)],
                ['label' => __('common.rule_card_label_minimum'),     'value' => number_format($min)],
                $max !== null ? ['label' => __('common.rule_card_label_maximum'), 'value' => number_format($max)] : null,
                ['label' => __('common.rule_card_label_source'),      'value' => $this->renderPointsSourceSummary($data)],
            ])),
            'progress' => ['current' => $current, 'target' => $min, 'percent' => $pct],
        ];
    }

    private function renderPointsSourceSummary(array $data): string
    {
        $sources = [];

        if (!empty($data['include_personal'])) {
            $sources[] = __('common.rule_card_source_personal');
        }

        if (!empty($data['include_children'])) {
            $sources[] = __('common.rule_card_source_children');
        }

        return $sources ? implode(', ', $sources) : __('common.rule_card_source_none');
    }
}
