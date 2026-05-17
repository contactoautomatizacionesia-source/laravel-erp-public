<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleResult;

class LifeTitleCountChecker extends AbstractRuleChecker
{
    public function categoryKey(): string
    {
        return 'LIFE_TITLE_COUNT';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            $answers            = $this->answers($rule);
            $beneathPlan        = $this->intAnswer($answers, 'BENEATH_PLAN');
            $minCount           = (int) $this->floatAnswer($answers, 'MIN_COUNT', 1);
            $minPointsPerMember = $this->floatAnswer($answers, 'MIN_POINTS_PER_MEMBER');

            $qualified = collect($context['downline'] ?? [])
                ->filter(
                    fn($m) => ($m['is_life_title'] ?? false)
                        && in_array($beneathPlan, $m['ancestor_plan_child_ids'] ?? [], true)
                        && (float) ($m['personal_points'] ?? 0) >= $minPointsPerMember
                )
                ->count();

            $passed = $qualified >= $minCount;

            $detail = $passed
                ? "Empresarios Life bajo plan #{$beneathPlan}: {$qualified} >= {$minCount} con >= {$minPointsPerMember} pts"
                : "Empresarios Life insuficientes: {$qualified} < {$minCount} bajo plan #{$beneathPlan}";

            return $passed
                ? RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, $detail, ['beneath_plan' => $beneathPlan, 'qualified' => $qualified, 'min_count' => $minCount, 'min_points' => $minPointsPerMember], $isRequired)
                : RuleResult::fail($rule->id, $this->categoryKey(), $rule->title, $detail, ['beneath_plan' => $beneathPlan, 'qualified' => $qualified, 'min_count' => $minCount, 'min_points' => $minPointsPerMember], $isRequired);
        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error evaluando regla: ' . $e->getMessage(), [], $isRequired);
        }
    }

    public function render(array $data, bool $passed): array
    {
        $qualified  = (int)   ($data['qualified']  ?? 0);
        $min        = (int)   ($data['min_count']  ?? 1);
        $beneath    = $data['beneath_plan']         ?? '—';
        $minPts     = (float) ($data['min_points'] ?? 0);
        $pct        = $min > 0 ? min(100.0, round($qualified / $min * 100, 1)) : 0.0;

        return [
            'icon'     => 'ti-star',
            'title'    => __('common.rule_card_life_title', ['min' => $min, 'beneath' => $beneath]),
            'summary'  => $passed
                ? __('common.rule_card_life_passed', ['qualified' => $qualified])
                : __('common.rule_card_life_failed', ['qualified' => $qualified, 'min' => $min]),
            'details'  => [
                ['label' => __('common.rule_card_label_qualified'),       'value' => (string) $qualified],
                ['label' => __('common.rule_card_label_required'),        'value' => (string) $min],
                ['label' => __('common.rule_card_label_base_plan'),       'value' => "#{$beneath}"],
                ['label' => __('common.rule_card_label_min_pts_member'),  'value' => number_format($minPts)],
            ],
            'progress' => ['current' => (float) $qualified, 'target' => (float) $min, 'percent' => $pct],
        ];
    }
}
