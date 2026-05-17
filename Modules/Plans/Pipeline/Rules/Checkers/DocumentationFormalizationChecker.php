<?php

namespace Modules\Plans\Pipeline\Rules\Checkers;

use Modules\Plans\Entities\Rule;
use Modules\Plans\Pipeline\Rules\AbstractRuleChecker;
use Modules\Plans\Pipeline\Rules\RuleResult;

class DocumentationFormalizationChecker extends AbstractRuleChecker
{
    public function categoryKey(): string
    {
        return 'DOCUMENTATION_FORMALIZATION';
    }

    public function check(Rule $rule, int $userId, array $context, bool $isRequired = true): RuleResult
    {
        try {
            // Al no tener formulario, solo leemos la bandera desde el contexto
            // Esta bandera será alimentada por la futura API en el SnapshotBuilder
            $hasFormalized = $context['has_formalized_documentation'] ?? false;

            $detail = $hasFormalized
                ? 'El empresario ha formalizado su documentación/contrato.'
                : 'El empresario aún no ha firmado o entregado la documentación requerida.';

            return $hasFormalized
                ? RuleResult::pass($rule->id, $this->categoryKey(), $rule->title, $detail, ['has_formalized' => $hasFormalized], $isRequired)
                : RuleResult::fail($rule->id, $this->categoryKey(), $rule->title, $detail, ['has_formalized' => $hasFormalized], $isRequired);

        } catch (\Throwable $e) {
            return RuleResult::fail($rule->id, $this->categoryKey(), $rule->title ?? '', 'Error validando formalización: ' . $e->getMessage(), [], $isRequired);
        }
    }

    public function render(array $data, bool $passed): array
    {
        return [
            'icon'     => 'ti-files',
            'title'    => __('common.rule_card_docs_title'),
            'summary'  => $passed
                ? __('common.rule_card_docs_passed')
                : __('common.rule_card_docs_failed'),
            'details'  => [
                ['label' => __('common.rule_card_label_status'), 'value' => $passed ? __('common.rule_card_status_formalized') : __('common.rule_card_status_pending')],
            ],
            'progress' => null,
        ];
    }
}
