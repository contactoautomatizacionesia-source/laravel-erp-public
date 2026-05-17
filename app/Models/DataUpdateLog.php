<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DataUpdateLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'payload_before',
        'payload_after',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'payload_before' => 'array',
        'payload_after' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
