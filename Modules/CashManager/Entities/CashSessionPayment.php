<?php

namespace Modules\CashManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\CashManager\Entities\Traits\HasUuid;
use Modules\GeneralSetting\Entities\Catalogs\PaymentForm;

class CashSessionPayment extends Model
{
    use HasUuid;

    protected $table = 'cash_session_payments';

    protected $fillable = [
        'session_id',
        'payment_form_id',
        'total_amount',
        'transaction_count',
        'reference_data',
    ];

    protected $casts = [
        'total_amount'      => 'decimal:2',
        'transaction_count' => 'integer',
        'reference_data'    => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'session_id');
    }

    public function paymentForm()
    {
        return $this->belongsTo(PaymentForm::class, 'payment_form_id');
    }
}
