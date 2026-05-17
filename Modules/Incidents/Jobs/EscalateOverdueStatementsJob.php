<?php

namespace Modules\Incidents\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Incidents\Entities\Incident;
use Modules\Incidents\Entities\IncidentSetting;
use Modules\Incidents\Repositories\IncidentAuditLogRepository;

class EscalateOverdueStatementsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 120;

    public function handle(IncidentAuditLogRepository $auditRepo): void
    {
        $setting = IncidentSetting::getInstance();

        if (! $setting->auto_escalate_on_deadline) {
            return;
        }

        $overdue = Incident::where('status', 'awaiting_statement')
            ->where('statement_expires_at', '<=', now())
            ->get();

        foreach ($overdue as $incident) {
            try {
                $incident->update(['status' => 'under_investigation']);

                $auditRepo->log($incident->id, [
                    'actor_label'     => ['es' => 'Sistema (escalado automático)', 'en' => 'System (auto-escalation)'],
                    'user_id'         => null,
                    'action'          => [
                        'es' => 'Plazo de pronunciamiento vencido. Novedad escalada automáticamente al administrador.',
                        'en' => 'Statement deadline expired. Incident automatically escalated to administrator.',
                    ],
                    'previous_status' => 'awaiting_statement',
                    'new_status'      => 'under_investigation',
                    'metadata'        => ['expired_at' => $incident->statement_expires_at],
                ]);
            } catch (\Throwable $e) {
                Log::error('[Incidents] Error al escalar novedad vencida: ' . $e->getMessage(), [
                    'incident_id' => $incident->id,
                ]);
            }
        }

        Log::info('[Incidents] EscalateOverdueStatementsJob: procesados ' . $overdue->count() . ' registros.');
    }
}
