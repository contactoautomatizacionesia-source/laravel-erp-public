<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class EuiStatusLog extends Model
{
    protected $table = 'eui_status_logs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'eui_id',
        'investigation_id',
        'resolution_id',
        'changed_by_id',
        'previous_status',
        'new_status',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function eui()
    {
        return $this->belongsTo(User::class, 'eui_id');
    }

    public function investigation()
    {
        return $this->belongsTo(Investigation::class, 'investigation_id');
    }

    public function resolution()
    {
        return $this->belongsTo(SanctionResolution::class, 'resolution_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}
