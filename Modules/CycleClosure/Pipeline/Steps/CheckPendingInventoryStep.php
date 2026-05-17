<?php

namespace Modules\CycleClosure\Pipeline\Steps;

use Illuminate\Support\Facades\DB;
use Modules\CycleClosure\Entities\Cycle;
use Modules\CycleClosure\Pipeline\CycleStepInterface;
use Modules\CycleClosure\Pipeline\StepResult;

class CheckPendingInventoryStep implements CycleStepInterface
{
    public function name(): string
    {
        return 'Verificación de conteos de inventario abiertos';
    }

    public function phase(): string
    {
        return 'check_pending_inventory';
    }

    public function run(Cycle $cycle): StepResult
    {
        $count = DB::table('inventory_counts')
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        if ($count === 0) {
            return StepResult::ok(
                $this->name(),
                'No existen conteos de inventario abiertos.'
            );
        }

        return StepResult::warn(
            $this->name(),
            $count . ' conteo(s) de inventario sin cerrar encontrado(s). Deben ser finalizados o cancelados antes del cierre.',
            ['open_inventory_counts' => $count]
        );
    }
}
