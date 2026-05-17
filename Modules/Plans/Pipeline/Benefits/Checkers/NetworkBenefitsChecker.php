<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * NETWORK_BENEFITS
 *
 * Earns a percentage of earnings based on points accumulated from referral activity
 * across defined networks and generations.
 *
 * Reads non-repeatable answers:
 *   NETWORK_SELECTED              (multiselect — JSON array, network option IDs)
 *   NETWORK_GENERATION_SELECTED   (multiselect — JSON array, network option IDs for payout)
 *   GENERATION_SELECTED           (multiselect — JSON array, generation depth option IDs)
 *
 * Reads repeatable answers (section PROFITABILITY_DATA):
 *   Each row: BENEFIT_POINTS (float), RENTABILITY_PERCENT (float)
 *
 * Data shape:
 * [
 *   'network_ids'         => int[],
 *   'payout_network_ids'  => int[],
 *   'generation_ids'      => int[],
 *   'profitability_tiers' => [
 *       ['points' => float, 'percent' => float],
 *       ...
 *   ],
 * ]
 */
class NetworkBenefitsChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'NETWORK_BENEFITS';
    }

    public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult
    {
        try {
            $answers = $this->answers($benefit);

            $networkIds       = $this->decodeMultiselect($answers['NETWORK_SELECTED'] ?? null);
            $payoutNetworkIds = $this->decodeMultiselect($answers['NETWORK_GENERATION_SELECTED'] ?? null);
            $generationIds    = $this->decodeMultiselect($answers['GENERATION_SELECTED'] ?? null);

            $tiers = [];
            foreach ($this->repeatedAnswers($benefit) as $row) {
                $tiers[] = [
                    'points'  => isset($row['BENEFIT_POINTS'])     ? (float) $row['BENEFIT_POINTS']     : null,
                    'percent' => isset($row['RENTABILITY_PERCENT']) ? (float) $row['RENTABILITY_PERCENT'] : null,
                ];
            }

            return BenefitResult::make(
                $benefit->id,
                $this->categoryKey(),
                $benefit->title,
                [
                    'network_ids'         => $networkIds,
                    'payout_network_ids'  => $payoutNetworkIds,
                    'generation_ids'      => $generationIds,
                    'profitability_tiers' => $tiers,
                ],
            );
        } catch (\Throwable $e) {
            return BenefitResult::error($benefit->id, $this->categoryKey(), $benefit->title ?? '', $e->getMessage());
        }
    }

    public function render(array $data): array
    {
        $tiers   = $data['profitability_tiers'] ?? [];
        $details = [];

        foreach ($tiers as $i => $tier) {
            $pts = number_format($tier['points'] ?? 0);
            $pct = $tier['percent'] ?? 0;
            $details[] = ['label' => __('common.benefit_card_network_tier_label', ['n' => $i + 1]), 'value' => "{$pts} pts → {$pct}%"];
        }

        if (empty($details)) {
            $details = [['label' => __('common.rule_card_label_status'), 'value' => __('common.benefit_card_label_active')]];
        }

        $tierCount = count($tiers);
        $summary   = $tierCount > 0
            ? __('common.benefit_card_network_summary_tiers', ['count' => $tierCount])
            : __('common.benefit_card_network_summary_default');

        return [
            'icon'     => 'ti-agenda',
            'title'    => __('common.benefit_card_network_title'),
            'summary'  => $summary,
            'details'  => $details,
            'progress' => null,
        ];
    }

    /**
     * Decode a stored multiselect answer (JSON array or comma-separated) into int[].
     */
    private function decodeMultiselect(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_map('intval', $decoded);
        }

        // Fallback: comma-separated
        return array_map('intval', array_filter(explode(',', $value)));
    }
}
