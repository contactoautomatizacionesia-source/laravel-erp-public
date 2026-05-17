<?php

namespace Modules\InventoryExit\Exceptions;

use RuntimeException;

class InsufficientLotStockException extends RuntimeException
{
    public function __construct(string $lotNumber, float $available, float $requested)
    {
        parent::__construct(
            __('inventoryexit::messages.insufficient_lot_stock', [
                'lot'       => $lotNumber,
                'available' => $available,
                'requested' => $requested,
            ])
        );
    }
}
