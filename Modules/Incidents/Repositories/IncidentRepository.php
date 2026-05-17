<?php

namespace Modules\Incidents\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Incidents\Entities\Incident;

class IncidentRepository
{
    public function getBaseQuery(array $filters = []): Builder
    {
        $query = Incident::with([
            'responsibleBranch',
            'responsibleUser',
        ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['incident_type'])) {
            $query->where('incident_type', $filters['incident_type']);
        }

        if (! empty($filters['responsible_branch_id'])) {
            $query->where('responsible_branch_id', $filters['responsible_branch_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('created_at');
    }

    public function findById(string $id, array $relations = []): Incident
    {
        return Incident::with($relations)->findOrFail($id);
    }

    public function create(array $data): Incident
    {
        return Incident::create($data);
    }

    public function update(Incident $incident, array $data): bool
    {
        return $incident->update($data);
    }

    /**
     * Genera el siguiente código secuencial con formato NOV-XXXX.
     * Usa MAX sobre sequential_code para garantizar unicidad incluso con UUIDs.
     */
    public function generateSequentialCode(): string
    {
        $last = Incident::selectRaw("MAX(CAST(SUBSTRING(sequential_code, 5) AS UNSIGNED)) as max_num")
            ->value('max_num');

        $next = ($last ?? 0) + 1;

        return sprintf('NOV-%04d', $next);
    }

    /**
     * Métricas para las tarjetas del header del índice.
     */
    public function getMetrics(): array
    {
        $rows = Incident::selectRaw("
            status,
            COUNT(*) as total,
            SUM(total_value) as value_sum
        ")
        ->groupBy('status')
        ->get()
        ->keyBy('status');

        $openStatuses = ['pending', 'awaiting_statement', 'under_investigation'];
        $totalPendingValue = Incident::whereIn('status', $openStatuses)->sum('total_value');

        return [
            'pending'              => (int) ($rows['pending']->total ?? 0),
            'awaiting_statement'   => (int) ($rows['awaiting_statement']->total ?? 0),
            'under_investigation'  => (int) ($rows['under_investigation']->total ?? 0),
            'closed'               => (int) ($rows['closed']->total ?? 0),
            'voided'               => (int) ($rows['voided']->total ?? 0),
            'total_pending_value'  => (float) $totalPendingValue,
        ];
    }

    /**
     * Verifica si ya existe una novedad activa para el mismo documento fuente y producto.
     * Cuando se proporciona $sourceItemId, la unicidad se evalúa a nivel de ítem individual
     * (necesario para transferencias con múltiples ítems del mismo producto).
     */
    public function hasActiveForSource(string $sourceType, int $sourceId, int $productId, ?int $sourceItemId = null): bool
    {
        $query = Incident::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('product_id', $productId)
            ->whereNotIn('status', ['closed', 'voided']);

        if ($sourceItemId !== null) {
            $query->where('source_item_id', $sourceItemId);
        }

        return $query->exists();
    }
    
}
