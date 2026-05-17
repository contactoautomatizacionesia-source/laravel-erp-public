<?php

namespace Modules\CycleClosure\Repositories;

use Modules\CycleClosure\Entities\CycleLog;

class CycleLogRepository
{
    public function log(
        int $cycleId,
        string $phase,
        string $level,
        string $message,
        array $context = [],
        ?int $userId = null
    ): CycleLog {
        return CycleLog::create([
            'cycle_id'   => $cycleId,
            'phase'      => $phase,
            'level'      => $level,
            'message'    => $message,
            'context'    => $context ?: null,
            'user_id'    => $userId,
            'created_at' => now(),
        ]);
    }

    public function getByCycle(int $cycleId)
    {
        return CycleLog::where('cycle_id', $cycleId)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    public function getDataTableByCycle(int $cycleId)
    {
        return CycleLog::where('cycle_id', $cycleId)->with('user');
    }
}
