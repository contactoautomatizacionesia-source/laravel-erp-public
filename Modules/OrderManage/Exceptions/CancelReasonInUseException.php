<?php

namespace Modules\OrderManage\Exceptions;

use RuntimeException;

class CancelReasonInUseException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(__('order.cancel_reason_in_use'));
    }
}
