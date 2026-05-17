<?php

namespace Modules\CycleClosure\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CycleClosure\Services\CycleClosureService;
use Modules\UserActivityLog\Traits\LogActivity;

class RunCycleClosureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Un solo intento — el pipeline no es idempotente (crea un Cycle en BD).
     */
    public int $tries = 1;

    /**
     * 10 minutos máximo — suficiente para cualquier pipeline actual y futuro.
     */
    public int $timeout = 600;

    public function handle(CycleClosureService $service): void
    {
        $service->runCycle();
    }

    public function failed(Exception $e): void
    {
        LogActivity::errorLog('[RunCycleClosureJob] Fallo permanente: ' . $e->getMessage());
    }
}
