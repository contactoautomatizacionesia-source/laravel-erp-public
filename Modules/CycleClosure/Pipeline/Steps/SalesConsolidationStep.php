<?php

namespace Modules\CycleClosure\Pipeline\Steps;

use Illuminate\Support\Facades\DB;
use Modules\CycleClosure\Entities\Cycle;
use Modules\CycleClosure\Pipeline\CycleStepInterface;
use Modules\CycleClosure\Pipeline\StepResult;

class SalesConsolidationStep implements CycleStepInterface
{
    public function name(): string
    {
        return 'Consolidación de ventas del período';
    }

    public function phase(): string
    {
        return 'sales_consolidation';
    }

    public function run(Cycle $cycle): StepResult
    {
        try {
            $result = DB::table('orders')
                ->whereBetween('created_at', [
                    $cycle->period_start->startOfDay(),
                    $cycle->period_end->endOfDay(),
                ])
                ->whereIn('order_status', ['delivered', 'invoiced', 'completed'])
                ->selectRaw('SUM(grand_total) as total_sales, COUNT(*) as order_count')
                ->first();

            $totalSales = (float) ($result->total_sales ?? 0);
            $orderCount = (int) ($result->order_count ?? 0);

            $cycle->update(['total_sales' => $totalSales]);

            return StepResult::ok(
                $this->name(),
                'Ventas consolidadas: $' . number_format($totalSales, 2) . ' en ' . $orderCount . ' pedido(s).',
                ['total_sales' => $totalSales, 'order_count' => $orderCount]
            );
        } catch (\Exception $e) {
            return StepResult::warn(
                $this->name(),
                'Error al consolidar ventas: ' . $e->getMessage()
            );
        }
    }
}
