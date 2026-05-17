<?php

namespace Modules\InventoryCount\Services;

use App\Exceptions\CustomHandledException;
use App\Traits\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Modules\InventoryCount\Events\InventoryCountDifferenceDetected;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\InventoryCount\Entities\InventoryCount;
use Modules\InventoryCount\Entities\InventoryCountSetting;
use Modules\InventoryCount\Repositories\InventoryCountAuditRepository;
use Modules\InventoryCount\Repositories\InventoryCountRepository;
use Modules\InventoryCount\Repositories\InventoryCountSettingRepository;
use Modules\UserActivityLog\Traits\LogActivity;

class InventoryCountService
{
    use Notification;

    public function __construct(
        public InventoryCountRepository        $repo,
        public InventoryCountSettingRepository $settingRepo,
        public InventoryCountAuditRepository   $auditRepo,
    ) {}

    private const NOTIFICATION_SLUG_RECOUNT = 'inventory-recount';

    // -------------------------------------------------------------------------
    // CONFIGURACIÓN
    // -------------------------------------------------------------------------

    public function getSettingsIndexData(): array
    {
        $configuredIds = \Modules\InventoryCount\Entities\InventoryCountSetting::pluck('cost_center_id')->toArray();

        return [
            'costCenters' => \Modules\CostCenter\Entities\CostCenter::where('status', 1)
                ->whereNotIn('id', $configuredIds)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'roles'       => $this->settingRepo->getAvailableRoles(),
            'adminUsers'  => $this->settingRepo->getAdminUsers(),
        ];
    }

    public function getSettingsFormData(int $costCenterId): array
    {
        return [
            'setting'    => $this->settingRepo->findByCostCenter($costCenterId),
            'roles'      => $this->settingRepo->getAvailableRoles(),
            'adminUsers' => $this->settingRepo->getAdminUsers(),
        ];
    }

    public function saveSetting(int $costCenterId, array $validated): InventoryCountSetting
    {
        return $this->settingRepo->upsert($costCenterId, $validated);
    }

    // -------------------------------------------------------------------------
    // CONTEO
    // -------------------------------------------------------------------------

    public function getCountFormData(int $costCenterId): array
    {
        return [
            'products'          => $this->repo->getProductsForCenter($costCenterId),
            'observationTypes'  => $this->repo->getActiveObservationTypes(),
            'countCode'         => $this->repo->generateCountCode($costCenterId),
        ];
    }

    /**
     * Crea el conteo y corre R1/R2 en una sola transacción.
     * Nada se persiste en BD hasta que el usuario confirma el envío.
     *
     * Retorna array con:
     *   - success: bool
     *   - status: 'correct' | 'incorrect' | 'limit_exceeded'
     *   - count_id: int
     *   - remaining_attempts: int|null
     *   - message: string
     */
    public function createAndSubmit(int $costCenterId, int $userId, array $deviceInfo, array $lines): array
    {
        return DB::transaction(function () use ($costCenterId, $userId, $deviceInfo, $lines) {
            $setting      = $this->settingRepo->findByCostCenter($costCenterId);
            $attemptsUsed = $this->repo->countTodayAttemptsForCenter($costCenterId);

            if ($setting?->hasLimit() && $attemptsUsed >= $setting->max_attempts) {
                return [
                    'success' => false,
                    'status'  => 'limit_exceeded',
                    'message' => __('inventorycount::messages.count_limit_exceeded'),
                ];
            }

            $count = $this->repo->create([
                'count_code'     => $this->repo->generateCountCode($costCenterId),
                'cost_center_id' => $costCenterId,
                'user_id'        => $userId,
                'status'         => 'pending',
                'audit_status'   => 'pending',
                'attempt_number' => $attemptsUsed + 1,
                'started_at'     => now(),
                'device_info'    => $deviceInfo,
            ]);

            $result = $this->runR1R2($count, $lines, $deviceInfo);

            // Si hay diferencias e intentos restantes, enriquecer el mensaje con el conteo restante.
            if ($result['status'] === 'incorrect' && $setting?->hasLimit()) {
                $remaining = $setting->max_attempts - ($attemptsUsed + 1);
                $result['message'] = $remaining > 0
                    ? __('inventorycount::messages.count_incorrect_with_remaining', ['remaining' => $remaining])
                    : __('inventorycount::messages.count_limit_exceeded');
            }

            return $result;
        });
    }

