<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;

class EuiDefense extends Model
{
    protected $table = 'eui_defenses';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'investigation_id',
        'defense_text',
        'document_url',
        'submitted_at',
        'has_evidence',
    ];

    protected $casts = [
        'submitted_at' => 'date',
        'has_evidence' => 'boolean',
    ];

    public function investigation()
    {
        return $this->belongsTo(Investigation::class, 'investigation_id');
    }
}
