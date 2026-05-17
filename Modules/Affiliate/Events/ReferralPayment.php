<?php

namespace Modules\Affiliate\Events;

use Illuminate\Queue\SerializesModels;

class ReferralPayment
{
    use SerializesModels;

    public $user_id ,$item_id,$item_price;

    public function __construct($user_id,$item_id,$itemPrice)
    {
        $this->user_id = $user_id;
        $this->item_id = $item_id;
        $this->item_price = $itemPrice;
    }

    public function broadcastOn()
    {
        return [];
    }
}
