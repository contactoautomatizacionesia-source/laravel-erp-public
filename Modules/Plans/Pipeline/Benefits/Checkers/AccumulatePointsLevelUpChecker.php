<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * ACCUMULATE_POINTS_LEVEL_UP
 *
 * The user accumulates points from their referrals' purchases to progress toward
 * the next plan level. This category has no form fields.
 *
 * Data shape:
 * [
 *   'is_cumulative'  => bool,
 *   'current_points' => float,  // total_points from snapshot context
 * ]
 */
class AccumulatePointsLevelUpChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'ACCUMULATE_POINTS_LEVEL_UP';
    }

    public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult
    {
        try {
            $currentPoints = (float) ($context['total_points'] ?? 0);

            return BenefitResult::make(
                $benefit->id,
                $this->categoryKey(),
                $benefit->title,
                [
                    'is_cumulative'  => $benefit->is_cumulative,
                    'current_points' => $currentPoints,
                ],
            );
        } catch (\Throwable $e) {
            return BenefitResult::error($benefit->id, $this->categoryKey(), $benefit->title ?? '', $e->getMessage());
        }
    }

    public function render(array $data): array
    {
        $pts = number_format($data['current_points'] ?? 0);

        return [
            'icon'     => 'ti-stats-up',
            'title'    => __('common.benefit_card_accumulate_title'),
            'summary'  => __('common.benefit_card_accumulate_summary'),
            'details'  => [
                ['label' => __('common.benefit_card_label_current_points'), 'value' => $pts],
            ],
            'progress' => null,
        ];
    }
}
