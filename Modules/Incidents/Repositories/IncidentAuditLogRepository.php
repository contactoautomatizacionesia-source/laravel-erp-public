<?php

namespace Modules\Incidents\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Incidents\Entities\IncidentAuditLog;

class IncidentAuditLogRepository
{
    /**
     * Inserta una entrada en el log de auditoría.
     * Este repositorio NUNCA expone métodos de update ni delete —
     * la tabla está protegida adicionalmente por triggers MySQL.
     *
     * @param string $incidentId UUID del incident
     * @param array  $data {
     *   actor_label: string|array  String plano o array {"es":"...","en":"..."}
     *   user_id?: int|null,
     *   action: string|array       String plano o array {"es":"...","en":"..."}
     *   previous_status?: string|null,
     *   new_status?: string|null,
     *   metadata?: array|null,
     * }
     */
    public function log(string $incidentId, array $data): IncidentAuditLog
    {
        // Normalizar actor_label y action a JSON si se pasan como array i18n
        $actorLabel = is_array($data['actor_label'])
            ? json_encode($data['actor_label'])
            : $data['actor_label'];

        $action = is_array($data['action'])
            ? json_encode($data['action'])
            : $data['action'];

        // Usar insert directo para evitar que cualquier Observer o boot method
        // intente hacer un UPDATE posterior.
        DB::table('incident_audit_logs')->insert([
            'incident_id'     => $incidentId,
            'actor_label'     => $actorLabel,
            'user_id'         => $data['user_id'] ?? null,
            'action'          => $action,
            'previous_status' => $data['previous_status'] ?? null,
            'new_status'      => $data['new_status'] ?? null,
            'metadata'        => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'created_at'      => now(),
        ]);

        return IncidentAuditLog::where('incident_id', $incidentId)
            ->latest('created_at')
            ->first();
    }
}
