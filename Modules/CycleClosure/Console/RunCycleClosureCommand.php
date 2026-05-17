<?php

namespace Modules\CycleClosure\Console;

use Illuminate\Console\Command;
use Modules\CycleClosure\Repositories\CycleSettingRepository;
use Modules\CycleClosure\Services\CycleClosureService;

class RunCycleClosureCommand extends Command
{
    protected $signature = 'cycle:close {--force : Omite la validación del día configurado (solo para pruebas)}';

    protected $description = 'Despacha el pipeline de cierre de ciclo operativo (LIF) en background.';

    public function handle(CycleSettingRepository $settingRepo, CycleClosureService $service): int
    {
        $setting = $settingRepo->getActive();

        if (! $setting) {
            $this->warn('[CycleClosure] No hay configuración activa. Saltando ejecución.');
            return Command::SUCCESS;
        }

        $expectedDay = (int) ($setting->execution_day ?? 1);

        if (! $this->option('force') && (int) now()->day !== $expectedDay) {
            $this->info('[CycleClosure] Hoy no es el día configurado (' . $expectedDay . '). Saltando.');
            return Command::SUCCESS;
        }

        $this->info('[CycleClosure] Iniciando pipeline de cierre de ciclo...');

        $cycle = $service->runCycle();

        $this->info('[CycleClosure] Pipeline completado. Ciclo ID: ' . $cycle->id . ' — Estado: ' . $cycle->status);

        return Command::SUCCESS;
    }
}
