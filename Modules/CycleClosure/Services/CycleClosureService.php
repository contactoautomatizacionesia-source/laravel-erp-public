<?php

namespace Modules\CycleClosure\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\CycleClosure\Entities\Cycle;
use Modules\CycleClosure\Pipeline\CycleStepInterface;
use Modules\CycleClosure\Pipeline\Steps\CheckPendingInventoryStep;
use Modules\CycleClosure\Pipeline\Steps\CheckPendingOrdersStep;
use Modules\CycleClosure\Pipeline\Steps\PointsConversionStep;
use Modules\CycleClosure\Pipeline\Steps\SalesConsolidationStep;
use Modules\CycleClosure\Repositories\CycleLogRepository;
use Modules\CycleClosure\Repositories\CycleRepository;
use Modules\CycleClosure\Exceptions\NoActiveSettingException;
use Modules\CycleClosure\Repositories\CycleSettingRepository;
use Modules\UserActivityLog\Traits\LogActivity;
use PDF;

class CycleClosureService
{
    public CycleRepository $repo;
    public CycleLogRepository $logRepo;

    /**
     * Pipeline de pasos escalable.
     * Para agregar un nuevo paso: añadir la clase aquí.
     * Cada paso implementa CycleStepInterface y NUNCA lanza excepciones.
     *
     * @var class-string<CycleStepInterface>[]
     */
    protected array $steps = [
        PointsConversionStep::class,
        // CheckPendingOrdersStep::class,
        // CheckPendingInventoryStep::class,
        // SalesConsolidationStep::class,
        // TODO: [COMISIONES] CommissionsLiquidationStep::class — cuando el módulo esté disponible. // NOSONAR
    ];

