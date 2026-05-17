<?php

namespace Modules\CycleClosure\Pipeline\Steps;

use Illuminate\Support\Facades\DB;
use Modules\CycleClosure\Entities\Cycle;
use Modules\CycleClosure\Pipeline\CycleStepInterface;
use Modules\CycleClosure\Pipeline\StepResult;
use Modules\Wallet\Entities\WalletBalance;

class PointsConversionStep implements CycleStepInterface
{
    public function name(): string
    {
        return 'Conversión de puntos a billetera';
    }

    public function phase(): string
    {
        return 'points_conversion';
    }

    public function run(Cycle $cycle): StepResult
    {
        try {
            $resultData = DB::transaction(function () use ($cycle) {
                // Órdenes completadas con puntos pendientes de convertir dentro del período del ciclo.
                // Se usa point_value (congelado al momento de la compra) — no depende del rate vigente.
                $orders = DB::table('orders')
                    ->where('is_completed', 1)
                    ->where('point_convert', 0)
                    ->where('point_value', '>', 0)
                    ->whereBetween('created_at', [
                        $cycle->period_start->startOfDay(),
                        $cycle->period_end->endOfDay(),
                    ])
                    ->select('id', 'customer_id', 'point_value')
                    ->get();

                if ($orders->isEmpty()) {
                    return null;
                }

                // Agrupar por usuario y preparar inserción masiva
                $byUser = $orders->groupBy('customer_id');
                $now    = now();

                $walletBalancesToInsert = [];
                foreach ($byUser as $userId => $userOrders) {
                    $walletBalancesToInsert[] = [
                        'user_id'    => $userId,
                        'amount'     => $userOrders->sum('point_value'),
                        'type'       => 'point',
                        'status'     => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                WalletBalance::insert($walletBalancesToInsert);

                // Marcar todas las órdenes procesadas
                DB::table('orders')
                    ->whereIn('id', $orders->pluck('id'))
                    ->update(['point_convert' => 1]);

                return [
                    'orders_converted' => $orders->count(),
                    'users_credited'   => $byUser->count(),
                    'total_value'      => $orders->sum('point_value'),
                ];
            });

            if ($resultData === null) {
                return StepResult::ok(
                    $this->name(),
                    'No existen puntos pendientes de conversión en el período.'
                );
            }

            return StepResult::ok(
                $this->name(),
                "Se convirtieron puntos de {$resultData['orders_converted']} orden(es) para {$resultData['users_credited']} usuario(s). Total acreditado: \${$resultData['total_value']}.",
                $resultData
            );
        } catch (\Throwable $e) {
            return StepResult::warn(
                $this->name(),
                'Error al procesar la conversión de puntos: ' . $e->getMessage()
            );
        }
    }
}
