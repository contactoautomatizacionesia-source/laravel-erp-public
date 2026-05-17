<?php

namespace Modules\Incidents\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IncidentEvidence extends Model
{
    protected $table = 'incident_evidences';

    // Sin updated_at — registros inmutables
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'created_at'      => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function fileSizeForHumans(): string
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_mime_type, 'image/');
    }

    /**
     * URL pública del archivo, respetando el scheme del request actual (http/https).
     */
    public function getFileUrlAttribute(): string
    {
        return asset('public/' . $this->attributes['file_url']);
    }
}
