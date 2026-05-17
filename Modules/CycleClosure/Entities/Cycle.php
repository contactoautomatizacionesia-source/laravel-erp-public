<?php

namespace Modules\CycleClosure\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Cycle extends Model
{
    protected $fillable = [
        'period_label',
        'period_start',
        'period_end',
        'executor_id',
        'co_approver_id',
        'status',
        'pipeline_detail',
        'executed_at',
        'executor_approved_at',
        'approved_at',
        'total_sales',
        'act_path',
    ];

    protected $casts = [
        'pipeline_detail'      => 'array',
        'period_start'         => 'date',
        'period_end'           => 'date',
        'executed_at'          => 'datetime',
        'executor_approved_at' => 'datetime',
        'approved_at'          => 'datetime',
    ];

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_id');
    }

    public function coApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'co_approver_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CycleLog::class);
    }

    public function blockedPeriod(): HasMany
    {
        return $this->hasMany(BlockedPeriod::class);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeNeedsReview($query)
    {
        return $query->where('status', 'needs_review');
    }
}
