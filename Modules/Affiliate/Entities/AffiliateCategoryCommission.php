<?php

namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateCategoryCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'calculation_method',
        'amount',
    ];


}
