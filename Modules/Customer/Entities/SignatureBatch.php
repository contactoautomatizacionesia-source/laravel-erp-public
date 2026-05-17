<?php

namespace Modules\Customer\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SignatureBatch extends Model
{
    protected $table = 'signature_batches';

    protected $fillable = [
        'user_id',
        'trigger',
        'status',
        'total_docs',
        'signed_docs',
    ];

    // Triggers posibles: qué evento originó este lote de firmas
    const TRIGGER_REGISTRATION  = 'registration';   // Primera afiliación del usuario
    const TRIGGER_DATA_UPDATE   = 'data_update';     // Actualización de datos del usuario
    const TRIGGER_MANUAL        = 'manual';          // Relanzado manualmente desde el panel admin

    // Estados del lote
    const STATUS_PENDING   = 'pending';    // Ningún documento firmado aún
    const STATUS_PARTIAL   = 'partial';    // Al menos uno firmado, pero no todos // NOSONAR
    const STATUS_COMPLETED = 'completed';  // Todos los documentos firmados

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(SignatureDocument::class);
    }

    /**
     * Recalcula y persiste el status del lote consultando la BD.
     * Usar en el callback, después de marcar un documento como firmado.
     * Se hace con COUNT en BD en lugar de contador manual para evitar
     * race conditions si dos callbacks llegan casi simultáneamente.
     */
    public function recalculateStatus(): void
    {
        $signedCount = $this->documents()->where('status', SignatureDocument::STATUS_SIGNED)->count();

        $this->update([
            'signed_docs' => $signedCount,
            'status'      => match (true) {
                $signedCount === 0                 => self::STATUS_PENDING,
                $signedCount < $this->total_docs   => self::STATUS_PARTIAL,
                default                            => self::STATUS_COMPLETED,
            },
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
