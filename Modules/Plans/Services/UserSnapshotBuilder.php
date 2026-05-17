<?php

namespace Modules\Plans\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\SignatureBatch;

/**
 * Builds the user context snapshot used by rule checkers.
 *
 * All DB queries are centralized here. Rule checkers receive the pre-built
 * context array and must NOT execute their own queries.
 *
 * Context shape:
 * [
 *   'user_id'               => int,
 *   'current_plan_child_id' => int|null,
 *   'personal_points'       => float,   // provisional: user's own purchases in the inferred current open window
 *   'children_points'       => float,   // provisional: points from direct+indirect referrals in the inferred current open window
 *   'total_points'          => float,   // personal + children
 *   'closed_cycles'         => array,   // recent closed cycles: [{cycle_id, plan_child_id, personal_points, life_points, no_life_points, closed_at}]
 *   'downline'              => array,   // flat downline: [{user_id, plan_child_id, generation, personal_points, benefit_total, is_life_title, ancestor_plan_child_ids}]
 * ]
 */
class UserSnapshotBuilder
{
    private const NETWORK_PATHS = 'network_paths as np';

    public function __construct(
        private readonly UserSnapshotSupport $support,
    ) {}

    /**
     * Build the full context array for a given userId.
     */
    public function build(int $userId): array
    {
        $personalPoints     = $this->resolvePersonalPoints($userId);
        $childrenPoints     = $this->resolveChildrenPoints($userId);

        return [
            'user_id'               => $userId,
            'current_plan_child_id' => $this->resolveCurrentPlan($userId),
            'personal_points'       => $personalPoints,
            'children_points'       => $childrenPoints,
            'total_points'          => $personalPoints + $childrenPoints,
            'closed_cycles'         => $this->resolveClosedCycles($userId),
            'downline'              => $this->resolveDownline($userId),
            'has_formalized_documentation' => $this->resolveDocumentationStatus($userId),
        ];
    }

    /**
     * Returns true if the entrepreneur has signed all their contracts.
     *
     * Checks the most recent signature batch for the user. A batch with
     * status = 'completed' means every document in the batch has been signed.
     * If no batch exists (e.g. ProtecData disabled in QA), returns true so
     * evaluations are not blocked in environments where signing is inactive.
     */
    private function resolveDocumentationStatus(int $userId): bool
    {
        $latestBatch = SignatureBatch::where('user_id', $userId)
            ->latest()
            ->first();

        if ($latestBatch === null) {
            return true;
        }

        return $latestBatch->isCompleted();
    }

    /**
     * Returns the current active plan_child_id from customer_profiles.
     */
    private function resolveCurrentPlan(int $userId): ?int
    {
        $profile = DB::table('customer_profiles')
            ->where('user_id', $userId)
            ->value('plan_child_id');

        return $profile ? (int) $profile : null;
    }

    /**
     * Returns the user's personal purchase points in the inferred current open window.
     *
     * Provisional rule:
     * - start: day after the latest closed cycle end
     * - fallback when no closed cycle exists: first day of current month
     *
     * This avoids using historical lifetime points as "current" points while the
     * dedicated current-cycle points ledger is still pending.
     */
    private function resolvePersonalPoints(int $userId): float // NOSONAR
    {
        return (float) $this->support->qualifiedOrdersQuery()
            ->where('customer_id', $userId)
            ->sum('total_points');
    }

    /**
     * Returns cumulative points from all downline members (all generations)
     * in the inferred current open window.
     */
    private function resolveChildrenPoints(int $userId): float // NOSONAR
    {
        $window = $this->support->resolveCurrentEvaluationWindow();

        return (float) DB::table(self::NETWORK_PATHS)
            ->join('orders as o', 'o.customer_id', '=', 'np.entrepreneur_id')
            ->where('np.ancestor_id', $userId)
            ->where('np.depth', '>', 0)
            ->where('o.is_cancelled', 0)
            ->whereBetween('o.created_at', [$window['start'], $window['end']])
            ->where(function ($query) {
                $query->where('o.is_completed', 1)
                    ->orWhereIn('o.order_status', ['delivered', 'invoiced', 'completed']);
            })
            ->sum('o.total_points');
    }

