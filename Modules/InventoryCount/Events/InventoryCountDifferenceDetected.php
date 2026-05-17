<?php

namespace Modules\InventoryCount\Events;

use Illuminate\Queue\SerializesModels;
use Modules\InventoryCount\Entities\InventoryCount;

class InventoryCountDifferenceDetected
{
    use SerializesModels;

    public function __construct(
        public readonly InventoryCount $count,
        public readonly int            $userId,
    ) {}
}
