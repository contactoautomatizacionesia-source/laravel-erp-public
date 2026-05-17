<?php

namespace Modules\Plans\Helpers;

use Illuminate\Support\Facades\Auth;
use Modules\Customer\Entities\EntrepreneurPlanHistory;

class PlanHistoryHelper
{
    /**
     * Resolve the plan change history for a given user (or the authenticated user).
     *
     * Each entry represents one plan assignment period and includes:
     *   - change_date       : when this plan was assigned (started_at)
     *   - plan_label        : display name of the assigned plan (parent + child if multi-level)
     *   - from_plan_label   : display name of the previous plan (null for the first entry)
     *   - change_type       : human-readable type derived from assigned_reason
     *   - change_type_key   : raw assigned_reason constant value
     *   - description       : justification / reason text
     *   - next_plan_label   : display name of the plan the user moved to next (null if still active)
     *   - ended_at          : when this plan ended (null = currently active)
     *   - is_active         : whether this is the current active assignment
     *   - assigned_by_name  : name of the admin or system that made the assignment
     *
     * @param  int|null  $userId  If null, resolves the authenticated user.
     * @return array[]|null       Null when no user context can be resolved.
     */
    public static function resolve(?int $userId = null): ?array
    {
        $resolvedUserId = $userId ?? Auth::id();
        if (! $resolvedUserId) {
            return null;
        }

        $history = EntrepreneurPlanHistory::with([
            'planChild.plan' => function ($query) {
                $query->withCount('planChildren');
            },
            'assignedBy',
        ])
            ->where('user_id', $resolvedUserId)
            ->orderBy('started_at', 'asc')
            ->get();

        if ($history->isEmpty()) {
            return [];
        }

        // Build a flat array first so we can reference prev/next by index
        $entries = $history->values();
        $result  = [];

        foreach ($entries as $i => $entry) {
            $prev = $i > 0               ? $entries[$i - 1] : null;
            $next = isset($entries[$i + 1]) ? $entries[$i + 1] : null;
            $planContext = PlanContextHelper::resolve(planChildId: $entry->plan_child_id);

            $result[] = [
                'change_date'      => $entry->started_at?->toIso8601String(),
                'plan_label'       => self::buildLabel($entry->planChild),
                'from_plan_label'  => $prev ? self::buildLabel($prev->planChild) : null,
                'change_type'      => self::changeTypeLabel($entry->assigned_reason),
                'change_type_key'  => $entry->assigned_reason,
                'description'      => self::changeDescription($entry->assigned_reason),
                'next_plan_label'  => $next ? self::buildLabel($next->planChild) : null,
                'ended_at'         => $entry->ended_at?->toIso8601String(),
                'is_active'        => $entry->ended_at === null,
                'assigned_by_name' => $entry->assignedBy?->name ?? null,
                'plan_title'       => $entry->planChild?->plan?->title,
                'plan_level'       => $entry->planChild?->level_order,
                'plan_children_count' => $entry->planChild?->plan?->plan_children_count,
                'plan_styles'      => $entry->planChild?->plan?->styles,
                'plan_context'     => $planContext,
            ];
        }

        // Return most recent first (dashboard convention)
        return array_reverse($result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build a human-readable plan label.
     * Single-child plans → "PlanName"
     * Multi-child plans  → "PlanName > LevelOrder"
     */
    private static function buildLabel(?\Modules\Plans\Entities\PlanChild $planChild): ?string
    {
        if (! $planChild || ! $planChild->plan) {
            return null;
        }

        $plan       = $planChild->plan;
        $childCount = $plan->plan_children_count ?? $plan->planChildren()->count();
        $planTitle  = $plan->title;

        if ($childCount <= 1) {
            return $planTitle;
        }

        return $planTitle . ' > ' . $planChild->level_order;
    }

    /**
     * Map assigned_reason constant to a human-readable label.
     */
    private static function changeTypeLabel(?string $reason): string
    {
        return match ($reason) {
            EntrepreneurPlanHistory::REASON_INITIAL   => 'Registro inicial',
            EntrepreneurPlanHistory::REASON_UPGRADE   => 'Subida de nivel',
            EntrepreneurPlanHistory::REASON_DOWNGRADE => 'Bajada de nivel',
            EntrepreneurPlanHistory::REASON_MANUAL    => 'Ajuste manual',
            default                                   => $reason ?? 'Desconocido',
        };
    }

    /**
     * Map assigned_reason to a short descriptive sentence.
     */
    private static function changeDescription(?string $reason): string
    {
        return match ($reason) {
            EntrepreneurPlanHistory::REASON_INITIAL   => 'Asignación inicial al momento del registro.',
            EntrepreneurPlanHistory::REASON_UPGRADE   => 'El empresario cumplió los requisitos y subió de nivel automáticamente.',
            EntrepreneurPlanHistory::REASON_DOWNGRADE => 'El empresario no mantuvo los requisitos del nivel y fue reclasificado.',
            EntrepreneurPlanHistory::REASON_MANUAL    => 'Cambio realizado manualmente por un administrador.',
            default                                   => 'Cambio de plan registrado en el sistema.',
        };
    }
}
