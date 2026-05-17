<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * REFERRED_PURCHASE_DIFFERENTIAL
 *
 * The differential percentage earned from all direct referrals' purchases.
 * This category has no form fields; the differential is implicit to the plan level.
 *
 * Data shape:
 * [
 *   'is_cumulative' => bool,  // from benefit.is_cumulative
 * ]
 */
class ReferredPurchaseDifferentialChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'REFERRED_PURCHASE_DIFFERENTIAL';
    }

    public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult
    {
        try {
            return BenefitResult::make(
                $benefit->id,
                $this->categoryKey(),
                $benefit->title,
                [
                    'is_cumulative' => $benefit->is_cumulative,
                ],
            );
        } catch (\Throwable $e) {
            return BenefitResult::error($benefit->id, $this->categoryKey(), $benefit->title ?? '', $e->getMessage());
        }
    }

    public function render(array $data): array
    {
        return [
            'icon'     => 'ti-share-alt',
            'title'    => __('common.benefit_card_differential_title'),
            'summary'  => __('common.benefit_card_differential_summary'),
            'details'  => [
                ['label' => __('common.rule_card_label_status'), 'value' => __('common.benefit_card_label_active')],
            ],
            'progress' => null,
        ];
    }
}
