<?php

namespace Modules\InventoryCount\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\CostCenter\Entities\CostCenter;
use App\Models\User;

class InventoryCount extends Model
{
    protected $table = 'inventory_counts';

    protected $fillable = [
        'count_code',
        'cost_center_id',
        'user_id',
        'status',
        'audit_status',
        'attempt_number',
        'started_at',
        'finished_at',
        'observation',
        'device_info',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'device_info'    => 'array',
        'attempt_number' => 'integer',
        'started_at'     => 'datetime',
        'finished_at'    => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });
        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    // --- Relaciones ---

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(InventoryCountDetail::class, 'inventory_count_id');
    }

    public function attempts()
    {
        return $this->hasMany(InventoryCountAttempt::class, 'inventory_count_id');
    }

    public function audit()
    {
        return $this->hasOne(InventoryCountAudit::class, 'inventory_count_id');
    }

    // --- Helpers ---

    public function isCorrect(): bool
    {
        return $this->status === 'correct';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAuditPending(): bool
    {
        return $this->audit_status === 'pending';
    }
}
