<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CashManager\Entities\Traits\HasUuid;

class CashTransfer extends Model
{
    use HasUuid;

    protected $table = 'cash_transfers';

    protected $fillable = [
        'origin_session_id',
        'destination_box_id',
        'amount',
        'transfer_hash',
        'status',
        'received_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'received_at' => 'datetime',
    ];

    // status: 'IN_TRANSIT' | 'RECEIVED' | 'REJECTED'

    public function originSession()
    {
        return $this->belongsTo(CashSession::class, 'origin_session_id');
    }

    public function destinationBox()
    {
        return $this->belongsTo(CashBox::class, 'destination_box_id');
    }
}
