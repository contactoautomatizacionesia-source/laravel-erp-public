<?php

namespace Modules\CycleClosure\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedPeriod extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cycle_id',
        'blocked_until',
        'created_at',
    ];

    protected $casts = [
        'blocked_until' => 'date',
        'created_at'    => 'datetime',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    /**
     * Check if a given date is blocked.
     */
    public static function isDateBlocked(string $date): bool
    {
        return static::where('blocked_until', '>', $date)->exists();
    }
}
