
@extends('frontend.amazy.layouts.app')
<style>
    .cart_thumb_div .summery_pro_content {
        max-width: calc(100% - var(--thum-width)) !important;
    }
    @media (max-width: 767.98px){
        .cart_thumb_div .summery_pro_content{
            padding-left: 20px !important;
        }

    }
    .min_hight_70{
        min-height: 100vh;
    }

</style>
@section('title')
    {{ __('defaultTheme.checkout') }} {{__('common.summary')}}
@endsection

@section('content')
    @php
        $order_total_points = (float) ($order->total_points ?? 0);

        if ($order_total_points === 0) {
            foreach ($order->packages as $order_package) {
                foreach ($order_package->products as $order_product) {
                    $order_total_points += (float) ($order_product->total_club_point ?? 0);
                }
            }
        }
    @endphp
    <div class="amazy_dashboard_area dashboard_bg section_spacing6 min_hight_70 order-summary">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <div class="">
                        <div class="white_box_header d-flex align-items-center gap_20 flex-wrap justify-content-center ">
                            <div class="title text-center">
                                <h3 class="m-0">{{ __('defaultTheme.thank_you_for_your_purchase') }}!</h3>
                                <p>{{ __('defaultTheme.your_order_number_is') }} {{ getNumberTranslate($order->order_number) }}</p>
                                
                                @if($order->order_payment && $order->order_payment->status == 0)
                                    <div class="alert alert-warning mt-3">
                                        <h5><i class="fas fa-clock"></i> {{ __('payment_gatways.payment_pending') }}</h5>
                                        <p class="mb-0">{{ __('payment_gatways.payment_pending_description') }}</p>
                                        <small class="text-muted">{{ __('payment_gatways.payment_pending_note') }}</small>
                                    </div>
                                @elseif($order->order_payment && $order->order_payment->status == 1)
                                    <div class="alert alert-success mt-3">
                                        <h5><i class="fas fa-check-circle"></i> {{ __('payment_gatways.payment_confirmed') }}</h5>
                                        <p class="mb-0">{{ __('payment_gatways.payment_confirmed_description') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="bg-white  p-3 p-md-5 mt-3 cart-shadown rounded-10">
                            @foreach ($order->packages as $key => $package)
                                <div class="card rounded-10 mb-3">
                                    <div class="card-body ">
                                        <div class="d-flex flex-wrap">
                                            <div class="flex-fill">
                                                @foreach ($package->products as $key => $package_product)
                                                    @if ($package_product->type == "gift_card")
                                                        <a href="{{route('frontend.gift-card.show',$package_product->giftCard->sku)}}" class="d-flex align-items-center gap_20 w-100 flex-fill @if(!$loop->last) amazy_bb3 @endif cart_thumb_div">
                                                            <div class="thumb">
                                                                <img src="{{showImage(@$package_product->giftCard->thumbnail_image)}}" alt="{{ textLimit(@$package_product->giftCard->name, 28) }}" title="{{ textLimit(@$package_product->giftCard->name, 28) }}">
                                                            </div>
                                                            <div class="summery_pro_content">
                                                                <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$package_product->giftCard->name, 28) }}</h4>
                                                            </div>
                                                        </a>
                                                    @else
                                                        <a href="{{singleProductURL(@$package_product->seller_product_sku->product->seller->slug, @$package_product->seller_product_sku->product->slug)}}" class="d-flex align-items-center gap_20 w-100 flex-fill @if(!$loop->last) amazy_bb3 @endif cart_thumb_div">
                                                            <div class="thumb">
                                                                
                                                                @if (@$package_product->seller_product_sku->sku->product->product_type == 1)
                                                                    @php
                                                                        $image = !empty($package_product->seller_product_sku->product->thum_img) ? $package_product->seller_product_sku->product->thum_img:$package_product->seller_product_sku->sku->product->thumbnail_image_source;
                                                                    @endphp

                                                                    <img src="{{showImage($image)}}" alt="{{ textLimit(@$package_product->seller_product_sku->product->product_name, 28) }}" title="{{ textLimit(@$package_product->seller_product_sku->product->product_name, 28) }}">
                                                                @else
                                                                    @php
                                                                        $image = !empty($package_product->seller_product_sku->sku->variant_image) ? $package_product->seller_product_sku->sku->variant_image:$package_product->seller_product_sku->product->thum_img;
                                                                    @endphp
                                                                    <img src="{{showImage($image)}}" alt="{{ textLimit(@$package_product->seller_product_sku->product->product_name, 28) }}" title="{{ textLimit(@$package_product->seller_product_sku->product->product_name, 28) }}">
                                                                @endif
                                                            </div>
                                                            <div class="summery_pro_content">
                                                                <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$package_product->seller_product_sku->product->product_name, 28) }}</h4>
                                                                @if($package_product->seller_product_sku->sku->product->product_type == 2)
                                                                    <p class="font_14 f_w_400 m-0">
                                                                    @foreach($package_product->seller_product_sku->product_variations as $key => $combination)
                                                                        @if($combination->attribute->id == 1)
                                                                            {{$combination->attribute->name}}: {{$combination->attribute_value->color->name}}
                                                                        @else
                                                                            {{$combination->attribute->name}}: {{$combination->attribute_value->value}}
                                                                        @endif
                                                                        @if(!$loop->last), @endif
                                                                    @endforeach
                                                                    </p>
                                                                @endif
                                                                <p>
                                                                    {{__('common.points')}}: {{ ($package_product->total_club_point ?? 0) > 0 ? getNumberTranslate($package_product->total_club_point) : '' }}
                                                                </p>
                                                            </div>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>

                                            <h4 class="font_16 f_w_500 m-0 text-capitalize">
                                                @php
                                                    // Procesar shipping_date para traducir
                                                    $shippingDateText = $package->shipping_date;
                                                    
                                                    // Caso específico: "Est arrival date" -> traducir directamente
                                                    if (strpos($shippingDateText, 'Est arrival date:') === 0) {
                                                        $datePart = substr($shippingDateText, strlen('Est arrival date: '));
                                                        echo __('defaultTheme.Est_arrival_date') . ': ' . trim($datePart);
                                                    }
                                                    // Caso con error tipográfico
                                                    elseif (strpos($shippingDateText, 'deafultTheme.') !== false) {
                                                        $cleanText = str_replace('deafultTheme.', 'defaultTheme.', $shippingDateText);
                                                        $parts = explode(': ', $cleanText, 2);
                                                        if (count($parts) == 2) {
                                                            echo __($parts[0]) . ': ' . $parts[1];
                                                        } else {
                                                            echo $cleanText;
                                                        }
                                                    }
                                                    // Caso normal con clave de traducción
                                                    elseif (strpos($shippingDateText, 'defaultTheme.') !== false) {
                                                        $parts = explode(': ', $shippingDateText, 2);
                                                        if (count($parts) == 2) {
                                                            echo __($parts[0]) . ': ' . $parts[1];
                                                        } else {
                                                            echo $shippingDateText;
                                                        }
                                                    }
                                                    // Default: mostrar texto original
                                                    else {
                                                        echo $shippingDateText;
                                                    }
                                                @endphp
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-between align-items-center justify-content-center flex-column g-2 mb_20 flex-wrap">
                                <p>{{ __('defaultTheme.for_more_details_track_your_delivery_status_order') }} <span class="f_w_600">{{ __('customer_panel.my_account') }} > {{ __('order.my_order') }}</span></p>
                                <a href="{{ route('frontend.my_purchase_order_detail', encrypt($order->id)) }}" class="amaz_primary_btn style3 px-4 mt-3 text-nowrap ">{{__('common.view_order')}}</a>
                            </div>
                            <div class="table-responsive mb_10">
                                <table class="table  mb-0">
                                    <thead><th></th></thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center border-0">
                                                <p><i class="ti-email"></i> {{ __('defaultTheme.we_have_a_confirmation_email_to') }} {{ $order->customer_email }} {{ __('defaultTheme.with_the_order_details') }}</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="border gray_color_1 p-3 rounded-10">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center g-2 mb_20  " style="max-width: 600px; margin:auto">
                                    <h4 class="f_w_500 font_25 m-0 ">{{ __('defaultTheme.order_summary') }}</h4>
                                    <span  class="f_w_500 font_20 m-0  secondary_text ">{{ single_price($order->grand_total) }}</span>
                                </div>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center g-2 mb_20  " style="max-width: 600px; margin:auto">
                                    <h4 class="f_w_500 font_25 m-0 ">{{ __('common.totals_points') }}</h4>
                                    <span  class="f_w_500 font_20 m-0  secondary_text ">{{ getNumberTranslate($order_total_points) }}</span>
                                </div>
                            </div>
                            <div class="continue_shoping text-center mt-5">
                                <a class="amaz_primary_btn style3 px-4 text-nowrap" href="{{ route('frontend.welcome') }}">{{ __('defaultTheme.continue_shopping') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
