<?php

namespace Modules\Plans\Pipeline\Rules;

use Modules\Plans\Entities\Rule;

abstract class AbstractRuleChecker implements RuleCheckerInterface
{
    abstract public function categoryKey(): string;

    abstract public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult;

    /**
     * Returns non-repeatable answers keyed by field_key.
     * e.g. ['MIN_POINTS' => '200.00', 'MAX_POINTS' => null]
     */
    protected function answers(Rule $rule): array
    {
        return $rule->formAnswers
            ->whereNull('repeat_index')
            ->keyBy(fn($a) => $a->field->field_key)
            ->map(fn($a) => $a->answer)
            ->all();
    }

    /**
     * Returns repeatable section answers grouped by repeat_index.
     * e.g. [0 => ['CYCLE_1_POINTS' => '50', 'CYCLE_2_POINTS' => '30'], 1 => [...]]
     */
    protected function repeatedAnswers(Rule $rule): array
    {
        $groups = [];
        foreach ($rule->formAnswers->whereNotNull('repeat_index') as $ans) {
            $groups[$ans->repeat_index][$ans->field->field_key] = $ans->answer;
        }
        ksort($groups);
        return $groups;
    }

    protected function floatAnswer(array $answers, string $key, float $default = 0.0): float
    {
        return (isset($answers[$key]) && $answers[$key] !== null && $answers[$key] !== '')
            ? (float) $answers[$key]
            : $default;
    }

    protected function boolAnswer(array $answers, string $key, bool $default = false): bool
    {
        if (!isset($answers[$key]) || $answers[$key] === null) {
            return $default;
        }
        return filter_var($answers[$key], FILTER_VALIDATE_BOOLEAN);
    }

    protected function intAnswer(array $answers, string $key, ?int $default = null): ?int
    {
        return (isset($answers[$key]) && $answers[$key] !== null && $answers[$key] !== '')
            ? (int) $answers[$key]
            : $default;
    }

    protected function resolvePointsSource(array $answers, array $context): array
    {
        $includePersonal = $this->boolAnswer($answers, 'INCLUDE_PERSONAL', false);
        $includeChildren = $this->boolAnswer($answers, 'INCLUDE_CHILDREN', false);

        $effectivePoints = 0.0;

        if ($includePersonal) {
            $effectivePoints += (float) ($context['personal_points'] ?? 0);
        }

        if ($includeChildren) {
            $effectivePoints += (float) ($context['children_points'] ?? 0);
        }

        return [
            'effective_points' => $effectivePoints,
            'include_personal' => $includePersonal,
            'include_children' => $includeChildren,
        ];
    }

    /**
     * Default render — subclasses should override with category-specific presentation.
     */
    public function render(array $data, bool $passed): array
    {
        $details = array_map(
            fn($k, $v) => ['label' => $k, 'value' => is_array($v) ? json_encode($v) : (string) $v],
            array_keys($data),
            array_values($data),
        );

        $fallbackDetails = [['label' => 'Estado', 'value' => $passed ? 'Cumplido' : 'Pendiente']];

        return [
            'icon'     => 'ti-check-box',
            'title'    => 'Requisito',
            'summary'  => $passed ? 'Requisito cumplido.' : 'Requisito pendiente.',
            'details'  => $details ?: $fallbackDetails,
            'progress' => null,
        ];
    }
}
