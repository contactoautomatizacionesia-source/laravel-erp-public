<?php

namespace Modules\WholeSale\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Seller\Entities\SellerProduct;

class WholesalePrice extends Model
{
    use HasFactory;
    protected $table = "wholesale_prices";
    public $timestamps=true;
    protected $guarded = ['id'];

    public function product(){
        return $this->belongsTo(SellerProduct::class,'product_id','id');
    }
    protected $appends = ['sell_price'];
    
    public function getSellPriceAttribute(){
        if (app('general_setting')->price_with_vat) {
            return $this->attributes['selling_price'] + ($this->product->tax ?? 0);
        }else{
            return $this->attributes['selling_price'];
        }
    }
}
