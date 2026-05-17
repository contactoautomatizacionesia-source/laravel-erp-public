<?php

namespace Modules\Plans\Services;

use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * Holds the resolved benefits for a given PlanChild.
 *
 * `benefitResults` is a flat array of BenefitResult objects, one per benefit
 * assigned to the evaluated PlanChild.
 *
 * Shape:
 * [
 *   'plan_child_id' => int,
 *   'benefits'      => BenefitResult[],   // indexed by benefit_id
 *   'current_plan_child_id' => int|null,  // explicit discount context
 *   'next_plan_child_id'    => int|null,  // next level discount context
 * ]
 */
class BenefitEvaluationResult
{
    /**
     * @param int             $planChildId      The PlanChild whose benefits were resolved.
     * @param BenefitResult[] $benefitResults   Keyed by benefit_id.
     * @param array[]         $cards            Rendered card per benefit_id (null on error).
     */
    public function __construct(
        public readonly int   $planChildId,
        public readonly array $benefitResults = [],
        public readonly array $cards          = [],
    ) {}

    /**
     * Returns the resolved BenefitResult for a given benefit_id, or null.
     */
    public function forBenefit(int $benefitId): ?BenefitResult
    {
        return $this->benefitResults[$benefitId] ?? null;
    }

    /**
     * Returns all resolved BenefitResult objects as a plain array (for serialization).
     *
     * @return array[]
     */
    public function toArray(): array
    {
        return array_values(array_map(fn(BenefitResult $r) => [
            'benefit_id'   => $r->benefitId,
            'category_key' => $r->categoryKey,
            'label'        => $r->label,
            'resolved'     => $r->resolved,
            'data'         => $r->data,
            'card'         => $this->cards[$r->benefitId] ?? null,
            'error'        => $r->error ?: null,
        ], $this->benefitResults));
    }
}
