<?php

namespace Modules\CycleClosure\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CycleLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cycle_id',
        'phase',
        'level',
        'message',
        'context',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
