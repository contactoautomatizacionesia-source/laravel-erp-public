<?php

namespace Modules\Plans\Services;

use Modules\Plans\Pipeline\Rules\RuleResult;

class PlanEvaluationResult
{
    /**
     * @param int|null       $qualifiedPlanChildId       Lowest PlanChild ID where all required rules pass. Null = none qualified.
     * @param RuleResult[][] $ruleResultsByPlanChildId   Keyed by plan_child_id; each value is array of RuleResult.
     */
    public function __construct(
        public readonly ?int  $qualifiedPlanChildId,
        public readonly array $ruleResultsByPlanChildId = [],
    ) {}

    public function qualified(): bool
    {
        return $this->qualifiedPlanChildId !== null;
    }

    /**
     * Returns rule results for a specific plan child.
     *
     * @return RuleResult[]
     */
    public function resultsForPlan(int $planChildId): array
    {
        return $this->ruleResultsByPlanChildId[$planChildId] ?? [];
    }
}
