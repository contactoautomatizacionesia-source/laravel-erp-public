<?php

namespace Modules\Plans\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserSnapshotSupport
{
    private const QUALIFIED_ORDER_STATUSES = ['delivered', 'invoiced', 'completed'];

    public function resolveCurrentEvaluationWindow(): array
    {
        $latestClosedPeriodEnd = DB::table('cycles')
            ->where('status', 'closed')
            ->max('period_end');

        $start = $latestClosedPeriodEnd
            ? Carbon::parse($latestClosedPeriodEnd)->addDay()->startOfDay()
            : now()->startOfMonth()->startOfDay();

        return [
            'start' => $start,
            'end'   => now(),
        ];
    }

    public function qualifiedOrdersQuery()
    {
        return DB::table('orders')
            ->whereNotNull('customer_id')
            ->where('is_cancelled', 0)
            ->where(function ($query) {
                $query->where('is_completed', 1)
                    ->orWhereIn('order_status', self::QUALIFIED_ORDER_STATUSES);
            });
    }

    public function resolvePlanChildForMoment(array $history, Carbon $moment): ?int
    {
        foreach ($history as $entry) {
            $startedAt = Carbon::parse($entry->started_at);
            $endedAt   = $entry->ended_at ? Carbon::parse($entry->ended_at) : null;

            if ($startedAt->lte($moment) && ($endedAt === null || $endedAt->gt($moment))) {
                return (int) $entry->plan_child_id;
            }
        }

        return null;
    }

    public function resolveCycleNetworkPoints(int $userId, Carbon $cycleStart, Carbon $cycleEnd): array
    {
        $row = DB::table('network_paths as np')
            ->join('orders as o', 'o.customer_id', '=', 'np.entrepreneur_id')
            ->leftJoin('customer_profiles as cp', 'cp.user_id', '=', 'np.entrepreneur_id')
            ->leftJoin('plan_child as pc', 'pc.id', '=', 'cp.plan_child_id')
            ->leftJoin('plan as p', 'p.id', '=', 'pc.plan_id')
            ->where('np.ancestor_id', $userId)
            ->where('np.depth', '>', 0)
            ->where('o.is_cancelled', 0)
            ->whereBetween('o.created_at', [$cycleStart, $cycleEnd])
            ->where(function ($query) {
                $query->where('o.is_completed', 1)
                    ->orWhereIn('o.order_status', self::QUALIFIED_ORDER_STATUSES);
            })
            ->selectRaw(
                'SUM(CASE WHEN p.is_life_title = 1 THEN o.total_points ELSE 0 END) as life_points,
                 SUM(CASE WHEN p.is_life_title = 1 THEN 0 ELSE o.total_points END) as no_life_points'
            )
            ->first();

        return [
            'life_points'    => (float) ($row->life_points ?? 0),
            'no_life_points' => (float) ($row->no_life_points ?? 0),
        ];
    }
}
