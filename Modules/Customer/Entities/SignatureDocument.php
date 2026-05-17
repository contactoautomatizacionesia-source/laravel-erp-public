<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

class SignatureDocument extends Model
{
    protected $table = 'signature_documents';

    protected $fillable = [
        'signature_batch_id',
        'company_name',
        'contract_type',
        'original_filename',
        'signed_filename',
        'protecdata_process_id',
        'status',
        'signed_at',
        'pdf_local_path',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    // Estados del documento individual
    const STATUS_PENDING  = 'pending';   // Enviado a ProtecData, esperando firma
    const STATUS_SIGNED   = 'signed';    // Firmado — callback recibido con estado=True
    const STATUS_REJECTED = 'rejected';  // Proceso rechazado o expirado

    public function batch()
    {
        return $this->belongsTo(SignatureBatch::class, 'signature_batch_id');
    }

    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
