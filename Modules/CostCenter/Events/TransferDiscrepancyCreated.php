<?php

namespace Modules\CostCenter\Events;

use Illuminate\Queue\SerializesModels;
use Modules\CostCenter\Entities\CostCenterTransfer;
use Modules\CostCenter\Entities\CostCenterTransferItem;

class TransferDiscrepancyCreated
{
    use SerializesModels;

    public function __construct(
        public readonly CostCenterTransfer     $transfer,
        public readonly CostCenterTransferItem $item,
        public readonly float                  $discrepancyQty,
        public readonly int                    $dispatchedBy,
    ) {}
}
