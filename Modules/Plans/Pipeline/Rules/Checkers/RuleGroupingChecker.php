<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Illuminate\Contracts\Container\Container;
use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleCheckerRegistry;
use Modules\Plans\Pipeline\Rules\RuleResult;

class RuleGroupingChecker extends AbstractRuleChecker
{
    public function __construct(private readonly Container $container) {}

    public function categoryKey(): string
    {
        return 'RULE_GROUPING';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            $registry     = $this->container->make(RuleCheckerRegistry::class);
            $dependencies = $rule->dependencies()
                ->with(['childRule.category', 'childRule.formAnswers.field'])
                ->get();

            if ($dependencies->isEmpty()) {
                return RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, 'Sin dependencias configuradas.', [], $isRequired);
            }

            $accumulated  = null;
            $childResults = [];

            foreach ($dependencies as $dep) {
                $childRule    = $dep->childRule;
                $childChecker = $registry->for($childRule->category->key);
                $childResult  = $childChecker->check($childRule, $userId, $context, true);
                $childResults[] = $childResult;

                if ($accumulated === null) {
                    $accumulated = $childResult->passed;
                } elseif ($dep->operator === 'AND') {
                    $accumulated = $accumulated && $childResult->passed;
                } else {
                    $accumulated = $accumulated || $childResult->passed;
                }
            }

            $passed = (bool) $accumulated;

            return $passed
                ? RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, 'Condición de mantenimiento cumplida.', ['child_results' => $childResults], $isRequired)
                : RuleResult::fail($rule->id, $this->categoryKey(), $rule->title, 'Condición de mantenimiento no cumplida.', ['child_results' => $childResults], $isRequired);
        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error evaluando mantenimiento: ' . $e->getMessage(), [], $isRequired);
        }
    }

    public function render(array $data, bool $passed): array
    {
        /** @var RuleResult[] $childResults */
        $childResults = $data['child_results'] ?? [];
        $details      = [];

        foreach ($childResults as $child) {
            $childPassed = is_object($child) ? $child->passed : ($child['passed'] ?? false);
            $details[] = [
                'label' => is_object($child) ? ($child->label ?? '—') : ($child['label'] ?? '—'),
                'value' => $childPassed ? __('common.rule_card_status_met') : __('common.rule_card_status_pending'),
            ];
        }

        $fallbackDetails = [['label' => __('common.rule_card_label_status'), 'value' => $passed ? __('common.rule_card_status_met') : __('common.rule_card_status_pending')]];

        return [
            'icon'     => 'ti-layers',
            'title'    => __('common.rule_card_grouping_title'),
            'summary'  => $passed
                ? __('common.rule_card_grouping_passed')
                : __('common.rule_card_grouping_failed'),
            'details'  => $details ?: $fallbackDetails,
            'progress' => null,
        ];
    }
}
