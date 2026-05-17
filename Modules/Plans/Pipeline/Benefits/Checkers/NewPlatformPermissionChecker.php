<?php

namespace Modules\Plans\Pipeline\Benefits\Checkers;

use Modules\Plans\Entities\Benefit;
use Modules\Plans\Pipeline\Benefits\AbstractBenefitChecker;
use Modules\Plans\Pipeline\Benefits\BenefitResult;

/**
 * NEW_PLATFORM_PERMISSION
 *
 * Grants a new permission or access within the platform.
 * Reads form answers: SELECT_PERMISSION (int, permission ID).
 *
 * Data shape:
 * [
 *   'permission_id' => int|null,   // permission ID from the catalog
 * ]
 */
class NewPlatformPermissionChecker extends AbstractBenefitChecker
{
    public function categoryKey(): string
    {
        return 'NEW_PLATFORM_PERMISSION';
    }

    public function resolve(Benefit $benefit, int $userId, array $context): BenefitResult
    {
        try {
            $answers      = $this->answers($benefit);
            $permissionId = $this->intAnswer($answers, 'SELECT_PERMISSION');

            return BenefitResult::make(
                $benefit->id,
                $this->categoryKey(),
                $benefit->title,
                [
                    'permission_id' => $permissionId,
                ],
            );
        } catch (\Throwable $e) {
            return BenefitResult::error($benefit->id, $this->categoryKey(), $benefit->title ?? '', $e->getMessage());
        }
    }

    public function render(array $data): array
    {
        $permId = $data['permission_id'] ?? null;

        return [
            'icon'     => 'ti-unlock',
            'title'    => __('common.benefit_card_permission_title'),
            'summary'  => __('common.benefit_card_permission_summary'),
            'details'  => [
                ['label' => __('common.benefit_card_label_permission_id'), 'value' => $permId !== null ? (string) $permId : '—'],
            ],
            'progress' => null,
        ];
    }
}
