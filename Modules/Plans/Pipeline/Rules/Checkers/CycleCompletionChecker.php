<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleResult;

class CycleCompletionChecker extends AbstractRuleChecker
{
    public function categoryKey(): string
    {
        return 'CYCLE_COMPLETION';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            $answers      = $this->answers($rule);
            $requiredPlan = $this->intAnswer($answers, 'REQUIRED_PLAN');
            $minCycles    = (int) $this->floatAnswer($answers, 'MIN_CYCLES', 1);

            $matched = collect($context['closed_cycles'] ?? [])
                ->filter(fn($c) => (int) ($c['plan_child_id'] ?? 0) === $requiredPlan)
                ->count();

            $passed = $matched >= $minCycles;

            $detail = $passed
                ? "Ciclos cerrados en plan #{$requiredPlan}: {$matched} >= {$minCycles}"
                : "Ciclos insuficientes en plan #{$requiredPlan}: {$matched} < {$minCycles}";

            return $passed
                ? RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, $detail, ['matched_cycles' => $matched, 'required_plan' => $requiredPlan, 'min_cycles' => $minCycles], $isRequired)
                : RuleResult::fail($rule->id, $this->categoryKey(), $rule->title, $detail, ['matched_cycles' => $matched, 'required_plan' => $requiredPlan, 'min_cycles' => $minCycles], $isRequired);
        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error evaluando regla: ' . $e->getMessage(), [], $isRequired);
        }
    }

    public function render(array $data, bool $passed): array
    {
        $matched  = (int)   ($data['matched_cycles']  ?? 0);
        $required = (int)   ($data['required_plan']   ?? 0);
        $min      = (int)   ($data['min_cycles']      ?? 1);
        $pct      = $min > 0 ? min(100.0, round($matched / $min * 100, 1)) : 0.0;

        return [
            'icon'     => 'ti-loop',
            'title'    => __('common.rule_card_cycle_title', ['min' => $min, 'required' => $required]),
            'summary'  => $passed
                ? __('common.rule_card_cycle_passed', ['matched' => $matched])
                : __('common.rule_card_cycle_failed', ['matched' => $matched, 'min' => $min, 'required' => $required]),
            'details'  => [
                ['label' => __('common.rule_card_label_cycles_completed'), 'value' => (string) $matched],
                ['label' => __('common.rule_card_label_cycles_required'),  'value' => (string) $min],
                ['label' => __('common.rule_card_label_required_plan'),    'value' => "#{$required}"],
            ],
            'progress' => ['current' => (float) $matched, 'target' => (float) $min, 'percent' => $pct],
        ];
    }
}