    public function __construct(
        CycleRepository $repo,
        CycleLogRepository $logRepo,
        private CycleSettingRepository $settingRepo,
    ) {
        $this->repo    = $repo;
        $this->logRepo = $logRepo;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EJECUCIÓN DEL CRON
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Punto de entrada del cron.
     * Crea el ciclo, corre el pipeline completo, determina el estado final
     * y notifica al ejecutor.
     */
    public function runCycle(?int $executorId = null): Cycle
    {
        $activeSetting = $this->settingRepo->getActive();

        // Único check bloqueante: sin configuración activa no hay cierre.
        if (! $activeSetting) {
            throw new NoActiveSettingException();
        }

        $executorId    = $activeSetting->executor_user_id ?? $executorId;
        $executionTime = Carbon::now();

        $cycle = $this->repo->create([
            'period_label'   => $executionTime->format('Y-m'),
            'period_start'   => $executionTime->copy()->startOfMonth()->toDateString(),
            'period_end'     => $executionTime->copy()->endOfMonth()->toDateString(),
            'executor_id'    => $executorId,
            'co_approver_id' => $activeSetting->approver_user_id,
            'status'         => 'running',
            'executed_at'    => $executionTime,
        ]);

        $this->logRepo->log(
            cycleId: $cycle->id,
            phase:   'pipeline_start',
            level:   'info',
            message: 'Iniciando pipeline de cierre de ciclo.',
            userId:  $executorId
        );

        // ── Ejecutar pipeline ────────────────────────────────────────────────
        $stepResults = [];
        $hasWarnings = false;

        foreach ($this->steps as $stepClass) {
            /** @var CycleStepInterface $step */
            $step   = app($stepClass);
            $result = $step->run($cycle->fresh());

            $stepResults[$stepClass] = [
                'label'   => $result->label,
                'passed'  => $result->passed,
                'detail'  => $result->detail,
                'context' => $result->context,
            ];

            $this->logRepo->log(
                cycleId: $cycle->id,
                phase:   $step->phase(),
                level:   $result->passed ? 'success' : 'warning',
                message: $result->label . ': ' . $result->detail,
                context: $result->context,
                userId:  $executorId
            );

            if (! $result->passed) {
                $hasWarnings = true;
            }
        }

        // ── Guardar detalle del pipeline ─────────────────────────────────────
        $cycle->update(['pipeline_detail' => $stepResults]);

        // ── Determinar estado final ──────────────────────────────────────────
        $finalStatus = $hasWarnings ? 'needs_review' : 'pending_approval';

        $this->repo->updateStatus($cycle, $finalStatus);

        $this->logRepo->log(
            cycleId: $cycle->id,
            phase:   'pipeline_end',
            level:   $hasWarnings ? 'warning' : 'success',
            message: $hasWarnings
                ? 'Pipeline completado con advertencias. Requiere revisión del ejecutor.'
                : 'Pipeline completado sin errores. Esperando aprobación del co-aprobador.',
            userId:  $executorId
        );

        LogActivity::successLog('CycleClosure pipeline finished. Cycle ID: ' . $cycle->id . ' Status: ' . $finalStatus);

        // TODO: [NOTIFICATIONS] Notificar al ejecutor (executor_id) con el resultado del pipeline (éxito o advertencias). // NOSONAR

        return $cycle->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // APROBACIÓN DEL EJECUTOR (solo cuando needs_review)
    // ─────────────────────────────────────────────────────────────────────────

    public function approveByExecutor(int $cycleId): Cycle
    {
        $cycle = $this->repo->findById($cycleId);

        abort_if(! $cycle, 404);
        abort_if($cycle->status !== 'needs_review', 422, __('cycleclosure::messages.cycle_not_needs_review'));
        abort_if($cycle->executor_id !== auth()->id(), 403, __('cycleclosure::messages.not_authorized_executor'));

        $this->repo->updateStatus($cycle, 'pending_approval', [
            'executor_approved_at' => now(),
        ]);

        $this->logRepo->log(
            cycleId: $cycle->id,
            phase:   'pipeline',
            level:   'info',
            message: 'Ejecutor aprobó manualmente el ciclo con advertencias. Enviado a co-aprobador.',
            userId:  auth()->id()
        );

        // TODO: [NOTIFICATIONS] Notificar al co-aprobador (co_approver_id) que el ciclo está pendiente de su aprobación. // NOSONAR

        return $cycle->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CANCELACIÓN POR EJECUTOR (needs_review → cancelled)
    // ─────────────────────────────────────────────────────────────────────────

    public function cancelByExecutor(int $cycleId): Cycle
    {
        $cycle = $this->repo->findById($cycleId);

        abort_if(! $cycle, 404);
        abort_if($cycle->status !== 'needs_review', 422, __('cycleclosure::messages.cycle_not_needs_review'));
        abort_if($cycle->executor_id !== auth()->id(), 403, __('cycleclosure::messages.not_authorized_executor'));

        $this->repo->updateStatus($cycle, 'cancelled');

        $this->logRepo->log(
            cycleId: $cycle->id,
            phase:   'pipeline',
            level:   'error',
            message: 'Ejecutor canceló el ciclo tras revisar las advertencias.',
            userId:  auth()->id()
        );

        return $cycle->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // APROBACIÓN / RECHAZO DEL CO-APROBADOR
    // ─────────────────────────────────────────────────────────────────────────

    public function approveByCoapprover(int $cycleId): Cycle
    {
        $cycle = $this->repo->findPendingApproval($cycleId);

        abort_if(! $cycle, 422, __('cycleclosure::messages.cycle_not_pending'));
        abort_if($cycle->co_approver_id !== auth()->id(), 403, __('cycleclosure::messages.not_authorized_approver'));

        $this->repo->updateStatus($cycle, 'closed', [
            'approved_at' => now(),
        ]);

        $this->logRepo->log(
            cycleId: $cycle->id,
            phase:   'act_generation',
            level:   'info',
            message: 'Co-aprobador dio visto bueno. Generando acta PDF.',
            userId:  auth()->id()
        );

        $actPath = $this->generateAct($cycle->fresh());

        $cycle->update(['act_path' => $actPath]);

        $this->logRepo->log(
            cycleId: $cycle->id,
            phase:   'act_generation',
            level:   'success',
            message: 'Acta generada y almacenada en: ' . $actPath
        );

        LogActivity::successLog('CycleClosure closed. Cycle ID: ' . $cycle->id);

        // TODO: [NOTIFICATIONS] Notificar al ejecutor y co-aprobador que el cierre fue exitoso y el acta está disponible. // NOSONAR

        return $cycle->fresh();
    }

    public function rejectByCoapprover(int $cycleId): Cycle
    {
        $cycle = $this->repo->findPendingApproval($cycleId);

        abort_if(! $cycle, 422, __('cycleclosure::messages.cycle_not_pending'));
        abort_if($cycle->co_approver_id !== auth()->id(), 403, __('cycleclosure::messages.not_authorized_approver'));

        $this->repo->updateStatus($cycle, 'cancelled');

        $this->logRepo->log(
            cycleId: $cycle->id,
            phase:   'pipeline',
            level:   'error',
            message: 'Co-aprobador rechazó el ciclo.',
            userId:  auth()->id()
        );

        LogActivity::errorLog('CycleClosure rejected by co-approver. Cycle ID: ' . $cycle->id);

        // TODO: [NOTIFICATIONS] Notificar al ejecutor y co-aprobador que el cierre fue rechazado. // NOSONAR

        return $cycle->fresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERACIÓN DEL ACTA PDF
    // ─────────────────────────────────────────────────────────────────────────

    private function generateAct(Cycle $cycle): string
    {
        $cycle->loadMissing(['executor', 'coApprover', 'logs']);

        $integrityHash = hash('sha256', $cycle->id . '|' . $cycle->period_label . '|' . $cycle->approved_at);

        $viewData = [
            'cycle'            => $cycle,
            'ipAddress'        => request()->ip(),
            'integrityHash'    => $integrityHash,
            'backupReference'  => 'snapshot-' . $cycle->period_label,
            'financialSummary' => $this->buildFinancialSummary($cycle),
            'inventorySummary' => [],
            'rankSummary'      => [],
        ];

        $config = [
            'instanceConfigurator' => function ($mpdf) {
                $mpdf->autoScriptToLang = true;
                $mpdf->baseScript       = 1;
                $mpdf->autoLangToFont   = true;
            },
        ];

        $pdf = PDF::loadView('cycleclosure::pdf.acta', $viewData, [], $config);

        $directory = 'cycle_closure/actas/' . $cycle->period_label;
        Storage::makeDirectory($directory);

        $filename = $directory . '/Acta-' . $cycle->period_label
            . '-' . str_pad($cycle->id, 3, '0', STR_PAD_LEFT) . '.pdf';

        Storage::put($filename, $pdf->output());

        return $filename;
    }

    private function buildFinancialSummary(Cycle $cycle): array
    {
        $totalSales = $cycle->total_sales ?? 0;

        $orderCount = DB::table('orders')
            ->whereBetween('created_at', [
                $cycle->period_start->startOfDay(),
                $cycle->period_end->endOfDay(),
            ])
            ->whereIn('order_status', ['delivered', 'invoiced', 'completed'])
            ->count();

        return [
            [
                'concept'  => 'Pedidos procesados en el período',
                'quantity' => $orderCount,
                'value'    => '$ ' . number_format($totalSales, 2, ',', '.'),
            ],
            [
                'concept'  => 'Total Ventas (TOTAL)',
                'quantity' => null,
                'value'    => '$ ' . number_format($totalSales, 2, ',', '.'),
            ],
        ];
    }
}
