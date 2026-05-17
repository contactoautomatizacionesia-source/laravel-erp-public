<?php

namespace Modules\Plans\Services;

use Modules\Plans\Entities\PlanChild;
use Modules\Plans\Pipeline\Benefits\BenefitCheckerRegistry;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

class BenefitEvaluationService
{
    public function __construct(
        private readonly BenefitCheckerRegistry $registry,
        private readonly UserSnapshotBuilder    $snapshotBuilder,
    ) {}

    /**
     * Resolve all benefits for a given PlanChild.
     *
     * Each benefit assigned to the PlanChild is passed to its corresponding
     * BenefitChecker (matched by benefit_category.key). The checker reads
     * the benefit's form answers and the user snapshot to produce a BenefitResult.
     *
     * @param int $planChildId  The PlanChild whose benefits should be resolved.
     * @param int $userId       The entrepreneur whose snapshot is used as context.
     */
    public function resolveForPlanChild(int $planChildId, int $userId): BenefitEvaluationResult
    {
        $context   = $this->snapshotBuilder->build($userId);
        $planChild = PlanChild::with([
            'benefits.category',
            'benefits.formAnswers.field',
        ])->find($planChildId);

        if (! $planChild) {
            return new BenefitEvaluationResult($planChildId);
        }

        $benefitResults = [];
        $cardsByBenefit = [];

        foreach ($planChild->benefits as $benefit) {
            if (! $benefit->is_active || ! $benefit->category) {
                continue;
            }

            try {
                $checker = $this->registry->for($benefit->category->key);
                $result  = $checker->resolve($benefit, $userId, $context);
                $cardsByBenefit[$benefit->id] = $checker->render($result->data);
            } catch (\Throwable $e) {
                $result = BenefitResult::error(
                    $benefit->id,
                    $benefit->category->key ?? 'UNKNOWN',
                    $benefit->title ?? '',
                    $e->getMessage(),
                );
                $cardsByBenefit[$benefit->id] = null;
            }

            $benefitResults[$benefit->id] = $result;
        }

        return new BenefitEvaluationResult($planChildId, $benefitResults, $cardsByBenefit);
    }
}
