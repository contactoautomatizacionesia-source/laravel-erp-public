<?php

namespace Modules\Incidents\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\CostCenter\Events\TransferDiscrepancyCreated;
use Modules\Incidents\Listeners\CreateIncidentFromInventoryCount;
use Modules\Incidents\Listeners\CreateIncidentFromTransfer;
use Modules\InventoryCount\Events\InventoryCountDifferenceDetected;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            TransferDiscrepancyCreated::class,
            CreateIncidentFromTransfer::class
        );

        Event::listen(
            InventoryCountDifferenceDetected::class,
            CreateIncidentFromInventoryCount::class
        );
    }
}
