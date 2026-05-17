<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('common.document') }}</title>
    <link rel="stylesheet" href="{{asset(asset_path('modules/ordermanage/css/sale_print.css'))}}" />
    @php
    $themeColor = Modules\Appearance\Entities\ThemeColor::where('status',1)->first();
    $contactDetails = $order->address ?: $order->guest_info;
    $paymentReference = filled(optional($order->order_payment)->txn_id) ? $order->order_payment->txn_id : '-';
    $paymentMethod = filled(optional($order->method)->slug) ? $order->GatewayName : '-';
    $costCenterLabel = optional($order->costCenter)->name ?: '-';
    @endphp
    <style>
        :root{
            --base_color : {{ $themeColor->base_color }};
        }
    </style>
</head>
<body>
    <div class="invoice_wrapper ign-sales-print">
        <!-- invoice print part here -->
        <div class="invoice_print ">
            <div class="container">
                <div class="invoice_part_iner">
                    <table class="table border_bottom ">
                        <thead>
                            <tr>
                                <th>
                                    <div class="logo_div">
                                        <img src="{{showImage(app('general_setting')->logo)}}" alt="">
                                    </div>
                                </th>
                                <th class="text_center invoice_info">
                                    <h4>{{__('common.order')}}</h4>
                                    <h4>{{getNumberTranslate($order->order_number)}}</h4>
                                    <h4>{{ dateConvert(@$order->created_at) }}</h4>
                                </th>
                                <th class="virtical_middle text_right invoice_info">
                                    <h4 class="text_uppercase">{{app('general_setting')->company_name}}</h4>
                                    <h4>{{getNumberTranslate(app('general_setting')->phone)}}</h4>
                                    <h4>{{app('general_setting')->email}}</h4>
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <!-- middle content  -->
                    <table class="table ">
                        <thead><th></th></thead>
                        <tbody>
                            <tr>
                                <td>
                                   <!-- single table  -->
                                   <table class="mb_20 border_table">
                                    <thead><th></th></thead>
                                       <tbody>
                                           <tr>
                                               <td>
                                                   <h5 class="font_18 mb-0" >{{ __('shipping.billing_info') }}</h5>
                                               </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.name') }}</span>
                                                           
                                                        </span>
                                                        {{ optional($contactDetails)->billing_name ?? '-' }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.email') }}</span>
                                                           
                                                        </span>
                                                        {{ optional($contactDetails)->billing_email ?? '-' }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.phone') }}</span>
                                                           
                                                        </span>
                                                        {{ getNumberTranslate(optional($contactDetails)->billing_phone ?? '-') }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.address') }}</span>
                                                           
                                                        </span>
                                                        {{ optional($contactDetails)->billing_address ?? '-' }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.state') }}</span>
                                                           
                                                        </span>
                                                        {{ optional(optional($contactDetails)->getBillingCity)->name ?? '-' }},
                                                        {{ optional(optional($contactDetails)->getBillingState)->name ?? '-' }},
                                                        {{ optional(optional($contactDetails)->getBillingCountry)->name ?? '-' }}
                                                    </p>
                                                    </p>
                                                </td>
                                           </tr>
                                        
                                       </tbody>
                                   </table>
                                   <!--/ single table  -->
                                </td>
                                <td>
                                    <!-- single table  -->
                                   <table class="border_table">
                                    <thead><th></th></thead>
                                       <tbody>
                                           <tr>
                                               <td>
                                                   <h5 class="font_18 mb-0" >{{ __('shipping.shipping_info') }}</h5>
                                               </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.name') }}</span>
                                                           
                                                        </span>
                                                        {{ optional($contactDetails)->shipping_name ?? '-' }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.email') }}</span>
                                                           
                                                        </span>
                                                        {{ optional($contactDetails)->shipping_email ?? '-' }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.phone') }}</span>
                                                           
                                                        </span>
                                                        {{ getNumberTranslate(optional($contactDetails)->shipping_phone ?? '-') }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.address') }}</span>
                                                           
                                                        </span>
                                                        {{ optional($contactDetails)->shipping_address ?? '-' }}
                                                    </p>
                                                </td>
                                           </tr>
                                           <tr>
                                                <td>
                                                    <p class="line_grid_2" >
                                                        <span>
                                                            <span>{{ __('common.state') }}</span>
                                                           
                                                        </span>
                                                        {{ optional(optional($contactDetails)->getShippingCity)->name ?? '-' }},
                                                        {{ optional(optional($contactDetails)->getShippingState)->name ?? '-' }},
                                                        {{ optional(optional($contactDetails)->getShippingCountry)->name ?? '-' }}
                                                    </p>
                                                </td>
                                           </tr>

                                       </tbody>
                                   </table>
                                   <!--/ single table  -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- invoice print part end -->
        <h3 class="center title_text">{{__('order.ordered_products')}}</h3>
        @php
            $order_total_points = (float) ($order->total_points ?? 0);

            if ($order_total_points === 0) {
                foreach ($order->packages as $order_package_item) {
                    foreach ($order_package_item->products as $order_product_item) {
                        $order_total_points += (float) ($order_product_item->total_club_point ?? 0);
                    }
                }
            }
        @endphp
        @foreach ($order->packages as $key => $order_package)
            <table class="table">
                <thead><th></th></thead>
                <tbody>
                    <tr>
                        <td>
                            <p class="line_grid_2">
                                <span>
                                    <span>{{ __('common.package') }}</span>
                                    
                                </span>
                                {{ getNumberTranslate($order_package->package_code) }}
                            </p>
                        </td>
                        @if(isModuleActive('MultiVendor'))
                        <td>
                            <p class="line_grid_auto grid_end">
                                <span>
                                    <span>{{ __('shipping.shop_name') }}</span>
                                    
                                </span>
                                @if(@$order_package->seller->role->type == 'seller')
                                    {{ (@$order_package->seller->SellerAccount->seller_shop_display_name) ? @$order_package->seller->SellerAccount->seller_shop_display_name : @$order_package->seller->first_name }}
                                @else
                                    {{ app('general_setting')->company_name }}
                                @endif
                            </p>
                        </td>
                        @endif
                    </tr>
                    <tr>
                        @if (file_exists(base_path().'/Modules/GST/') && (app('gst_config')['enable_gst'] == "gst" || app('gst_config')['enable_gst'] == "flat_tax"))
                            <td>
                                @foreach ($order_package->gst_taxes as $key => $gst_tax)
                                    <p class="line_grid_2 ">
                                        <span>
                                            <span>{{ $gst_tax->gst->name }}</span>
                                            
                                        </span>
                                        {{ single_price($gst_tax->amount) }}
                                    </p>
                                @endforeach
                            </td>
                        @endif
                        
                    </tr>
                    <tr>
                        <td>
                            <p class="line_grid_2">
                                <span>
                                    <span>{{ __('common.cost_center') }}</span>
                                    
                                </span>
                                {{ $costCenterLabel }}
                            </p>
                        </td>
                        <td>
                            <p class="line_grid_auto grid_end">
                                <span>
                                    <span>{{ __('shipping.shipping_method') }}</span>
                                    
                                </span>
                                {{ $order_package->shipping->method_name }}
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="table border_table mb_20" >
                <tr class="border_bottom" >
                    <th scope="col" class="text_left">{{ __('common.name') }}</th>
                    <th scope="col" class="text_left">{{ __('common.details') }}</th>
                    <th scope="col" class="text-right">{{ __('common.price') }}</th>
                    <th scope="col" class="text-right">{{ __('common.points') }}</th>
                    <th scope="col" class="text-right">{{ __('common.total') }}</th>
                </tr>
                @foreach ($order_package->products as $key => $package_product)
                    <tr>
                        <td>
                            @if ($package_product->type == "gift_card")
                                {{ @$package_product->giftCard->name }}
                            @else
                                {{ @$package_product->seller_product_sku->sku->product->product_name }}
                            @endif
                        </td>
                        @if ($package_product->type == "gift_card")
                            <td>{{__('common.qty')}}: {{ getNumberTranslate($package_product->qty) }}</td>
                        @else
                            @if (@$package_product->seller_product_sku->sku->product->product_type == 2)
                                <td>
                                    {{__('common.qty')}}: {{getNumberTranslate( $package_product->qty) }}
                                    <br>
                                    @php
                                        $countCombinatiion = count(@$package_product->seller_product_sku->product_variations);
                                    @endphp
                                    @foreach (@$package_product->seller_product_sku->product_variations as $key => $combination)
                                        @if ($combination->attribute->id == 1)
                                            <div class="box_grid ">
                                                <span>{{ $combination->attribute->name }}:</span><span class='box' style="background-color:{{ $combination->attribute_value->value }}"></span>
                                            </div>
                                        @else
                                            {{ $combination->attribute->name }}:
                                            {{ $combination->attribute_value->value }}
                                        @endif
                                        @if (getNumberTranslate($countCombinatiion > $key + 1))
                                            <br>
                                        @endif
                                    @endforeach
                                </td>
                            @else
                                <td>{{__('common.qty')}}: {{ getNumberTranslate($package_product->qty) }}</td>
                            @endif
                        @endif

                        <td class="text-right">{{ single_price($package_product->price) }}</td>
                        <td class="text-right">{{ ($package_product->total_club_point ?? 0) > 0 ? getNumberTranslate($package_product->total_club_point) : '' }}</td>
                        <td class="text-right">{{ single_price($package_product->price * $package_product->qty) }}</td>
                    </tr>
                @endforeach
                @php
                $minRows = 3;
                $currentRows = count($order_package->products);
                $emptyRows = $minRows - $currentRows;
                @endphp

                @for($i = 0; $i < $emptyRows; $i++)
                <tr>
                    <td style="height:25px;">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </table>
        @endforeach

        <div>
            <table class="table ">
                <thead><th></th></thead>
                <tbody>
                    <tr>
                        <td style="width: 50%">
                            <!-- single table  -->
                            <table class="border_table">
                                <thead><th></th></thead>
                                <tbody>
                                    <tr>
                                        <td>
                                                            <h5 class="font_18 mb-0" >{{ __('defaultTheme.payment_info') }}</h5>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="line_grid" >
                                                <span>
                                                    <span>{{ __('order.is_paid') }}</span>
                                                    
                                                </span>
                                                {{ $order->is_paid == 1 ? __('common.yes') : __('common.no') }}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="line_grid" >
                                                <span>
                                                    <span>{{ __('common.payment_method') }}</span>
                                                    
                                                </span>
                                                {{ $paymentMethod }}
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p class="line_grid" >
                                                <span>
                                                    <span>{{ __('common.payment_reference') }}</span>
                                                    
                                                </span>
                                                {{ $paymentReference }}
                                            </p>
                                        </td>
                                    </tr>
                                    
                                    
                                    

                                </tbody>
                            </table>
                            <!--/ single table  -->
                        </td>
                        <td style="width: 50%">
                            <table>
                                <thead><th></th></thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <!-- single table  -->
                                            <table class="mb_20 border_table">
                                                <thead><th></th></thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <h5 class="font_18 mb-0" >{{ __('shipping.order_info') }}</h5>
                                                        </td>
                                                    </tr>
                                                    @if($order->customer_id == null)
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{ __('common.secret_id') }}</span>
                                                                    
                                                                </span>
                                                                {{$order->guest_info->guest_id}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{ __('common.subtotal') }}</span>
                                                                    
                                                                </span>
                                                                {{single_price($order->sub_total)}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{ __('common.discount') }}</span>
                                                                    
                                                                </span>
                                                                - {{single_price($order->discount_total)}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    @if($order->coupon)
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{__('common.coupon')}} {{__('common.discount')}}</span>
                                                                    
                                                                </span>
                                                                - {{single_price($order->coupon->discount_amount)}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{ __('common.shipping_charge') }}</span>
                                                                    
                                                                </span>
                                                                {{single_price($order->shipping_total)}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{ __('common.tax') }}</span>
                                                                    
                                                                </span>
                                                                {{single_price($order->tax_amount)}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    @if (file_exists(base_path().'/Modules/GST/') && (app('gst_config')['enable_gst'] == "gst" || app('gst_config')['enable_gst'] == "flat_tax"))
                                                        <tr>
                                                            <td>
                                                                <p class="line_grid" >
                                                                    <span>
                                                                        <span>{{ __('gst.total_gst') }}</span>
                                                                        
                                                                    </span>
                                                                    {{single_price($order->TotalGstAmount)}}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{ __('common.grand_total') }}</span>
                                                                    
                                                                </span>
                                                                {{single_price($order->grand_total)}}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <p class="line_grid" >
                                                                <span>
                                                                    <span>{{ __('common.totals_points') }}</span>
                                                                    
                                                                </span>
                                                                {{ $order_total_points > 0 ? getNumberTranslate($order_total_points) : '' }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!--/ single table  -->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <script src="{{asset(asset_path('backend/js/jquery.min.js'))}}"></script>
    <script type="text/javascript">
        (function($){
            "use strict";
            $(document).ready(function() {
                window.print();
            });
        })(jQuery);
    </script>
</body>
</html>
