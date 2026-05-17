<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleResult;

class PointsThresholdChecker extends AbstractRuleChecker
{
    public function categoryKey(): string
    {
        return 'POINTS_THRESHOLD';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            $answers       = $this->answers($rule);
            $minPoints     = $this->floatAnswer($answers, 'MIN_POINTS');
            $pointsContext = $this->resolvePointsSource($answers, $context);
            $userPoints    = $pointsContext['effective_points'];

            if ($userPoints >= $minPoints) {
                return RuleResult::pass(
                    $rule->id,
                    $this->categoryKey(),
                    $rule->title,
                    "Puntos suficientes: {$userPoints} >= {$minPoints}",
                    [
                        'user_points' => $userPoints,
                        'min_points' => $minPoints,
                        'include_personal' => $pointsContext['include_personal'],
                        'include_children' => $pointsContext['include_children'],
                    ],
                    $isRequired,
                );
            }

            return RuleResult::fail(
                $rule->id,
                $this->categoryKey(),
                $rule->title,
                "Puntos insuficientes: {$userPoints} < {$minPoints}",
                [
                    'user_points' => $userPoints,
                    'min_points' => $minPoints,
                    'include_personal' => $pointsContext['include_personal'],
                    'include_children' => $pointsContext['include_children'],
                ],
                $isRequired,
            );
        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error evaluando regla: ' . $e->getMessage(), [], $isRequired);
        }
    }

    public function render(array $data, bool $passed): array
    {
        $current = (float) ($data['user_points'] ?? 0);
        $target  = (float) ($data['min_points']  ?? 0);
        $pct     = $target > 0 ? min(100.0, round($current / $target * 100, 1)) : 0.0;
        $missing = max(0, $target - $current);

        return [
            'icon'     => 'ti-crown',
            'title'    => __('common.rule_card_points_required', ['target' => number_format($target)]),
            'summary'  => $passed
                ? __('common.rule_card_points_sufficient')
                : __('common.rule_card_points_missing', ['missing' => number_format($missing)]),
            'details'  => [
                ['label' => __('common.rule_card_label_your_points'), 'value' => number_format($current)],
                ['label' => __('common.rule_card_label_goal'),        'value' => number_format($target)],
                ['label' => __('common.rule_card_label_source'),      'value' => $this->renderPointsSourceSummary($data)],
            ],
            'progress' => ['current' => $current, 'target' => $target, 'percent' => $pct],
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
