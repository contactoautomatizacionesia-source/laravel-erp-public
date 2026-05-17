<?php

namespace Modules\CycleClosure\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CycleSetting extends Model
{
    protected $fillable = [
        'period_type',
        'execution_day',
        'executor_user_id',
        'approver_user_id',
        'configured_by',
        'is_active',
        'payload',
    ];

    protected $casts = [
        'payload'   => 'array',
        'is_active' => 'boolean',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executor_user_id');
    }

    public function configurator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'configured_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
