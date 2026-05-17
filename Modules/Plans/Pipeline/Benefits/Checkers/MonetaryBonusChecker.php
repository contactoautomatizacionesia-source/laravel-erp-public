<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * MONETARY_BONUS
 *
 * The user receives a monetary bonus as a reward. No form fields.
 *
 * Data shape:
 * [
 *   'is_cumulative' => bool,
 * ]
 */
class MonetaryBonusChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'MONETARY_BONUS';
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
            'icon'     => 'ti-money',
            'title'    => __('common.benefit_card_monetary_title'),
            'summary'  => __('common.benefit_card_monetary_summary'),
            'details'  => [
                ['label' => __('common.rule_card_label_status'), 'value' => __('common.benefit_card_label_active')],
            ],
            'progress' => null,
        ];
    }
}
