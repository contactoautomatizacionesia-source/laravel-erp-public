<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * FIRST_REFERRED_PURCHASE_BENEFIT
 *
 * Fixed benefit paid on the first purchase of each directly enrolled entrepreneur.
 * Reads form answers: BENEFIT_QUANTITY (float), BENEFIT_TYPE (int: 1=fixed, 2=percent).
 *
 * Data shape:
 * [
 *   'benefit_quantity'   => float,
 *   'benefit_type'       => int,     // 1 = fixed, 2 = percentage
 *   'benefit_type_label' => string,  // 'Fijo' | 'Porcentaje'
 *   'is_cumulative'      => bool,
 * ]
 */
class FirstReferredPurchaseBenefitChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'FIRST_REFERRED_PURCHASE_BENEFIT';
    }

    public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult
    {
        try {
            $answers         = $this->answers($benefit);
            $benefitQuantity = $this->floatAnswer($answers, 'BENEFIT_QUANTITY');
            $benefitType     = $this->intAnswer($answers, 'BENEFIT_TYPE', 1);

            return BenefitResult::make(
                $benefit->id,
                $this->categoryKey(),
                $benefit->title,
                [
                    'benefit_quantity' => $benefitQuantity,
                    'benefit_type'     => $benefitType,
                    'is_cumulative'    => $benefit->is_cumulative,
                ],
            );
        } catch (\Throwable $e) {
            return BenefitResult::error($benefit->id, $this->categoryKey(), $benefit->title ?? '', $e->getMessage());
        }
    }

    public function render(array $data): array
    {
        $qty       = $data['benefit_quantity'] ?? 0;
        $isPercent = ($data['benefit_type'] ?? 1) === 2;
        $type      = $isPercent ? __('common.benefit_card_discount_type_percent') : __('common.benefit_card_discount_type_fixed');
        $fmt       = $isPercent ? "{$qty}%" : "$ {$qty}";

        return [
            'icon'     => 'ti-user',
            'title'    => __('common.benefit_card_first_referred_title'),
            'summary'  => __('common.benefit_card_first_referred_summary', ['fmt' => $fmt]),
            'details'  => [
                ['label' => __('common.benefit_card_label_type'),  'value' => $type],
                ['label' => __('common.benefit_card_label_bonus'), 'value' => $fmt],
            ],
            'progress' => null,
        ];
    }
}