    /**
     * Returns closed cycle history for the user.
     *
     * Each entry:
     * [
     *   'cycle_id'        => int,
     *   'plan_child_id'   => int,
     *   'closed_at'       => string,
     *   'personal_points' => float,   // points from the user's own purchases in that cycle
     *   'life_points'     => float,   // points from Life-network referrals in that cycle
     *   'no_life_points'  => float,   // points from No-Life-network referrals in that cycle
     * ]
     */
    private function resolveClosedCycles(int $userId): array // NOSONAR
    {
        $cycles = DB::table('cycles')
            ->where('status', 'closed')
            ->orderByDesc('period_end')
            ->get(['id', 'period_start', 'period_end', 'approved_at']);

        if ($cycles->isEmpty()) {
            return [];
        }

        $history = DB::table('entrepreneur_plan_history')
            ->where('user_id', $userId)
            ->orderBy('started_at')
            ->get(['plan_child_id', 'started_at', 'ended_at']);

        $closedCycles = [];

        foreach ($cycles as $cycle) {
            $cycleStart = Carbon::parse($cycle->period_start)->startOfDay();
            $cycleEnd   = Carbon::parse($cycle->period_end)->endOfDay();
            $cutoff     = $cycleEnd;

            $planChildId = $this->support->resolvePlanChildForMoment($history->all(), $cutoff);
            if ($planChildId === null) {
                continue;
            }

            $personalPoints = (float) $this->support->qualifiedOrdersQuery()
                ->where('customer_id', $userId)
                ->whereBetween('created_at', [$cycleStart, $cycleEnd])
                ->sum('total_points');

            $networkPoints = $this->support->resolveCycleNetworkPoints($userId, $cycleStart, $cycleEnd);

            $closedCycles[] = [
                'cycle_id'        => (int) $cycle->id,
                'plan_child_id'   => $planChildId,
                'closed_at'       => $cycle->approved_at
                    ? Carbon::parse($cycle->approved_at)->toIso8601String()
                    : $cycleEnd->toIso8601String(),
                'personal_points' => $personalPoints,
                'life_points'     => $networkPoints['life_points'],
                'no_life_points'  => $networkPoints['no_life_points'],
            ];
        }

        return $closedCycles;
    }

    /**
     * Returns the flat downline list for the user.
     *
     * Each entry: [user_id, plan_child_id, generation, personal_points, benefit_total, is_life_title, ancestor_plan_child_ids]
     *
     * The 'ancestor_plan_child_ids' array must list plan_child_id of all ancestors up to the root user.
     * The 'is_life_title' flag indicates whether the member holds a "Life" category plan.
     * The 'benefit_total' represents the sum of benefits earned by that member.
     */
    private function resolveDownline(int $userId): array // NOSONAR
    {
        $window = $this->support->resolveCurrentEvaluationWindow();

        $pointsSubquery = $this->support->qualifiedOrdersQuery()
            ->selectRaw('customer_id, SUM(total_points) as personal_points')
            ->whereBetween('created_at', [$window['start'], $window['end']])
            ->groupBy('customer_id');

        $members = DB::table(self::NETWORK_PATHS)
            ->leftJoin('customer_profiles as cp', 'cp.user_id', '=', 'np.entrepreneur_id')
            ->leftJoin('plan_child as pc', 'pc.id', '=', 'cp.plan_child_id')
            ->leftJoin('plan as p', 'p.id', '=', 'pc.plan_id')
            ->leftJoinSub($pointsSubquery, 'pts', function ($join) {
                $join->on('pts.customer_id', '=', 'np.entrepreneur_id');
            })
            ->where('np.ancestor_id', $userId)
            ->where('np.depth', '>', 0)
            ->orderBy('np.depth')
            ->get([
                'np.entrepreneur_id as user_id',
                'cp.plan_child_id',
                'np.depth as generation',
                DB::raw('IFNULL(pts.personal_points, 0) as personal_points'),
                DB::raw('IFNULL(p.is_life_title, 0) as is_life_title'),
            ]);

        if ($members->isEmpty()) {
            return [];
        }

        $descendantIds = $members->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $subtreeIds    = array_merge([$userId], $descendantIds);

        $ancestorRows = DB::table(self::NETWORK_PATHS)
            ->leftJoin('customer_profiles as cp', 'cp.user_id', '=', 'np.ancestor_id')
            ->whereIn('np.entrepreneur_id', $descendantIds)
            ->whereIn('np.ancestor_id', $subtreeIds)
            ->where('np.depth', '>', 0)
            ->get([
                'np.entrepreneur_id',
                'cp.plan_child_id',
                'np.depth',
            ]);

        $ancestorsByDescendant = [];
        foreach ($ancestorRows as $row) {
            $descendantId = (int) $row->entrepreneur_id;
            $planChildId  = $row->plan_child_id ? (int) $row->plan_child_id : null;

            if ($planChildId !== null) {
                $ancestorsByDescendant[$descendantId][] = [
                    'depth' => (int) $row->depth,
                    'plan_child_id' => $planChildId,
                ];
            }
        }

        $downline = [];
        foreach ($members as $member) {
            $descendantId = (int) $member->user_id;
            $ancestorPlanChildIds = collect($ancestorsByDescendant[$descendantId] ?? [])
                ->sortBy('depth')
                ->pluck('plan_child_id')
                ->unique()
                ->values()
                ->all();

            $downline[] = [
                'user_id'                 => $descendantId,
                'plan_child_id'           => $member->plan_child_id ? (int) $member->plan_child_id : null,
                'generation'              => (int) $member->generation,
                'personal_points'         => (float) $member->personal_points,
                'benefit_total'           => 0.0,
                'is_life_title'           => (bool) $member->is_life_title,
                'ancestor_plan_child_ids' => $ancestorPlanChildIds,
            ];
        }

        return $downline;
    }
}
