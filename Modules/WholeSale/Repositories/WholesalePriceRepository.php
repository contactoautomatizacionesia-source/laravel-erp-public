<?php

namespace Modules\WholeSale\Repositories;

use Modules\Seller\Entities\SellerProduct;
use Modules\WholeSale\Entities\WholesalePrice;

class WholesalePriceRepository
{
    public function getAllWholesalePrice($id)
    {
        $sellerProductId = SellerProduct::where('product_id', $id)->first();
        return WholesalePrice::where('product_id', $sellerProductId->id)->get();
    }
}
