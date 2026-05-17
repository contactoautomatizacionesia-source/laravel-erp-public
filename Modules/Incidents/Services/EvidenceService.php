<?php

namespace Modules\Incidents\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Incidents\Entities\IncidentEvidence;
use Modules\Incidents\Repositories\IncidentAuditLogRepository;
use Modules\Incidents\Repositories\IncidentRepository;

class EvidenceService
{
    private const UPLOADS_DIR = 'uploads/incidents/';
    public function __construct(
        protected IncidentRepository         $repo,
        protected IncidentAuditLogRepository $auditRepo
    ) {}

    /**
     * Guarda un archivo como evidencia de una novedad.
     *
     * @param string $incidentId UUID
     * @param UploadedFile $file Archivo subido
     * @param string $actorRole 'destination' | 'origin' | 'admin'
     * @param string|null $notes Observación que acompaña la evidencia
     * @param int $userId ID del usuario que sube el archivo
     *
     * @throws \LogicException
     */
    public function upload(
        string $incidentId,
        UploadedFile $file,
        string $actorRole,
        ?string $notes,
        int $userId
    ): IncidentEvidence {
        $incident = $this->repo->findById($incidentId);

        if (! $incident->isOpen()) {
            throw new \LogicException('No se pueden adjuntar evidencias a una novedad cerrada o anulada.');
        }

        $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();

        if (! file_exists(asset_path(self::UPLOADS_DIR . $incidentId))) {
            mkdir(asset_path(self::UPLOADS_DIR . $incidentId), 0777, true);
        }

        // Capturar metadata antes de mover — después el temporal ya no existe
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getMimeType();
        $sizeBytes    = $file->getSize();

        $file->move(asset_path(self::UPLOADS_DIR . $incidentId), $fileName);

        $filePath = self::UPLOADS_DIR . $incidentId . '/' . $fileName;

        $evidence = IncidentEvidence::create([
            'incident_id'    => $incidentId,
            'uploaded_by'    => $userId,
            'actor_role'     => $actorRole,
            'file_url'       => $filePath,
            'file_name'      => $originalName,
            'file_mime_type' => $mimeType,
            'file_size_bytes'=> $sizeBytes,
            'notes'          => $notes,
            'created_at'     => now(),
        ]);

        $this->auditRepo->log($incidentId, [
            'actor_label' => [
                'es' => trans("incidents::messages.role_{$actorRole}", [], 'es'),
                'en' => trans("incidents::messages.role_{$actorRole}", [], 'en'),
            ],
            'user_id'     => $userId,
            'action'      => [
                'es' => 'Evidencia adjuntada.',
                'en' => 'Evidence attached.',
            ],
            'metadata'    => [
                'file_name'  => $originalName,
                'actor_role' => $actorRole,
            ],
        ]);

        return $evidence;
    }
}
