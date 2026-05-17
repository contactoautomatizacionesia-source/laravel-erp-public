<?php

namespace Modules\Affiliate\Entities;


use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    protected $fillable = [
        'affiliate_link',
        'owner_id',
        'visits',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class,'owner_id');
    }

    public function registerUser()
    {
        return $this->hasMany(AffiliateReferralUser::class,'affiliate_link_id');
    }

    public function payment()
    {
        return $this->hasMany(AffiliateReferralPayment::class,'affiliate_link_id');
    }
}
