<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserApprovalHistory extends Model
{
    // approved
    // rejected
    // pending
    
    protected $fillable = [
        'user_id',
        'from_status',
        'to_status',
        'reason',
        'changed_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
