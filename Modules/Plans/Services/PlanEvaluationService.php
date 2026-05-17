<?php

namespace Modules\Plans\Services;

use Modules\Plans\Entities\PlanChild;
use Modules\Plans\Pipeline\Rules\RuleCheckerRegistry;

class PlanEvaluationService
{
    public function __construct(
        private readonly RuleCheckerRegistry $registry,
        private readonly UserSnapshotBuilder $snapshotBuilder,
    ) {}

    /**
     * Evaluate all active plan children (ordered highest to lowest) for the given user.
     *
     * Iterates from the highest to the lowest plan and returns the first PlanChild
     * where ALL required rules pass.
     *
     * @param int $userId
     * @return PlanEvaluationResult
     */
    public function evaluate(int $userId): PlanEvaluationResult
    {
        \Log::info("[PlanEvaluationService] evaluate() START userId={$userId}");

        $context      = $this->snapshotBuilder->build($userId);

        \Log::info("[PlanEvaluationService] snapshot built", [
            'personal_points' => $context['personal_points'],
            'children_points' => $context['children_points'],
            'total_points'    => $context['total_points'],
            'current_plan_child_id' => $context['current_plan_child_id'],
        ]);

        $allResults   = [];

        $planChildren = $this->loadOrderedActivePlanChildren();

        \Log::info("[PlanEvaluationService] active plan children to evaluate: " . $planChildren->count());

        foreach ($planChildren as $planChild) {
            $rules        = $planChild->rules()
                ->where('rule.is_active', true)
                ->with(['category', 'formAnswers.field', 'dependencies.childRule.category', 'dependencies.childRule.formAnswers.field'])
                ->get();

            foreach ($rules as $rule) {
                $checker    = $this->registry->for($rule->category->key);
                $isRequired = (bool) ($rule->pivot->is_required ?? true);
                $result     = $checker->check($rule, $userId, $context, $isRequired);

                $allResults[$planChild->id][] = $result;
            }

            // Qualification: ALL required rules must pass
            $planQualifies = collect($allResults[$planChild->id] ?? [])
                ->filter(fn($r) => $r->isRequired)
                ->every(fn($r) => $r->passed);

            \Log::info("[PlanEvaluationService] planChild={$planChild->id} qualifies=" . ($planQualifies ? 'YES' : 'NO'), [
                'rules' => collect($allResults[$planChild->id] ?? [])->map(fn($r) => [
                    'rule'       => $r->ruleId ?? '?',
                    'passed'     => $r->passed,
                    'isRequired' => $r->isRequired,
                ])->toArray(),
            ]);

            if ($planQualifies) {
                \Log::info("[PlanEvaluationService] qualified planChild={$planChild->id} — stopping evaluation");
                return new PlanEvaluationResult($planChild->id, $allResults);
            }
        }

        return new PlanEvaluationResult(null, $allResults);
    }

    /**
     * Load all active PlanChild records ordered by parent plan.order DESC,
     * then plan_child.level_order DESC (highest/hardest plan first).
     *
     * By doing this, evaluate() checks the hardest plans first and stops
     * immediately upon finding the highest qualifications they meet.
     */
    private function loadOrderedActivePlanChildren(): \Illuminate\Support\Collection
    {
        return PlanChild::query()
            ->where('is_active', true)
            ->whereHas('plan', fn($q) => $q->where('is_active', true))
            ->with('plan')
            ->get()
            ->sortBy([
                fn($a, $b) => ($b->plan->order ?? 0) <=> ($a->plan->order ?? 0),
                fn($a, $b) => ($b->level_order ?? 0) <=> ($a->level_order ?? 0),
            ])
            ->values();
    }
}
