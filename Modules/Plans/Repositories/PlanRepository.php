<?php

namespace Modules\Plans\Repositories;

use Modules\Plans\Entities\Plan;

class PlanRepository
{
    public function getBaseQuery()
    {
        return Plan::with([])
            ->withCount('planChildren')
            ->orderBy('order', 'asc');
    }

    public function findById($id, $relations = [])
    {
        return Plan::with($relations)->findOrFail($id);
    }

    public function count(): int
    {
        return Plan::count();
    }

    /**
     * Increment order for all plans with order >= $fromOrder.
     * Processed descending to avoid unique constraint conflicts.
     */
    public function incrementOrderFrom(int $fromOrder): void
    {
        $ids = Plan::where('order', '>=', $fromOrder)
            ->orderBy('order', 'desc')
            ->pluck('id');

        foreach ($ids as $id) {
            Plan::where('id', $id)->increment('order');
        }
    }

    /**
     * Shift orders in [$from, $to] by +1 or -1.
     * Increment → descending order; Decrement → ascending order.
     */
    public function shiftBetween(int $from, int $to, int $direction): void
    {
        $sortOrder = $direction > 0 ? 'desc' : 'asc';

        $ids = Plan::whereBetween('order', [$from, $to])
            ->orderBy('order', $sortOrder)
            ->pluck('id');

        foreach ($ids as $id) {
            if ($direction > 0) {
                Plan::where('id', $id)->increment('order');
            } else {
                Plan::where('id', $id)->decrement('order');
            }
        }
    }

    /**
     * Assign order 1..N based on provided ID sequence.
     * Two-phase update to avoid unique constraint conflicts.
     */
    public function reorder(array $ids): void
    {
        $offset = Plan::count() + 1000;

        // Phase 1: move to a safe range far above any real values
        foreach ($ids as $index => $id) {
            Plan::where('id', $id)->update(['order' => $offset + $index + 1]);
        }

        // Phase 2: assign final consecutive values 1..N
        foreach ($ids as $index => $id) {
            Plan::where('id', $id)->update(['order' => $index + 1]);
        }
    }

    public function create(array $data)
    {
        return Plan::create($data);
    }

    public function update(Plan $plan, array $data)
    {
        $plan->update($data);
        return $plan;
    }

    public function delete(Plan $plan)
    {
        return $plan->delete();
    }
}
