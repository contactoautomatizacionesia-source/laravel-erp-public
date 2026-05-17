<?php

namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateReferralUser extends Model
{
    protected $fillable = [
        'user_id',
        'affiliate_link_id',
        'validity_start_date',
        'reffered_by'
    ];

    public function affiliateLink()
    {
        return $this->belongsTo(AffiliateLink::class,'affiliate_link_id');
    }
}
