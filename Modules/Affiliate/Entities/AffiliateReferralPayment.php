<?php

namespace Modules\Affiliate\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Seller\Entities\SellerProduct;

class AffiliateReferralPayment extends Model
{
    protected $fillable = [
        'payment_to',
        'amount',
        'affiliate_link_id',
        'payment_from',
        'item_id',
        'date',
        'status',
        'type',
        'order_id'
    ];

    public function item()
    {
        return $this->belongsTo(SellerProduct::class,'item_id');
    }

    public function incomeFrom()
    {
        return $this->belongsTo(User::class,'payment_from');
    }
    public function user(){
        return $this->belongsTo(User::class,'payment_to');
    }
}
