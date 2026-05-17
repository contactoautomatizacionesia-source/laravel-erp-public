<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Seller\Entities\SellerProductSKU;
use Modules\GiftCard\Entities\GiftCard;

class OrderProductDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = [
        'qty' => 'integer',
        'unit_club_point' => 'double',
    ];

    public function getTotalClubPointAttribute(): float
    {
        if ($this->type !== 'product') {
            return 0.0;
        }

        $unitClubPoint = (float) ($this->unit_club_point ?? 0);
        $quantity = (int) ($this->qty ?? 0);

        if ($unitClubPoint <= 0 || $quantity <= 0) {
            return 0.0;
        }

        return $unitClubPoint * $quantity;
    }

    public function package(){
        return $this->belongsTo(OrderPackageDetail::class,'package_id','id');
    }

    public function seller_product_sku()
    {
        return $this->belongsTo(SellerProductSKU::class, 'product_sku_id', 'id');
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class,'product_sku_id','id');
    }
}