    /**
     * Lógica R1/R2 compartida entre createAndSubmit y resubmitCount.
     */
    private function runR1R2(InventoryCount $count, array $lines, array $deviceInfo): array
    {
        $countId = $count->id;

        $this->repo->upsertDetails($countId, $lines, isDraft: false);

        // Una cantidad null (no contada) sobre un producto con stock en sistema
        // también es una diferencia: el asesor no pudo verificar ese ítem.
        $hasDifferences = collect($lines)->some(function ($line) {
            $systemStock = (int) $line['system_stock'];
            if (! isset($line['physical_quantity'])) {
                return $systemStock > 0;
            }
            return (int) $line['physical_quantity'] !== $systemStock;
        });

        $result = $hasDifferences ? 'incorrect' : 'correct';
        $status = $hasDifferences ? 'incorrect' : 'correct';

        $this->repo->update($count, [
            'status'       => $status,
            'audit_status' => 'pending',
            'finished_at'  => now(),
        ]);

        // Disparar evento para que el módulo Incidents cree las novedades por producto
        if ($hasDifferences) {
            try {
                Event::dispatch(new InventoryCountDifferenceDetected(
                    count:  $count->fresh(),
                    userId: $count->user_id,
                ));
            } catch (\Throwable $e) {
                Log::error('[InventoryCount] Error al disparar InventoryCountDifferenceDetected: ' . $e->getMessage());
            }
        }

        $this->repo->createAttempt([
            'inventory_count_id' => $countId,
            'user_id'            => $count->user_id,
            'attempt_number'     => $count->attempt_number,
            'result'             => $result,
            'device_info'        => $deviceInfo,
            'attempted_at'       => now(),
        ]);

        $message = $hasDifferences
            ? __('inventorycount::messages.count_submitted_with_differences')
            : __('inventorycount::messages.count_correct');

        return [
            'success'  => true,
            'status'   => $status,
            'count_id' => $countId,
            'message'  => $message,
        ];
    }

    // -------------------------------------------------------------------------
    // AUDITORÍA
    // -------------------------------------------------------------------------

    public function getReviewData(int $countId): array
    {
        $count  = $this->repo->findById($countId);
        $rejectedCount = $this->auditRepo->countIncorrectByUser($count->user_id);

        return [
            'count'         => $count,
            'rejectedCount' => $rejectedCount,
        ];
    }

    /**
     * Procesa la revisión del administrador.
     *
     * - 'rejected': borra detalles y notifica asesor para re-contar
     * - 'approved': si hay diferencias aplica ajuste de stock en el centro de costo
     */
    public function processAudit(int $countId, string $auditStatus, string $notes, int $auditorId): array
    {
        return DB::transaction(function () use ($countId, $auditStatus, $notes, $auditorId) {
            $count = InventoryCount::with(['details', 'costCenter'])->findOrFail($countId);

            // Evitar re-auditoría: si el conteo ya fue aprobado, el ajuste de stock
            // ya se aplicó y volver a procesar corrompería el inventario.
            if ($count->audit_status === 'approved') {
                return ['success' => false, 'message' => __('inventorycount::messages.audit_already_processed')];
            }

            // Crear registro de auditoría
            $this->auditRepo->create([
                'inventory_count_id' => $countId,
                'auditor_id'         => $auditorId,
                'status'             => $auditStatus,
                'notes'              => $notes,
            ]);

            // Actualizar estado de auditoría en el conteo
            $this->repo->update($count, ['audit_status' => $auditStatus]);

            if ($auditStatus === 'rejected') {
                // Cerrar todos los otros intentos del día (igual que al aprobar),
                // dejando el rechazado como referencia histórica con sus detalles intactos.
                InventoryCount::where('user_id', $count->user_id)
                    ->where('cost_center_id', $count->cost_center_id)
                    ->whereDate('created_at', $count->created_at->toDateString())
                    ->where('id', '!=', $countId)
                    ->whereNotIn('audit_status', ['approved', 'rejected', 'closed'])
                    ->update(['status' => 'closed', 'audit_status' => 'closed']);

                $notificationData = [
                    'count_code' => $count->count_code,
                    'notes'      => $notes,
                ];

                $this->sendInventoryRecountRejectedNotification(
                    self::NOTIFICATION_SLUG_RECOUNT,
                    $count->user_id,
                    $notificationData
                );
            }

            if ($auditStatus === 'approved') {
                if ($count->status === 'incorrect') {
                    // Ajuste de stock en el centro de costo basado en lo reportado por el asesor.
                    //
                    // TODO (SALIDAS): Este punto de ajuste debe integrarse con la lógica de // NOSONAR
                    // "salidas" de inventario que está siendo desarrollada de forma paralela.
                    // Por ahora se actualiza directamente cost_center_inventories.
                    // Cuando se implemente el módulo de salidas, este proceso debe generar
                    // el documento de salida correspondiente y delegar el ajuste a ese módulo.
                    $this->applyStockAdjustment($count);
                }

                // Cerrar todos los intentos del mismo día que no sean este conteo aprobado
                InventoryCount::where('user_id', $count->user_id)
                    ->where('cost_center_id', $count->cost_center_id)
                    ->whereDate('created_at', $count->created_at->toDateString())
                    ->where('id', '!=', $countId)
                    ->whereNotIn('audit_status', ['approved', 'rejected', 'closed'])
                    ->update(['status' => 'closed', 'audit_status' => 'closed']);
            }

            Log::info("Auditoría procesada: Conteo #{$count->count_code} → {$auditStatus} por admin ID {$auditorId}");

            return ['success' => true, 'message' => __('inventorycount::messages.audit_saved')];
        });
    }

