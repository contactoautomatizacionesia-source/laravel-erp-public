<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GiftCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //dd($this);
        $skus = [];
        if($this->type == 'gift_card')
        {
            foreach($this->addGiftCard as $giftCard)
            {
                $skus[] = [
                    "id" => $giftCard->id,
                    "user_id" => $this->created_by,
                    "product_id" => $this->id,
                    "product_sku_id" => $giftCard->id,
                    "product_stock" => 0,
                    "purchase_price" => 0,
                    "selling_price" => $giftCard->gift_selling_price,
                    "status" => $giftCard->status,
                    "in_app_purchase" => $giftCard->in_app_purchase,
                    "type" => "gift_card",
                    "created_at" => $giftCard->created_at,
                    "updated_at" => $giftCard->updated_at,
                    "product_variations" => [

                    ]
                ];
            }
        }else{
            $skus[] = [
                    "id" => $this->id,
                    "user_id" => $this->created_by,
                    "product_id" => $this->id,
                    "product_sku_id" => $this->id,
                    "product_stock" => 0,
                    "purchase_price" => 0,
                    "selling_price" => $this->selling_price,
                    "status" => $this->status,
                    "in_app_purchase" => $this->in_app_purchase,
                    "type" => "reedem_card",
                    "created_at" => $this->created_at,
                    "updated_at" => $this->updated_at,
                    "product_variations" => [

                    ]
            ];
        }
        return [
            "id" => 2,
            "name" => $this->name,
            "sku" => $this->sku,
            "selling_price" => $this->selling_price,
            "thumbnail_image" => $this->thumbnail_image,
            "discount" => $this->discount,
            "discount_type" => $this->discount_type,
            "start_date" => $this->start_date,
            "end_date" => $this->end_date,
            "description" => $this->description,
            "status" => $this->status,
            "avg_rating" => $this->avg_rating,
            "created_by" => $this->created_by,
            "updated_by" => $this->updated_by,
            "shipping_id" => $this->shipping_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "type" => empty($this->type) ? 'reedem_card':'',
            "skus" => $skus,
        ];
    }
}
