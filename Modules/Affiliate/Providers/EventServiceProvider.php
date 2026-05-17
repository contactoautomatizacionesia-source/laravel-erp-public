<?php

namespace Modules\Affiliate\Providers;


use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Affiliate\Events\CheckAffiliateLink;
use Modules\Affiliate\Events\ReferralPayment;
use Modules\Affiliate\Listeners\CheckAffiliateLinkListener;
use Modules\Affiliate\Listeners\ReferralPaymentListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        CheckAffiliateLink::class =>[
            CheckAffiliateLinkListener::class,
        ],
        ReferralPayment::class => [
            ReferralPaymentListener::class
        ]

    ];

}
