<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\CashManager\Entities\Traits\HasUuid;
use Modules\GeneralSetting\Entities\Catalogs\CashDiscrepancyType;

class CashDiscrepancy extends Model
{
    use HasUuid;

    protected $table = 'cash_discrepancies';

    protected $fillable = [
        'session_id',
        'discrepancy_type_id',
        'type',           // 'SHORTAGE' | 'SURPLUS' — mantener para compatibilidad con registros existentes
        'amount',
        'justification',
        'notes',
        'authorized_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'session_id');
    }

    public function discrepancyType()
    {
        return $this->belongsTo(CashDiscrepancyType::class, 'discrepancy_type_id');
    }

    public function authorizedBy()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
