<?php

namespace App\Http\Resources\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {



        $product = null;
        if (!empty($this->product)) {
            $prdct = $this->product;
            $productVariantDetails = [];
            if (isset($prdct->variantDetails)) {
                $productVariantDetails = $prdct->variantDetails;
            }


            $prdctFlashDeal = null;
            if ($prdct->flashDeal) {
                $prdctFlashDeal = $prdct->flashDeal;
            }

            $prdctProduct = null;
            if(!empty($prdct->product)){
                $prdctP = $prdct->product;
                $jsonPrdctPTransProName = json_decode($prdctP->translateProductName, true);

                $prdctProduct = [
                    "id" => (int)$prdctP->id,
                    "product_name" => (string)$prdctP->product_name,
                    "product_type" => (int)$prdctP->product_type,
                    "variant_sku_prefix" => (string)$prdctP->variant_sku_prefix,
                    "unit_type_id" => (int)$prdctP->unit_type_id,
                    "brand_id" => (int)$prdctP->brand_id,
                    "thumbnail_image_source" => (string)$prdctP->thumbnail_image_source,
                    "media_ids" => (string)$prdctP->media_ids,
                    "barcode_type" => (string)$prdctP->barcode_type,
                    "model_number" => (string)$prdctP->model_number,
                    "shipping_type" => (int)$prdctP->shipping_type,
                    "shipping_cost" => (float)$prdctP->shipping_cost,
                    "discount_type" => (int)$prdctP->discount_type,
                    "discount" => (float)$prdctP->discount,
                    "tax_type" => (int)$prdctP->tax_type,
                    "gst_group_id" => (int)$prdctP->gst_group_id,
                    "tax_id" => (int)$prdctP->tax_id,
                    "tax" => (float)$prdctP->tax,
                    "pdf" => (string)$prdctP->pdf,
                    "video_provider" => (string)$prdctP->video_provider,
                    "video_link" => (string)$prdctP->video_link,
                    "description" => (string)$prdctP->description,
                    "specification" => (string)$prdctP->specification,
                    "minimum_order_qty" => (int)$prdctP->minimum_order_qty,
                    "max_order_qty" => (int)$prdctP->max_order_qty,
                    "meta_title" => (string)$prdctP->meta_title,
                    "meta_description" => (string)$prdctP->meta_description,
                    "meta_image" => (string)$prdctP->meta_image,
                    "is_physical" => (int)$prdctP->is_physical,
                    "is_approved" => (int)$prdctP->is_approved,
                    "status" => (int)$prdctP->status,
                    "display_in_details" => (int)$prdctP->display_in_details,
                    "requested_by" => (int)$prdctP->requested_by,
                    "created_by" => (int)$prdctP->requested_by,
                    "slug" => (string)$prdctP->slug,
                    "stock_manage" => (string)$prdctP->stock_manage,
                    "subtitle_1" => (string)$prdctP->subtitle_1,
                    "subtitle_2" => (string)$prdctP->subtitle_2,
                    "updated_by" => (int)$prdctP->updated_by,
                    "created_at" => (string)$prdctP->created_at,
                    "updated_at" => (string)$prdctP->updated_at,

                ];
            }

            $prdctSkus = [];
            if(!empty($prdct->skus)){
                $prdctSkus = $prdct->skus;
            }

            $prdctSkus = [];
            if (isset($prdct->skus)) {
                foreach ($prdct->skus as $allProductsku) {
                    $in_purchase = $allProductsku->product->product->skus->where('id',$allProductsku->product_sku_id)->first();
                    $purchase_code  = '';
                    if(!empty($allProductsku->in_app_purchase))
                    {
                        $purchase_code = $allProductsku->in_app_purchase;
                    }else{
                        $purchase_code = !empty($in_purchase) ? $in_purchase->in_app_purchase:'';
                    }
                    $allPdtsSkuPdctVrans = [];
                    if (isset($allProductsku->product_variations)) {
                        foreach ($allProductsku->product_variations as $productVariation) {
                            $productVariationAttribute = $productVariation->attribute;
                            if(!empty(json_decode($productVariationAttribute->name, true))){
                                foreach (json_decode($productVariationAttribute->name, true) as $pdctVrntAttrNm) {
                                    $pdctVrntAttrName = $pdctVrntAttrNm;
                                }

                            }

                            if(!empty($pdctVrntAttrName)){
                            $allPdtsSkuPdctVrans[] = [
                                    "id" => $productVariation->id,
                                    "product_id" => $productVariation->product_id,
                                    "product_sku_id" => $productVariation->product_sku_id,
                                    "attribute_id" => $productVariation->attribute_id,
                                    "attribute_value_id" => $productVariation->attribute_value_id,
                                    "created_by" => $productVariation->created_by,
                                    "updated_by" => $productVariation->updated_by,
                                    "created_at" => $productVariation->created_at,
                                    "updated_at" => $productVariation->updated_at,
                                    "attribute_value" => $productVariation->attribute_value,
                                    'in_app_purchase' => $purchase_code,
                                    "attribute" => [
                                        "id" => $productVariationAttribute->id,
                                        "name" => $pdctVrntAttrName,
                                        "display_type" => $productVariationAttribute->display_type,
                                        "description" => $productVariationAttribute->description,
                                        "status" => $productVariationAttribute->status,
                                        "created_by" => $productVariationAttribute->created_by,
                                        "updated_by" => $productVariationAttribute->updated_by,
                                        "created_at" => $productVariationAttribute->created_at,
                                        "updated_at" => $productVariationAttribute->updated_at,
                                    ],
                                ];
                            }

                        }
                    }



                    $prdctSkus[] = [
                        "id" => $allProductsku->id,
                        "user_id" => $allProductsku->user_id,
                        "product_id" => $allProductsku->product_id,
                        "product_sku_id" => $allProductsku->product_sku_id,
                        "product_stock" => $allProductsku->product_stock,
                        "purchase_price" => $allProductsku->purchase_price,
                        "selling_price" => $allProductsku->selling_price,
                        "status" => $allProductsku->status,
                        'in_app_purchase' => $purchase_code,
                        "created_at" => $allProductsku->created_at,
                        "updated_at" => $allProductsku->updated_at,
                        "product_variations" => $allPdtsSkuPdctVrans,
                    ];
                }
            }

            $prdctReviews = [];
            if(isset($prdct->reviews)){
                $prdctReviews = $prdct->reviews;
            }

            $product = [
                "id" => (int)$prdct->id,
                "user_id" => (int)$prdct->user_id,
                "product_id" => (int) $prdct->product_id,
                "tax" => (float)$prdct->tax,
                "tax_type" => (string)$prdct->tax_type,
                "discount" => (float)$prdct->discount,
                "discount_type" => (int)$prdct->discount_type,
                "discount_start_date" => (string)$prdct->discount_start_date,
                "discount_end_date" => (string)$prdct->discount_end_date,
                "product_name" => (string)$prdct->product_name,
                "slug" => (string)$prdct->slug,
                "thum_img" => (string)$prdct->thum_img,
                "status" => (int)$prdct->status,
                "stock_manage" => (int)$prdct->stock_manage,
                "is_approved" => (int)$prdct->is_approved,
                "min_sell_price" => (float)$prdct->min_sell_price,
                "max_sell_price" => (float)$prdct->max_sell_price,
                "total_sale" => (int)$prdct->total_sale,
                "avg_rating" => (float)$prdct->avg_rating,
                "recent_view" => (string)$prdct->recent_view,
                "subtitle_1" => (string)$prdct->subtitle_1,
                "subtitle_2" => (string)$prdct->subtitle_2,
                "created_at" => (string)$prdct->created_at,
                "updated_at" => (string)$prdct->updated_at,
                "variantDetails" => $productVariantDetails,
                "MaxSellingPrice" => (float)$prdct->MaxSellingPrice,
                "hasDeal" =>  $prdct->hasDeal,
                "rating" => (int)$prdct->rating,
                "hasDiscount" => (string)$prdct->hasDiscount,
                "ProductType" => (string)$prdct->ProductType,

                "flash_deal" => $prdctFlashDeal,
                "product" => $prdctProduct,
                "skus" => $prdctSkus,
                "reviews" => $prdctReviews,
            ];
        }


        return [
                "id" => (int)$this->id,
                "user_id" => (int)$this->user_id,
                "seller_id" => (int)$this->seller_id,
                "type" => (string)$this->type,
                "seller_product_id" => (int)$this->seller_product_id,
                "created_at" => (string)$this->created_at,
                "updated_at" => (string)$this->updated_at,
                "user" => $this->user,
                "seller" => $this->seller,
                "giftcard" => $this->giftcard,
                "product" => $product,
            ];



    }
}
