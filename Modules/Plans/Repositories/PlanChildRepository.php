<?php

namespace Modules\Plans\Repositories;

use Modules\Plans\Entities\PlanChild;

class PlanChildRepository
{
    public function getByPlan($planId)
    {
        return PlanChild::where('plan_id', $planId)
            ->withCount(['rules', 'benefits'])
            ->orderBy('level_order');
    }

    public function findById($id, $relations = [])
    {
        return PlanChild::with($relations)->findOrFail($id);
    }

    public function countByPlan(int $planId): int
    {
        return PlanChild::where('plan_id', $planId)->count();
    }

    /**
     * Increment level_order for all children in $planId with level_order >= $fromOrder.
     * Processed descending to avoid unique constraint conflicts.
     */
    public function incrementOrderFrom(int $planId, int $fromOrder): void
    {
        $ids = PlanChild::where('plan_id', $planId)
            ->where('level_order', '>=', $fromOrder)
            ->orderBy('level_order', 'desc')
            ->pluck('id');

        foreach ($ids as $id) {
            PlanChild::where('id', $id)->increment('level_order');
        }
    }

    /**
     * Shift level_order in [$from, $to] by +1 or -1 for a given plan.
     * Increment → descending order; Decrement → ascending order.
     */
    public function shiftBetween(int $planId, int $from, int $to, int $direction): void
    {
        $sortOrder = $direction > 0 ? 'desc' : 'asc';

        $ids = PlanChild::where('plan_id', $planId)
            ->whereBetween('level_order', [$from, $to])
            ->orderBy('level_order', $sortOrder)
            ->pluck('id');

        foreach ($ids as $id) {
            if ($direction > 0) {
                PlanChild::where('id', $id)->increment('level_order');
            } else {
                PlanChild::where('id', $id)->decrement('level_order');
            }
        }
    }

    /**
     * Assign level_order 1..N based on provided ID sequence.
     * Two-phase update to avoid unique constraint conflicts.
     */
    public function reorder(array $ids): void
    {
        if (empty($ids)) return;

        $planId = PlanChild::whereIn('id', $ids)->value('plan_id');
        $offset = PlanChild::where('plan_id', $planId)->count() + 1000;

        // Phase 1: move to a safe range far above any real values
        foreach ($ids as $index => $id) {
            PlanChild::where('id', $id)->update(['level_order' => $offset + $index + 1]);
        }

        // Phase 2: assign final consecutive values 1..N
        foreach ($ids as $index => $id) {
            PlanChild::where('id', $id)->update(['level_order' => $index + 1]);
        }
    }

    public function create(array $data)
    {
        return PlanChild::create($data);
    }

    public function decrementOrderAfter(int $planId, int $afterOrder): void
    {
        $ids = PlanChild::where('plan_id', $planId)
            ->where('level_order', '>', $afterOrder)
            ->orderBy('level_order', 'asc')
            ->pluck('id');

        foreach ($ids as $id) {
            PlanChild::where('id', $id)->decrement('level_order');
        }
    }

    public function update(PlanChild $planChild, array $data)
    {
        $planChild->update($data);
        return $planChild;
    }

    public function delete(PlanChild $planChild)
    {
        return $planChild->delete();
    }
}
