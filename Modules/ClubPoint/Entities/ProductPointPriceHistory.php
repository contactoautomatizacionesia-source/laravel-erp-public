<?php

namespace Modules\ClubPoint\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;

class ProductPointPriceHistory extends Model
{
    protected $table = 'product_point_price_history';

    protected $fillable = [
        'product_id',
        'product_sku',
        'previous_points',
        'new_points',
        'previous_price',
        'new_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
