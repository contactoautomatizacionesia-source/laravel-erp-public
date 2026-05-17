<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CashManager\Entities\Traits\HasUuid;

class CashSessionDenomination extends Model
{
    use HasUuid;

    protected $table = 'cash_session_denominations';

    protected $fillable = [
        'session_id',
        'denomination_id',
        'quantity',
        'subtotal'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'subtotal' => 'decimal:2'
    ];

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'session_id');
    }

    public function denomination()
    {
        return $this->belongsTo(CatDenomination::class, 'denomination_id');
    }
}
