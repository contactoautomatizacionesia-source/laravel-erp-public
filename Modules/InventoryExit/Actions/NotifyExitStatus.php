<?php

namespace Modules\InventoryExit\Actions;

use Modules\InventoryExit\Entities\InventoryExitRequest;
use Modules\InventoryExit\Notifications\ExitRequestedNotification;
use Modules\InventoryExit\Notifications\ExitStatusChangedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotifyExitStatus
{
    /**
     * Notifica a todos los administradores cuando se crea una solicitud de salida.
     */
    public function notifyAdmins(InventoryExitRequest $exitRequest): void
    {
        $admins = User::whereIn('role_id', [1, 2, 7])->get();

        foreach ($admins as $admin) {
            try {
                $admin->notify(new ExitRequestedNotification($exitRequest)); // TODO: Crear plantilla de notificación ExitRequestedNotification (canal DB/mail, textos, variables) cuando el módulo de notificaciones esté listo. //NOSONAR
            } catch (\Throwable $e) {
                Log::error("InventoryExit: fallo al notificar admin {$admin->id} sobre solicitud {$exitRequest->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Notifica al solicitante cuando su solicitud es aprobada o rechazada.
     */
    public function notifyRequester(InventoryExitRequest $exitRequest): void
    {
        try {
            $exitRequest->requestedBy->notify(new ExitStatusChangedNotification($exitRequest)); // TODO: Crear plantilla de notificación ExitStatusChangedNotification (canal DB/mail, textos con estado aprobado/rechazado) cuando el módulo de notificaciones esté listo. //NOSONAR
        } catch (\Throwable $e) {
            Log::error("InventoryExit: fallo al notificar solicitante {$exitRequest->requested_by} sobre solicitud {$exitRequest->id}: " . $e->getMessage());
        }
    }
}
