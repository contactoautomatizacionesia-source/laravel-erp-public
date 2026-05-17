@extends('frontend.default.layouts.app')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('frontend/default/css/page_css/checkout_summary.css'))}}" />
@endsection
@section('title')
    {{ __('defaultTheme.checkout') }} {{__('common.summary')}}
@endsection
@section('content')

    @include('frontend.default.partials._breadcrumb')
    <section class="dashboard_part bg-white padding_top padding_bottom">
        <div class="container">
            <div class="row justify-content-center ">
                <div class="col-xl-8">
                    <div class="delivery_details_wrapper">
                        <div class="delivery_details_top text-center">
                            <h3>{{ __('defaultTheme.thank_you_for_your_purchase') }}!</h3>
                            <p>{{ __('defaultTheme.your_order_number_is') }} {{ $order->order_number }}</p>
                            
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
                        <h4 class="delivery_title"> {{ __('defaultTheme.your_delivery_dates') }} </h4>
                        <div class="delivery_details_box">
                            @foreach ($order->packages as $key => $package)
                                <div class="single_delivery_box">
                                    <div class="delivery_box_left">
                                        @foreach ($package->products as $key => $package_product)
                                            @if ($package_product->type == "gift_card")
                                                <div class="product_img_div">
                                                    <img src="{{showImage(@$package_product->giftCard->thumbnail_image)}}" alt="#">
                                                </div>
                                            @else
                                                <div class="product_img_div">
                                                    @if (@$package_product->seller_product_sku->sku->product->product_type == 1)
                                                        <img src="{{showImage(@$package_product->seller_product_sku->product->thum_img??@$package_product->seller_product_sku->sku->product->thumbnail_image_source)}}" alt="#">
                                                    @else
                                                        <img src="{{showImage((@$package_product->seller_product_sku->sku->variant_image?@$package_product->seller_product_sku->sku->variant_image:@$package_product->seller_product_sku->product->thum_img)??@$package_product->seller_product_sku->product->product->thumbnail_image_source)}}" alt="#">
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <h5>
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
</h5>
                                </div>
                            @endforeach
                            <div class="order_texts">
                                <p>{{ __('defaultTheme.for_more_details_track_your_delivery_status_order') }} <span>{{ __('customer_panel.my_account') }} > {{ __('order.my_order') }}</span></p>
                                <a href="{{ route('frontend.my_purchase_order_detail', encrypt($order->id)) }}" target="_blank" class="btn_1 m-0">view Order</a>
                            </div>
                        </div>

                        <div class="email_confimation">
                            <i class="ti-email"></i>
                            <p>{{ __('defaultTheme.we_have_a_confirmation_email_to') }} {{ $order->customer_email }} {{ __('defaultTheme.with_the_order_details') }}</p>
                        </div>
                        <div class="order_summary">
                            <h4>{{ __('defaultTheme.order_summary') }}</h4>
                            <span>{{ single_price($order->grand_total) }}</span>
                        </div>

                        <div class="continue_shoping text-center">
                            <a class="btn_1 " href="{{ route('frontend.welcome') }}">{{ __('defaultTheme.continue_shopping') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection
