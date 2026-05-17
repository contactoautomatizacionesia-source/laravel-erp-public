<?php

namespace Modules\InventoryCount\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\InventoryCount\Entities\InventoryCount;
use Modules\InventoryCount\Entities\InventoryCountAttempt;
use Modules\InventoryCount\Entities\InventoryCountDetail;
use Modules\GeneralSetting\Entities\Catalogs\ObservationType;

class InventoryCountRepository
{
    public function getDatatablesQuery(bool $isAdmin, ?int $userId = null, bool $allowHistory = true)
    {
        // Subquery: para cada grupo (user + cost_center + day) obtener el id del registro más reciente
        $latestIdsQuery = InventoryCount::selectRaw('MAX(id)')
            ->groupBy('user_id', 'cost_center_id', DB::raw('DATE(created_at)'));

        if (!$isAdmin) {
            $latestIdsQuery->where('user_id', $userId);

            if (!$allowHistory) {
                $latestIdsQuery->whereDate('created_at', today());
            }
        }

        // Query principal: solo los registros representativos del grupo
        // Se añade total_attempts como conteo de todos los registros del mismo grupo
        return InventoryCount::with(['costCenter', 'user', 'audit'])
            ->whereIn('id', $latestIdsQuery)
            ->select('inventory_counts.*')
            ->selectRaw('(
                SELECT COUNT(*) FROM inventory_counts ic2
                WHERE ic2.user_id = inventory_counts.user_id
                  AND ic2.cost_center_id = inventory_counts.cost_center_id
                  AND DATE(ic2.created_at) = DATE(inventory_counts.created_at)
            ) as total_attempts')
            ->orderByDesc('created_at');
    }

    public function findById(int $id): InventoryCount
    {
        return InventoryCount::with([
            'costCenter',
            'user',
            'details.product',
            'details.observationType',
            'attempts.user',
            'audit.auditor',
        ])->findOrFail($id);
    }

    /**
     * Retorna todos los conteos del mismo grupo (user + cost_center + día),
     * ordenados del más reciente al más antiguo, para el historial de intentos.
     */
    public function findGroupSiblings(InventoryCount $count): \Illuminate\Database\Eloquent\Collection
    {
        return InventoryCount::with(['user', 'costCenter'])
            ->where('user_id', $count->user_id)
            ->where('cost_center_id', $count->cost_center_id)
            ->whereDate('created_at', $count->created_at->toDateString())
            ->orderByDesc('id')
            ->get();
    }

    public function create(array $data): InventoryCount
    {
        return InventoryCount::create($data);
    }

    public function update(InventoryCount $count, array $data): InventoryCount
    {
        $count->update($data);
        return $count->fresh();
    }

    /**
     * Genera el código del conteo: CNT-YYYY-MM-DD-N
     * donde N es el número de intento del día para ese centro de costo.
     */
    public function generateCountCode(int $costCenterId): string
    {
        $today = now();
        $prefix = $today->format('Y-m-d');

        $attemptNumber = InventoryCount::where('cost_center_id', $costCenterId)
            ->whereDate('created_at', $today->toDateString())
            ->count() + 1;

        return sprintf('CNT-%s-%d', $prefix, $attemptNumber);
    }

    /**
     * Retorna el conteo aprobado del mismo grupo (user + cost_center + día), si existe.
     */
    public function findApprovedSibling(InventoryCount $count): ?InventoryCount
    {
        return InventoryCount::where('user_id', $count->user_id)
            ->where('cost_center_id', $count->cost_center_id)
            ->whereDate('created_at', $count->created_at->toDateString())
            ->where('audit_status', 'approved')
            ->first();
    }

    /**
     * Verifica si existe un conteo rechazado hoy para el centro de costo
     * sin un reconteo posterior (approved o nuevo pending/correct/incorrect).
     * Usado para saltarse el límite de intentos en reconteos ordenados por el admin.
     */
    /**
     * Determina si el asesor debe poder crear un reconteo ignorando el límite diario.
     * Solo aplica cuando: hay al menos un rechazo hoy, no hay aprobado, y el límite ya se agotó.
     * Si aún tiene intentos disponibles, los usa normalmente sin necesidad de este bypass.
     */
    public function hasPendingRecountToday(int $costCenterId, int $maxAttempts): bool
    {
        $limitAgotado = $maxAttempts > 0
            && $this->countTodayAttemptsForCenter($costCenterId) >= $maxAttempts;

        if (!$limitAgotado) {
            return false;
        }

        // Límite agotado: verificar si hay un rechazado sin reconteo activo posterior
        $lastRejectedId = InventoryCount::where('cost_center_id', $costCenterId)
            ->whereDate('created_at', today())
            ->where('audit_status', 'rejected')
            ->max('id');

        return $lastRejectedId && !InventoryCount::where('cost_center_id', $costCenterId)
            ->whereDate('created_at', today())
            ->where('id', '>', $lastRejectedId)
            ->whereNotIn('audit_status', ['closed', 'rejected'])
            ->exists();
    }

    /**
     * Retorna un conteo pending activo del usuario para el centro de costo (si existe)
     */
    public function findActivePending(int $costCenterId, int $userId): ?InventoryCount
    {
        return InventoryCount::where('cost_center_id', $costCenterId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->latest()
            ->first();
    }

    /**
     * Cuenta intentos realizados para un centro de costo en el día actual
     */
    public function countTodayAttemptsForCenter(int $costCenterId): int
    {
        return InventoryCount::where('cost_center_id', $costCenterId)
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Guarda o actualiza líneas de detalle (soporte para draft y guardado definitivo)
     */
    public function upsertDetails(int $countId, array $lines, bool $isDraft = true): void
    {
        foreach ($lines as $line) {
            InventoryCountDetail::updateOrCreate(
                [
                    'inventory_count_id' => $countId,
                    'product_id'         => $line['product_id'],
                ],
                [
                    'system_stock'        => $line['system_stock'],
                    'physical_quantity'   => $line['physical_quantity'] ?? null,
                    'observation_type_id' => $line['observation_type_id'] ?? null,
                    'is_draft'            => $isDraft ? 1 : 0,
                ]
            );
        }
    }

    /**
     * Elimina los detalles de un conteo (usado en re-conteo por rechazo)
     */
    public function deleteDetails(int $countId): void
    {
        InventoryCountDetail::where('inventory_count_id', $countId)->delete();
    }

    /**
     * Registra un intento de conteo con trazabilidad
     */
    public function createAttempt(array $data): InventoryCountAttempt
    {
        return InventoryCountAttempt::create($data);
    }

    /**
     * Productos del centro de costo con su stock actual (para cargar en Livewire)
     */
    public function getProductsForCenter(int $costCenterId)
    {
        return DB::table('cost_center_inventories as cci')
            ->join('product_sku as ps', 'cci.product_sku_id', '=', 'ps.id')
            ->join('products as p', 'ps.product_id', '=', 'p.id')
            ->where('cci.cost_center_id', $costCenterId)
            ->where('cci.qty', '>', 0)
            ->groupBy(
                'p.id',
                'p.product_name',
                'p.thumbnail_image_source'
            )
            ->select(
                'p.id as product_id',
                'p.product_name',
                'p.thumbnail_image_source',
                DB::raw('SUM(cci.qty) as current_stock')
            )
            ->orderBy('p.id')
            ->get()
            ->map(fn($row) => (array) $row);
    }

    public function getActiveObservationTypes()
    {
        return ObservationType::active()->orderBy('name')->get(['name', 'id']);
    }
}
