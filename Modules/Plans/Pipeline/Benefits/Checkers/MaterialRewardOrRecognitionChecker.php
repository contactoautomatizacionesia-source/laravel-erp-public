<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * MATERIAL_REWARD_OR_RECOGNITION
 *
 * Receives a material reward, recognition or announcement granted by the organization.
 * Reads form answers: PRIZE_NAME (string), PRIZE_DESCRIPTION (string).
 *
 * Data shape:
 * [
 *   'prize_name'        => string,
 *   'prize_description' => string,
 * ]
 */
class MaterialRewardOrRecognitionChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'MATERIAL_REWARD_OR_RECOGNITION';
    }

    public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult
    {
        try {
            $answers          = $this->answers($benefit);
            $prizeName        = $this->stringAnswer($answers, 'PRIZE_NAME');
            $prizeDescription = $this->stringAnswer($answers, 'PRIZE_DESCRIPTION');

            return BenefitResult::make(
                $benefit->id,
                $this->categoryKey(),
                $benefit->title,
                [
                    'prize_name'        => $prizeName,
                    'prize_description' => $prizeDescription,
                ],
            );
        } catch (\Throwable $e) {
            return BenefitResult::error($benefit->id, $this->categoryKey(), $benefit->title ?? '', $e->getMessage());
        }
    }

    public function render(array $data): array
    {
        $name = $data['prize_name'] ?? '—';
        $desc = $data['prize_description'] ?? '';

        $details = [['label' => __('common.benefit_card_label_prize'), 'value' => $name]];
        if ($desc) {
            $details[] = ['label' => __('common.benefit_card_label_description'), 'value' => $desc];
        }

        return [
            'icon'     => 'ti-medall',
            'title'    => __('common.benefit_card_material_title', ['name' => $name]),
            'summary'  => $desc ?: __('common.benefit_card_material_summary_default'),
            'details'  => $details,
            'progress' => null,
        ];
    }
}
