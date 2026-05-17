<?php

namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliateConfiguration extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];
}
