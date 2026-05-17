<?php

namespace Modules\CycleClosure\Pipeline\Steps;

use Illuminate\Support\Facades\DB;
use Modules\CycleClosure\Entities\Cycle;
use Modules\CycleClosure\Pipeline\CycleStepInterface;
use Modules\CycleClosure\Pipeline\StepResult;

class CheckPendingOrdersStep implements CycleStepInterface
{
    public function name(): string
    {
        return 'Verificación de órdenes pendientes';
    }

    public function phase(): string
    {
        return 'check_pending_orders';
    }

    public function run(Cycle $cycle): StepResult
    {
        $count = DB::table('orders')
            ->whereIn('order_status', ['pending', 'draft'])
            ->count();

        if ($count === 0) {
            return StepResult::ok(
                $this->name(),
                'No existen órdenes pendientes o en borrador.'
            );
        }

        return StepResult::warn(
            $this->name(),
            $count . ' orden(es) en estado pendiente o borrador encontrada(s). Deben ser procesadas o descartadas antes del cierre.',
            ['pending_orders_count' => $count]
        );
    }
}
