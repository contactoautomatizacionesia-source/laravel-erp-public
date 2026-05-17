<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{__('order.invoice')}} {{$order->order_number}}</title>

    <style>
        @page { margin: 40px; }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #222;
        }

        .container {
            width: 100%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-info {
            font-size: 11px;
            line-height: 1.4;
            text-align: center;
        }

        .invoice-title {
            text-align: right;
            padding-top: 30px;
        }


        .section-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .info-table td {
            padding: 4px;
            vertical-align: top;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .products-table th {
            text-align: left;
            padding: 10px 5px;
            font-size: 11px;
            background-color: #92b558;
            color:#fff
        }

        .products-table th:not(:last-child){
            border-right: 1px solid #fff;
        }
        .products-table td {
            border-bottom: 1px solid #ddd;
            padding: 6px 4px;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .totals-table td {
            padding: 4px;
            font-size: 11px;
        }

        .totals-table tr:last-child td {
            border-top: 1px solid #92b558;
            font-weight: bold;
            font-size: 13px;
        }

        .small {
            font-size: 10px;
        }

    .info-right {
        width: 50%;
        flex: 0 0 auto;
    }
    .info-right table tr td:last-child{
        font-size: 14px;

    }

        .info-details {
    border: 1px solid #ffffff;
    background-color: #ffffff;
    display: flex;
    padding: 10px;
}

    .info-details>* {
        flex-grow: 1;
        font-size: 18px;
    }

    .info table {
        border-radius: 2px;
        overflow: hidden;
    }

    .info table:not(:last-child) {
        margin-bottom: 14px;
    }

    .info table.bg-dark {
        background-color: #32393D;
    }

    .info table.bg-dark .text-red td {
        color: #FF4B4B;
    }

    .info table.bg-dark td {
        color: #fff;
        border-color: #55595A;
        opacity: 1;
    }

    .info table tr td {
        border: 1px solid #ececec;
        padding: 10px 10px;
    }

    .info table tr:last-child td {
        background-color: #92b558;
        color: #fff;
    }

    .info table tr td:first-child {
        font-weight: bold;
        opacity: 1;
    }

    .invoice-list {
        margin-bottom: 20px;
    }

    .invoice-list thead tr {
        background-color: #92b558;
    }

    .invoice-list thead tr th {
        color: #fff;
        font-size: 14px;
    }

    .invoice-list tr th,
    .invoice-list tr td {
        border-right: 1px solid #DDDEDE;
        padding: 10px 12px;
        text-align: left;
        color: #000;
    }

    .invoice-list tr th:last-child,
    .invoice-list tr td:last-child {
        border-right: none;
    }

    .invoice-list tr th:first-child,
    .invoice-list tr td:first-child {
        text-align: center;
    }

    .invoice-list tr th span,
    .invoice-list tr td span {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
    }

    .invoice-list tbody tr:nth-child(2n+1) {
        background-color: #f8f8f8;
    }

    .invoice-list tbody tr td {
        font-size: 12.6px;
        color: #000;
    }

    .invoice-list tbody tr td:first-child {
        color: #000;
    }

    .invoice-list tbody tr td:nth-child(2) {
        color: #1c1c1c;
    }

    .invoice-list tfoot tr {
        border-top: 1px solid #DDDEDE;
    }

    .invoice-list tfoot tr td:first-child {
        text-align: end;
    }

    table {
    margin: 0;
    padding: 0;
    border-collapse: collapse;
    width: 100%;
    font-family: "Lato", sans-serif;
}

    </style>
</head>
<body style="background-color: white;">
<div class="container">

    {{-- HEADER --}}
    <table class="header-table">
        <thead><th></th></thead>
        <tr>
            <td style="width:30%">
                <img alt="logo" src="{{showImage(app('general_setting')->logo)}}" style="max-height:70px;">
            </td>
            <td style="width:40%" class="company-info">
                <strong>{{$order->brand->full_name}}</strong><br>
                NIT: {{$order->brand->nit}}<br>
                {{app('general_setting')->address}}<br>
                {{app('general_setting')->email}}<br>
                {{app('general_setting')->phone}}<br>
                <span class="small">
                    @php
                        $dianSetting = $order->brand->dianSetting;
                    @endphp

                    {{__('order.resolution')}} {{$dianSetting->resolution_number}}
                    {{__('common.preposition_of_the')}} {{ __('common.date_format', ['day' => $dianSetting->resolution_date?->translatedFormat('d'), 'month' => $dianSetting->resolution_date?->translatedFormat('F'), 'year' => $dianSetting->resolution_date?->translatedFormat('Y')]) }}
                    <br>
                    {{ __('common.range_message', ['fromAttribute' => $dianSetting->invoice_number_from, 'toAttribute' => $dianSetting->invoice_number_to]) }}
                </span>
            </td>
            <td style="width:30%" class="invoice-title">
                <h2 style="font-size: 14px;">{{__('defaultTheme.electronic_invoice')}}</h2>
                <strong>No:</strong> {{$order->order_number}}<br>
                <strong>Fecha:</strong> {{$order->created_at}}<br>

            </td>
        </tr>
    </table>

    {{-- DATOS CLIENTE --}}
    <table class="info-table">
        <thead><th></th></thead>
        <tbody>
        <tr>
            <td style="width:33%">
                <div class="section-title" style="margin-bottom: 10px">{{ __('common.billing_info') }}</div><hr>
                <strong>{{($order->customer_id) ? $order->address->billing_name : $order->guest_info->billing_name}}</strong><br>
                {{($order->customer_id) ? $order->address->billing_address : $order->guest_info->billing_address}}<br>
                {{($order->customer_id) ? @$order->address->getBillingCity->name : @$order->guest_info->getBillingCity->name}},
                {{($order->customer_id) ? @$order->address->getBillingState->name : @$order->guest_info->getBillingState->name}},
                {{($order->customer_id) ? @$order->address->getBillingCountry->name : @$order->guest_info->getBillingCountry->name}}<br>
                {{($order->customer_id) ? $order->address->billing_email : $order->guest_info->billing_email}}<br>
                Tel: {{($order->customer_id) ? $order->address->billing_phone : $order->guest_info->billing_phone}}
            </td>

            <td style="width:33%">
                <div class="section-title" style="margin-bottom: 10px">{{__('defaultTheme.payment_info')}}</div><hr>
                <strong>{{__('common.payment_method')}}: </strong> {{$order->GatewayName}} <br>
                <strong>{{__('common.payment_reference')}}: </strong>@if(@$order->order_payment->txn_id && @$order->order_payment->txn_id != 'none'){{ @$order->order_payment->txn_id }} @else - @endif<br>
            </td>
            <td style="width:33%">
                <img src="{{asset('public/images/qr-demo.png')}}" alt="QR" title="QR" style="width: 150px; height: 150px;">
            </td>
        </tr>
        </tbody>
    </table>

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
    <div>
    @foreach ($order->packages as $key => $order_package)
        <table class="info-details" style="margin-top: 10px">
            <thead><th></th></thead>
            <tr>
                @if(isModuleActive('MultiVendor'))
                <td >
                    <p><strong>{{ __('common.shop_name') }}:</strong> @if($order_package->seller->role->type == 'seller'){{ (@$order_package->seller->SellerAccount->seller_shop_display_name) ? @$order_package->seller->SellerAccount->seller_shop_display_name : @$order_package->seller->first_name }} @else {{ app('general_setting')->company_name }} @endif</p>
                </td>
                @endif
                <td>
                    <p><strong>{{ __('common.package') }}:</strong> {{ getNumberTranslate($order_package->package_code) }}</p>
                </td>
            </tr>
        </table>

        <table class="invoice-list" style="margin-top: 10px">
            <thead>
                <tr>
                    <th>{{ __('common.sl') }}</th>
                    <th><span>{{ __('common.name') }}</span></th>
                    <th><span>{{ __('common.details') }}</span></th>
                    <th><span>{{ __('common.points') }}</span></th>
                    <th><span>{{ __('common.price') }}</span></th>
                    <th><span>{{ __('common.total') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subTotal = 0;
                @endphp
                @foreach ($order_package->products as $key => $package_product)
                    <tr>
                        <td><span>{{$key+1}}</span></td>
                        <td>
                            <span>
                                {{ @$package_product->seller_product_sku->product->product_name??@$package_product->seller_product_sku->sku->product->product_name }}
                            </span>
                        </td>
                        @if (@$package_product->seller_product_sku->sku->product->product_type == 2)
                            <td>
                                {{ __('common.qty') }}: {{ getNumberTranslate($package_product->qty) }}
                                @php
                                    $countCombinatiion = count(@$package_product->seller_product_sku->product_variations);
                                @endphp
                                @foreach (@$package_product->seller_product_sku->product_variations as $key => $combination)
                                    @if ($combination->attribute->id == 1)
                                        <span>{{ $combination->attribute->name }}:</span><span> {{ $combination->attribute_value->color->name }}</span>
                                    @else
                                        {{ $combination->attribute->name }}:
                                        {{ $combination->attribute_value->value }}
                                    @endif
                                    @if ($countCombinatiion > $key + 1)

                                    @endif
                                @endforeach
                            </td>
                        @else
                            <td>{{__('common.qty') }}: {{ getNumberTranslate($package_product->qty) }}</td>
                        @endif
                        <td>{{ ($package_product->total_club_point ?? 0) > 0 ? getNumberTranslate($package_product->total_club_point) : '' }}</td>
                        <td>{{ single_price($package_product->price) }}</td>
                        <td>
                            {{ single_price($package_product->price * $package_product->qty) }}
                            @php
                            $subTotal += $package_product->price * $package_product->qty;
                            @endphp
                        </td>
                    </tr>
                @endforeach
                @php
                $minRows = 7;
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

            </tbody>

        </table>
        <table class="info">
            <thead><th></th></thead>
            <tr>
                <td></td>
                <td class="info-right" >

                    <table class="">
                        <thead><th></th></thead>
                        <tr>
                            <td><strong>{{ __('common.totals_points') }}</strong></td>
                            <td>
                                {{ $order_total_points > 0 ? getNumberTranslate($order_total_points) : '' }}
                            </td>
                        </tr>
                        @if($order->customer_id == null)
                            <tr>
                                <td><strong>{{ __('common.secret_id') }}</strong></td>
                                <td>{{$order->guest_info->guest_id}}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>{{__('order.txn_id')}}</strong></td>
                            <td>@if(@$order->order_payment->txn_id && @$order->order_payment->txn_id != 'none'){{ @$order->order_payment->txn_id }} @else - @endif</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('order.subtotal') }}</strong></td>
                            <td>
                                {{single_price($order->sub_total)}}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('common.discount') }}</strong></td>
                            <td>- {{single_price($order->discount_total)}}</td>
                        </tr>
                        @if($order->coupon)
                            <tr>
                                <td><strong>{{ __('common.coupon') }} {{__('common.discount')}}</strong></td>
                                <td>- {{single_price($order->coupon->discount_amount)}}</td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>{{ __('common.shipping_charge') }}</strong></td>
                            <td>+ {{single_price($order->shipping_total)}}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('gst.total_gst') }}</strong></td>
                            <td>+ {{single_price($order->tax_amount)}}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('common.grand_total') }}</strong></td>
                            <td>{{single_price($order->grand_total)}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @endforeach
</div>

    @php
        $generalSetting = app('general_setting');
        $locale = app()->getLocale();
        $footerTextRaw  = json_decode($generalSetting->invoice_footer_text ?? 'null', true);
        $footerQuoteRaw = json_decode($generalSetting->invoice_footer_quote ?? 'null', true);
        $footerText  = is_array($footerTextRaw)  ? ($footerTextRaw[$locale] ?? $footerTextRaw['es'] ?? '') : '';
        $footerQuote = is_array($footerQuoteRaw) ? ($footerQuoteRaw[$locale] ?? $footerQuoteRaw['es'] ?? '') : '';
    @endphp

    {{-- FOOTER --}}
    <div style="clear: both; margin-top:30px;">
        <strong>CUFE:</strong><br>
    </div>
    <table style="width: 100%">
        <thead><th></th></thead>
        <tr>
            <td style="text-align: center; background-color: #92b558;color: #fff;padding: 10px;">
                @if(filled($footerText))
                    {{ $footerText }}
                @endif
                @if(filled($footerText) && filled($footerQuote))
                    <br>
                @endif
                @if(filled($footerQuote))
                    {{ $footerQuote }}
                @endif
            </td>
        </tr>
    </table>


</div>

</body>
</html>
