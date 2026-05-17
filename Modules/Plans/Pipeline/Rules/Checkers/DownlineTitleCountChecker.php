<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleResult;

class DownlineTitleCountChecker extends AbstractRuleChecker
{
    public function categoryKey(): string
    {
        return 'DOWNLINE_TITLE_COUNT';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            $answers        = $this->answers($rule);
            $requiredPlan   = $this->intAnswer($answers, 'REQUIRED_PLAN');
            $generation     = (int) $this->floatAnswer($answers, 'GENERATION', 1);
            $minCount       = (int) $this->floatAnswer($answers, 'MIN_COUNT', 1);
            $minMultiplier  = $this->floatAnswer($answers, 'MIN_BENEFIT_MULTIPLIER', 1.0);

            $qualified = collect($context['downline'] ?? [])
                ->filter(
                    fn($m) => (int) ($m['generation'] ?? 0) === $generation
                        && (int) ($m['plan_child_id'] ?? 0) === $requiredPlan
                        && (float) ($m['benefit_total'] ?? 0) >= $minMultiplier
                )
                ->count();

            $passed = $qualified >= $minCount;

            $detail = $passed
                ? "Generación {$generation}: {$qualified} empresarios con plan #{$requiredPlan} >= {$minCount} requeridos"
                : "Generación {$generation}: solo {$qualified} empresarios con plan #{$requiredPlan}, se requieren {$minCount}";

            return $passed
                ? RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, $detail, ['generation' => $generation, 'required_plan' => $requiredPlan, 'qualified' => $qualified, 'min_count' => $minCount], $isRequired)
                : RuleResult::fail($rule->id, $this->categoryKey(), $rule->title, $detail, ['generation' => $generation, 'required_plan' => $requiredPlan, 'qualified' => $qualified, 'min_count' => $minCount], $isRequired);
        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error evaluando regla: ' . $e->getMessage(), [], $isRequired);
        }
    }

    public function render(array $data, bool $passed): array
    {
        $gen       = $data['generation']     ?? 1;
        $plan      = $data['required_plan']  ?? '—';
        $qualified = (int) ($data['qualified'] ?? 0);
        $min       = (int) ($data['min_count'] ?? 1);
        $pct       = $min > 0 ? min(100.0, round($qualified / $min * 100, 1)) : 0.0;

        return [
            'icon'     => 'ti-user',
            'title'    => __('common.rule_card_downline_title', ['min' => $min, 'plan' => $plan, 'gen' => $gen]),
            'summary'  => $passed
                ? __('common.rule_card_downline_passed', ['qualified' => $qualified])
                : __('common.rule_card_downline_failed', ['qualified' => $qualified, 'min' => $min, 'gen' => $gen]),
            'details'  => [
                ['label' => __('common.rule_card_label_qualified'),     'value' => (string) $qualified],
                ['label' => __('common.rule_card_label_required'),      'value' => (string) $min],
                ['label' => __('common.rule_card_label_generation'),    'value' => (string) $gen],
                ['label' => __('common.rule_card_label_required_plan'), 'value' => "#{$plan}"],
            ],
            'progress' => ['current' => (float) $qualified, 'target' => (float) $min, 'percent' => $pct],
        ];
    }
}
