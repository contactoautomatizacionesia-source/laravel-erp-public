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

class SendStatementReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 120;

    public function handle(IncidentAuditLogRepository $auditRepo): void
    {
        $setting = IncidentSetting::getInstance();

        if (! $setting->send_deadline_reminder) {
            return;
        }

        $threshold = now()->addHours($setting->reminder_hours_before);

        $pending = Incident::where('status', 'awaiting_statement')
            ->where('statement_reminder_sent', false)
            ->where('statement_expires_at', '<=', $threshold)
            ->get();

        foreach ($pending as $incident) {
            try {
                // TODO: Integrar con el servicio de notificaciones del CRM // NOSONAR
                // (correo + notificación interna al asesor origen)
                // NotificationService::send($incident->originUser, 'incident.deadline_reminder', $incident);

                $incident->update(['statement_reminder_sent' => true]);

                $auditRepo->log($incident->id, [
                    'actor_label' => ['es' => 'Sistema (recordatorio)', 'en' => 'System (reminder)'],
                    'user_id'     => null,
                    'action'      => [
                        'es' => 'Recordatorio de plazo enviado al asesor de origen.',
                        'en' => 'Deadline reminder sent to origin advisor.',
                    ],
                    'metadata'    => ['expires_at' => $incident->statement_expires_at],
                ]);
            } catch (\Throwable $e) {
                Log::error('[Incidents] Error al enviar recordatorio: ' . $e->getMessage(), [
                    'incident_id' => $incident->id,
                ]);
            }
        }

        Log::info('[Incidents] SendStatementReminderJob: procesados ' . $pending->count() . ' recordatorios.');
    }
}
