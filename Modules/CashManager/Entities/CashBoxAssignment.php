<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CashManager\Entities\Traits\HasUuid;
use App\Models\User;

class CashBoxAssignment extends Model
{
    use HasUuid;

    protected $table = 'cash_box_assignments';

    protected $fillable = [
        'cash_box_id',
        'user_id',
        'assigned_by_id', // El líder que entregó la caja
        'assigned_at',
        'revoked_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'revoked_at'  => 'datetime',
    ];

    public function box()
    {
        return $this->belongsTo(CashBox::class, 'cash_box_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function sessions()
    {
        return $this->hasMany(CashSession::class, 'assignment_id');
    }
}
