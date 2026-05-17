<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * DISCOUNT_ON_NEXT_PURCHASE
 *
 * Resolves the explicit discount the user currently has on their subsequent purchases.
 * Reads form answers: DISCOUNT_QUANTITY (float), DISCOUNT_TYPE (int: 1=fixed, 2=percent).
 *
 * Data shape:
 * [
 *   'discount_quantity'   => float,   // e.g. 20 or 20.5
 *   'discount_type'       => int,     // 1 = fixed amount, 2 = percentage
 *   'discount_type_label' => string   // 'Fijo' | 'Porcentaje'
 * ]
 */
class DiscountOnNextPurchaseChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'DISCOUNT_ON_NEXT_PURCHASE';
    }

    public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult
    {
        try {
            $answers          = $this->answers($benefit);
            $discountQuantity = $this->floatAnswer($answers, 'DISCOUNT_QUANTITY');
            $discountType     = $this->intAnswer($answers, 'DISCOUNT_TYPE', 1);

            return BenefitResult::make(
                $benefit->id,
                $this->categoryKey(),
                $benefit->title,
                [
                    'discount_quantity' => $discountQuantity,
                    'discount_type'     => $discountType,
                ],
            );
        } catch (\Throwable $e) {
            return BenefitResult::error($benefit->id, $this->categoryKey(), $benefit->title ?? '', $e->getMessage());
        }
    }

    public function render(array $data): array
    {
        $qty      = $data['discount_quantity'] ?? 0;
        $isPercent = ($data['discount_type'] ?? 1) === 2;
        $type     = $isPercent ? __('common.benefit_card_discount_type_percent') : __('common.benefit_card_discount_type_fixed');
        $fmt      = $isPercent ? "{$qty}%" : "$ {$qty}";

        return [
            'icon'     => 'ti-tag',
            'title'    => __('common.benefit_card_discount_title', ['fmt' => $fmt]),
            'summary'  => __('common.benefit_card_discount_summary', ['fmt' => $fmt]),
            'details'  => [
                ['label' => __('common.benefit_card_label_discount_type'), 'value' => $type],
                ['label' => __('common.benefit_card_label_discount'),      'value' => $fmt],
            ],
            'progress' => null,
        ];
    }
}
