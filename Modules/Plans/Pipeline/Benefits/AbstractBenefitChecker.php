<?php

namespace Modules\Plans\Pipeline\Benefits;

use Modules\Plans\Entities\Benefit;

abstract class AbstractBenefitChecker implements BenefitCheckerInterface
{
    abstract public function categoryKey(): string;

    abstract public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult;

    /**
     * Returns non-repeatable answers keyed by field_key.
     * e.g. ['DISCOUNT_QUANTITY' => '20', 'DISCOUNT_TYPE' => '2']
     */
    protected function answers(Benefit $benefit): array
    {
        return $benefit->formAnswers
            ->whereNull('repeat_index')
            ->keyBy(fn($a) => $a->field->field_key)
            ->map(fn($a) => $a->answer)
            ->all();
    }

    /**
     * Returns repeatable section answers grouped by repeat_index.
     * e.g. [0 => ['BENEFIT_POINTS' => '50', 'RENTABILITY_PERCENT' => '10'], 1 => [...]]
     */
    protected function repeatedAnswers(Benefit $benefit): array
    {
        $groups = [];
        foreach ($benefit->formAnswers->whereNotNull('repeat_index') as $ans) {
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

    protected function intAnswer(array $answers, string $key, ?int $default = null): ?int
    {
        return (isset($answers[$key]) && $answers[$key] !== null && $answers[$key] !== '')
            ? (int) $answers[$key]
            : $default;
    }

    protected function stringAnswer(array $answers, string $key, string $default = ''): string
    {
        return (isset($answers[$key]) && $answers[$key] !== null && $answers[$key] !== '')
            ? (string) $answers[$key]
            : $default;
    }

    protected function boolAnswer(array $answers, string $key, bool $default = false): bool
    {
        if (!isset($answers[$key]) || $answers[$key] === null) {
            return $default;
        }
        return filter_var($answers[$key], FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Default render — subclasses should override with category-specific presentation.
     */
    public function render(array $data): array
    {
        $details = array_map(
            fn($k, $v) => ['label' => $k, 'value' => is_array($v) ? json_encode($v) : (string) $v],
            array_keys($data),
            array_values($data),
        );

        return [
            'icon'     => 'ti-gift',
            'title'    => 'Beneficio',
            'summary'  => 'Beneficio activo en tu plan.',
            'details'  => $details ?: [['label' => 'Estado', 'value' => 'Activo']],
            'progress' => null,
        ];
    }
}
