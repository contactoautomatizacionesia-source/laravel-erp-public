<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Modules\Customer\Entities\EntrepreneurPlanHistory;

class PlanTimeline extends Component
{
    public array $items = [];
    public string $emptyText;

    public function __construct($history = null, string $type = 'customer', ?string $emptyText = null)
    {
        $this->emptyText = $emptyText ?? __('common.no_results_found');
        $this->items = $this->buildItems($history, $type);
    }

    public function render()
    {
        return view('components.plan-timeline');
    }

    private function buildItems($history, string $type): array
    {
        if ($type === 'dashboard') {
            return $this->fromDashboardHistory($history);
        }

        return $this->fromCustomerHistory($history);
    }

    private function fromCustomerHistory($history): array
    {
        $collection = $this->toCollection($history);

        $reasonLabels = [
            EntrepreneurPlanHistory::REASON_INITIAL => 'Registro inicial',
            EntrepreneurPlanHistory::REASON_UPGRADE => 'Actualizacion de plan',
            EntrepreneurPlanHistory::REASON_DOWNGRADE => 'Cambio a plan inferior',
            EntrepreneurPlanHistory::REASON_MANUAL => 'Cambio manual',
        ];

        return $collection->map(function ($historyItem) use ($reasonLabels) {
            $plan = $historyItem->planChild?->plan;
            $planTitle = $plan?->title ?? __('common.plan');
            $levelOrder = $historyItem->planChild?->level_order;
            $activeChildCount = $plan?->planChildren?->where('is_active', true)->count() ?? 0;

            $planName = ($activeChildCount > 1 && $levelOrder)
                ? $planTitle . ' - Nivel ' . $levelOrder
                : $planTitle;

            $planStyles = is_array($plan?->styles) ? $plan->styles : [];
            $primaryColor = $this->normalizeColor($planStyles['primaryColor'] ?? null);
            $iconSvg = $planStyles['icon'] ?? null;

            $reasonText = $reasonLabels[$historyItem->assigned_reason] ?? __('common.plan_change');

            $startedTitle = $historyItem->started_at ? $historyItem->started_at->format('d/m/Y H:i') : '';
            $startedHuman = $historyItem->started_at ? $historyItem->started_at->diffForHumans() : '';

            return [
                'title' => $planName,
                'meta' => $reasonText,
                'date_title' => $startedTitle,
                'date_human' => $startedHuman,
                'active' => empty($historyItem->ended_at),
                'primary_color' => $primaryColor,
                'icon_svg' => $iconSvg,
            ];
        })->values()->all();
    }

    private function fromDashboardHistory($history): array
    {
        $collection = $this->toCollection($history);

        return $collection->map(function ($entry) {
            $key = $entry['change_type_key'] ?? 'upgrade';
            $descKey = match ($key) {
                'initial_registration' => 'common.plan_history_desc_initial',
                'upgrade' => 'common.plan_history_desc_upgrade',
                'downgrade' => 'common.plan_history_desc_downgrade',
                'admin_manual' => 'common.plan_history_desc_manual',
                default => 'common.plan_history_desc_unknown',
            };

            $changeDate = !empty($entry['change_date']) ? Carbon::parse($entry['change_date']) : null;

            $planTitle = $entry['plan_title'] ?? $entry['plan_label'] ?? __('common.plan');
            $levelOrder = $entry['plan_level'] ?? null;
            $childCount = $entry['plan_children_count'] ?? 0;

            $planName = ($childCount > 1 && $levelOrder)
                ? $planTitle . ' - Nivel ' . $levelOrder
                : $planTitle;

            $planStyles = is_array($entry['plan_styles'] ?? null) ? $entry['plan_styles'] : [];
            $primaryColor = $this->normalizeColor($planStyles['primaryColor'] ?? null);
            $iconSvg = $planStyles['icon'] ?? null;

            return [
                'title' => $planName,
                'meta' => __($descKey),
                'date_title' => $changeDate ? $changeDate->format('d/m/Y H:i:s') : '',
                'date_human' => $changeDate ? $changeDate->diffForHumans() : '',
                'active' => (bool) ($entry['is_active'] ?? false),
                'primary_color' => $primaryColor,
                'icon_svg' => $iconSvg,
            ];
        })->values()->all();
    }

    private function toCollection($history): Collection
    {
        if ($history instanceof Collection) {
            return $history;
        }

        if (is_array($history)) {
            return collect($history);
        }

        return collect();
    }

    private function normalizeColor(?string $color): string
    {
        if (is_string($color) && preg_match('/^#([0-9A-Fa-f]{6})$/', $color)) {
            return $color;
        }

        return '#e9ecef'; // Default color
    }
}