    public function sendInventoryRecountRejectedNotification(string $slug, int $userId, array $notificationData = [])
    {
        try {
            $notification = NotificationSetting::where('slug', $slug)->first();

            if ($notification) {
                $messageTranslations = $notification->getTranslations('message');

                $adminMsgTranslations = $notification->getTranslations('admin_msg');

                // Definir las variables para el reemplazo
                $search = ['{PRODUCT_NAME}', '{NOTES}'];

                $replace = [
                    $notificationData['count_code'],
                    $notificationData['notes']
                ];

                // Reemplazar en cada idioma del array
                foreach ($messageTranslations as $locale => $value) {
                    $messageTranslations[$locale] = str_replace($search, $replace, $value);
                }

                foreach ($adminMsgTranslations as $locale => $value) {
                    $adminMsgTranslations[$locale] = str_replace($search, $replace, $value);
                }

                // Asignar las traducciones procesadas de vuelta al modelo
                $notification->setTranslations('message', $messageTranslations);
                $notification->setTranslations('admin_msg', $adminMsgTranslations);

                $this->typeId = EmailTemplateType::where('type', 'inventory_recount_template')->firstOrFail()->id;

                $this->order_on_notification = (object) [
                    'order_number' => $notificationData['count_code']
                ];

                $this->notificationSend($notification, $userId, 0, $notificationData, $slug);

                LogActivity::successLog(
                    __('inventorycount::messages.audit_rejected_log_msg', [
                        'code' => $notificationData['count_code'],
                        'userId' => $userId,
                        'notes' => $notificationData['notes'],
                    ])
                );
            }
        } catch (\Exception $e) {
            $context = __('inventorycount::messages.audit_rejected_error_log_msg', [
                'error' => $e->getMessage(),
            ]);

            throw new CustomHandledException($e, (string) $context);
        }
    }
    // -------------------------------------------------------------------------
    // HELPERS PRIVADOS
    // -------------------------------------------------------------------------

    private function applyStockAdjustment(InventoryCount $count): void
    {
        foreach ($count->details as $detail) {
            // null = no contado; se trata como 0 físico (el ítem no fue encontrado).
            $physicalQty = $detail->physical_quantity ?? 0;
            $difference  = $physicalQty - $detail->system_stock;
            if ($difference === 0) {
                continue;
            }

            // Ajustar stock en cost_center_inventories agrupando por product_id → skus
            // Se buscan todos los SKU del producto en el centro y se distribuye el ajuste
            $skus = DB::table('cost_center_inventories as cci')
                ->join('product_sku as ps', 'cci.product_sku_id', '=', 'ps.id')
                ->where('cci.cost_center_id', $count->cost_center_id)
                ->where('ps.product_id', $detail->product_id)
                ->select('cci.id', 'cci.qty')
                ->get();

            if ($skus->isEmpty()) {
                continue;
            }

            // Distribuir el ajuste proporcionalmente entre los SKU del producto
            $totalCurrentQty = $skus->sum('qty');
            $remaining = $difference;

            foreach ($skus as $index => $sku) {
                $isLast = $index === $skus->count() - 1;
                $adjustment = $isLast
                    ? $remaining
                    : (int) round($difference * ($sku->qty / max($totalCurrentQty, 1)));

                $newQty = max(0, $sku->qty + $adjustment);
                DB::table('cost_center_inventories')->where('id', $sku->id)->update(['qty' => $newQty]);
                $remaining -= $adjustment;
            }
        }
    }

}
