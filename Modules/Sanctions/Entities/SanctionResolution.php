<?php

namespace Modules\Sanctions\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class SanctionResolution extends Model
{
    use SoftDeletes;

    protected $table = 'sanction_resolutions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'investigation_id',
        'sanction_type_id',
        'action_type_id',
        'resolved_by_id',
        'resolution_text',
        'resolved_at',
        'effect_start_date',
        'effect_end_date',
        'is_appealable',
    ];

    protected $casts = [
        'resolved_at'       => 'date',
        'effect_start_date' => 'date',
        'effect_end_date'   => 'date',
        'is_appealable'     => 'boolean',
    ];

    public function investigation()
    {
        return $this->belongsTo(Investigation::class, 'investigation_id');
    }

    public function sanctionType()
    {
        return $this->belongsTo(CatSanctionType::class, 'sanction_type_id');
    }

    public function actionType()
    {
        return $this->belongsTo(CatActionType::class, 'action_type_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_id');
    }

    public function enforcement()
    {
        return $this->hasOne(SanctionEnforcement::class, 'resolution_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(EuiStatusLog::class, 'resolution_id');
    }
}
