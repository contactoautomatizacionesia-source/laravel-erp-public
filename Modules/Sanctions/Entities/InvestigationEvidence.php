<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InvestigationEvidence extends Model
{
    protected $table = 'investigation_evidences';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'investigation_id',
        'uploaded_by_id',
        'file_type',
        'file_url',
        'description',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'date',
    ];

    public function investigation()
    {
        return $this->belongsTo(Investigation::class, 'investigation_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
