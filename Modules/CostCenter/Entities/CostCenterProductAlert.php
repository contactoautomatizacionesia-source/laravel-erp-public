<?php

namespace Modules\CostCenter\Entities;

use Illuminate\Database\Eloquent\Model;

class CostCenterProductAlert extends Model
{
    protected $table = 'cost_center_product_alerts';

    protected $fillable = ['cost_center_id', 'product_id', 'min_stock', 'max_stock'];

    protected $casts = [
        'min_stock' => 'integer',
        'max_stock' => 'integer',
    ];
}
