<?php

namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliateUserWallet extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'paypal_account',
    ];
}
