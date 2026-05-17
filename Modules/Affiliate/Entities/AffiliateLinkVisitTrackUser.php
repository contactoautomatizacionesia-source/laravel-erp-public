<?php

namespace Modules\Affiliate\Entities;

use Illuminate\Database\Eloquent\Model;

class AffiliateLinkVisitTrackUser extends Model
{
    protected $fillable = [
        'affiliate_link_id',
        'ip',
        'agent',
        'date',
    ];
    public function affiliateLink()
    {
        return $this->belongsTo(AffiliateLink::class,'affiliate_link_id');
    }
}
