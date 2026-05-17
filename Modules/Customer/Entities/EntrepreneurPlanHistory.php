<?php

namespace Modules\Customer\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Plans\Entities\PlanChild;

class EntrepreneurPlanHistory extends Model
{
    protected $table = 'entrepreneur_plan_history';

    // Razones de asignación — definidas en código, no enum en BD
    const REASON_INITIAL  = 'initial_registration';
    const REASON_UPGRADE  = 'upgrade';
    const REASON_DOWNGRADE = 'downgrade';
    const REASON_MANUAL   = 'admin_manual';

    protected $fillable = [
        'user_id',
        'plan_child_id',
        'assigned_by',
        'assigned_reason',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function planChild()
    {
        return $this->belongsTo(PlanChild::class, 'plan_child_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scope: solo el registro activo (ended_at IS NULL)
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }
}
