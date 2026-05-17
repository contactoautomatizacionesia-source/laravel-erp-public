@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('modules/ordermanage/css/sale_details.css'))}}" />
<link rel="stylesheet" href="{{ asset('/public/css/tree.css') }}">
@endsection
@push('scripts')
<script>
    (function(){
        const tabBtns = document.querySelectorAll('.lif-tab-btn');
        const orderTab = document.getElementById('lif-tab-order');
        const diffTab = document.getElementById('lif-tab-differentials');
        const printBtn = document.getElementById('lif-print-btn');
        const printLabel = @json(__('order.print'));
        const viewOrderLabel = @json(__('tree.order_view'));

        function setTab(tab){
            const isDiff = tab === 'differentials';
            if (orderTab) orderTab.style.display = isDiff ? 'none' : 'block';
            if (diffTab) diffTab.style.display = isDiff ? 'block' : 'none';
            tabBtns.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tab));
            if (printBtn) {
                const orderUrl = printBtn.dataset.orderUrl;
                const printUrl = printBtn.dataset.printUrl;
                if (isDiff) {
                    printBtn.textContent = viewOrderLabel;
                    printBtn.href = orderUrl + '?tab=order';
                    printBtn.removeAttribute('target');
                } else {
                    printBtn.textContent = printLabel;
                    printBtn.href = printUrl;
                    printBtn.setAttribute('target', '_blank');
                }
            }
        }

        function updateUrl(tab){
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function(){
                const tab = btn.dataset.tab || 'order';
                setTab(tab);
                updateUrl(tab);
            });
        });

        const params = new URLSearchParams(window.location.search);
        const initialTab = params.get('tab') || 'order';
        setTab(initialTab);
    })();
</script>
@endpush
@section('mainContent')
    <div id="add_product">
        <section class="admin-visitor-area ign-sales-details">
            <div class="container-fluid p-0">
                <!-- Header Section -->
                <div class="row mb-24 align-items-end justify-content-between">
                    <div class="col-lg-8">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb p-0 mb-1 bg-transparent">
                                <li class="breadcrumb-item text-uppercase fs-11 fw-600 text-muted" style="letter-spacing: 0.05em;">{{ __('common.sales') }}</li>
                                <li class="breadcrumb-item text-uppercase fs-11 fw-600 text-muted active" style="letter-spacing: 0.05em;">{{ __('order.order_list') }}</li>
                            </ol>
                        </nav>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h1 class="ign-order-id">{{ __('order.order') }} <span class="ign-order-number">#{{ getNumberTranslate($order->order_number) }}</span></h1>
                            @if ($order->is_paid == 1)
                                <span class="ign-status-badge ign-status-paid">
                                    <i class="ti-check-box"></i> {{ __('common.paid') }}
                                </span>
                            @else
                                <span class="ign-status-badge badge-warning">
                                    <i class="ti-timer"></i> {{ __('common.pending') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                        <div class="btn-group-toolkit justify-content-lg-end flex-wrap">
                            <button type="button" class="btn-toolkit btn-outline btn-icon lif-tab-btn" data-tab="order">
                                {{ __('tree.order_tab') }}
                            </button>
                            <button type="button" class="btn-toolkit btn-outline btn-icon lif-tab-btn" data-tab="differentials">
                                {{ __('tree.differentials') }}
                            </button>
                            <a id="lif-print-btn"
                               data-order-url="{{ route('order_manage.show_details', $order->id) }}"
                               data-print-url="{{ route('order_manage.print_order_details', $order->id) }}"
                               href="{{ route('order_manage.print_order_details', $order->id) }}"
                               target="_blank"
                               class="btn-toolkit btn-secondary btn-icon">
                                <i class="ti-printer"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Main Content (8/12) -->
                    <div class="col-lg-8" id="printableArea">
                        <div id="lif-tab-order">
                            <div class="row mb-24">
                                @php
                                    $addr        = $order->address;
                                    $guestInfo   = $order->guest_info;
                                    $useAddress  = $addr !== null;
                                @endphp
                                <!-- Billing Info -->
                                <div class="col-md-6">
                                    <div class="ign-order-card">
                                        <div class="ign-card-header">
                                            <i class="ti-receipt"></i>
                                            <h4 class="ign-card-title">{{ __('defaultTheme.billing_info') }}</h4>
                                        </div>
                                        <div class="ign-card-body">
                                            <div class="ign-info-label">{{ __('common.name') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->billing_name : @$guestInfo->billing_name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.email') }}</div>
                                            <div class="ign-info-value">
                                                @php $billingEmail = $useAddress ? @$addr->billing_email : @$guestInfo->billing_email; @endphp
                                                <a class="text-dark" href="mailto:{{ $billingEmail }}">{{ $billingEmail }}</a>
                                            </div>

                                            <div class="ign-info-label">{{ __('common.phone') }}</div>
                                            <div class="ign-info-value">{{ getNumberTranslate($useAddress ? @$addr->billing_phone : @$guestInfo->billing_phone) }}</div>

                                            <div class="ign-info-label">{{ __('common.address') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->billing_address : @$guestInfo->billing_address }}</div>

                                            <div class="ign-info-label">{{ __('common.city') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->getBillingCity->name : @$guestInfo->getBillingCity->name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.state') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->getBillingState->name : @$guestInfo->getBillingState->name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.country') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->getBillingCountry->name : @$guestInfo->getBillingCountry->name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.postcode') }}</div>
                                            <div class="ign-info-value">{{ getNumberTranslate($useAddress ? @$addr->billing_postcode : @$guestInfo->billing_post_code) }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shipping Info -->
                                <div class="col-md-6">
                                    <div class="ign-order-card">
                                        <div class="ign-card-header">
                                            <i class="ti-truck"></i>
                                            <h4 class="ign-card-title">{{ __('defaultTheme.shipping_info') }}</h4>
                                        </div>
                                        <div class="ign-card-body">
                                            <div class="ign-info-label">{{ __('common.name') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->shipping_name : @$guestInfo->shipping_name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.email') }}</div>
                                            <div class="ign-info-value">
                                                @php $shippingEmail = $useAddress ? @$addr->shipping_email : @$guestInfo->shipping_email; @endphp
                                                <a class="text-dark" href="mailto:{{ $shippingEmail }}">{{ $shippingEmail }}</a>
                                            </div>

                                            <div class="ign-info-label">{{ __('common.phone') }}</div>
                                            <div class="ign-info-value">{{ getNumberTranslate($useAddress ? @$addr->shipping_phone : @$guestInfo->shipping_phone) }}</div>

                                            <div class="ign-info-label">{{ __('common.address') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->shipping_address : @$guestInfo->shipping_address }}</div>

                                            <div class="ign-info-label">{{ __('common.city') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->getShippingCity->name : @$guestInfo->getShippingCity->name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.state') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->getShippingState->name : @$guestInfo->getShippingState->name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.country') }}</div>
                                            <div class="ign-info-value">{{ $useAddress ? @$addr->getShippingCountry->name : @$guestInfo->getShippingCountry->name }}</div>
                                            
                                            <div class="ign-info-label">{{ __('common.postcode') }}</div>
                                            <div class="ign-info-value mb-4">{{ getNumberTranslate($useAddress ? @$addr->shipping_postcode : @$guestInfo->shipping_post_code) }}</div>

                                            <div class="ign-info-label">{{ __('shipping.shipping_method') }}</div>
                                            <div class="ign-info-value mb-0">
                                                <span class="badge badge-light p-2 rounded">
                                                     <i class="ti-package mr-1"></i> {{ $order->packages->first()->shipping->method_name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Info -->
                            <!-- Payment Info -->
                            <div class="row mb-24">
                                <div class="col-12">
                                    <div class="ign-order-card">
                                        <div class="ign-card-header">
                                            <i class="ti-wallet"></i>
                                            <h4 class="ign-card-title">{{ __('defaultTheme.payment_info') }}</h4>
                                        </div>
                                        <div class="ign-card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="ign-info-label">{{ __('common.payment_method') }}</div>
                                                    <div class="ign-info-value">{{ $order->GatewayName }}</div>

                                                    <div class="ign-info-label">{{ __('order.txn_id') }}</div>
                                                    <div class="ign-info-value">{{ @$order->order_payment->txn_id }}</div>

                                                    <div class="ign-info-label">{{ __('defaultTheme.payment_status') }}</div>
                                                    <div class="ign-info-value mb-0">
                                                        @if ($order->is_paid == 1)
                                                            <span class="text-success fw-600">{{__('common.paid')}}</span>
                                                        @else
                                                            <span class="text-warning fw-600">{{__('common.pending')}}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="ign-info-label">{{ __('common.amount') }}</div>
                                                    <div class="ign-info-value">{{ single_price(@$order->order_payment->amount) }}</div>

                                                    <div class="ign-info-label">{{ __('common.date') }}</div>
                                                    <div class="ign-info-value">{{ dateConvert(@$order->order_payment->created_at) }}</div>

                                                    <div class="ign-info-label">{{ __('common.payment_reference') }}</div>
                                                    <div class="ign-info-value mb-0">{{ filled(optional($order->order_payment)->txn_id) ? $order->order_payment->txn_id : '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Products Section -->
                            <div class="ign-products-section">
                                <div class="ign-section-header">
                                    <h3 class="ign-section-title">{{ __('common.details') }}</h3>
                                    <span class="text-muted fs-13">{{ $order->packages->sum(fn($p) => $p->products->count()) }} {{ __('common.items') }}</span>
                                </div>
                                <div class="ign-table-container">
                                    <table class="ign-table">
                                        <thead>
                                            <tr>
                                                <th class="ign-td-image">{{ __('common.image') }}</th>
                                                <th>{{ __('common.name') }}</th>
                                                <th>{{ __('common.details') }}</th>
                                                <th class="text-right">{{ __('common.points') }}</th>
                                                <th class="text-right">{{ __('common.price') }}</th>
                                                <th class="text-right">{{ __('common.tax') }}</th>
                                                <th class="text-right">{{ __('common.total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            @foreach ($order->packages as $order_package)
                                                @foreach ($order_package->products as $package_product)
                                                
                                                    <tr>
                                                        <td class="ign-td-image">
                                                            @php
                                                                $image = '';
                                                                if ($package_product->type == "gift_card") {
                                                                    $image = showImage(@$package_product->giftCard->thumbnail_image);
                                                                } else {
                                                                    $spsku = $package_product->seller_product_sku;
                                                                    $img_src = @$spsku->product->thum_img
                                                                        ?: @$spsku->sku->product->thumbnail_image_source;
                                                                    if (@$spsku->sku->product->product_type == 2) {
                                                                        $img_src = @$spsku->sku->variant_image
                                                                            ?: @$spsku->product->thum_img
                                                                            ?: @$spsku->sku->product->thumbnail_image_source;
                                                                    }
                                                                    $image = showImage($img_src);
                                                                }
                                                            @endphp
                                                            <img src="{{ $image }}" class="ign-product-img" alt="Product">
                                                        </td>
                                                        <td>
                                                            <div class="ign-product-name">{{ @$package_product->seller_product_sku->sku->product->product_name ?? @$package_product->giftCard->name }}</div>
                                                            <div class="ign-product-sku">SKU: {{ @$package_product->seller_product_sku->sku->sku ?? 'N/A' }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="ign-product-details">
                                                                {{ __('common.qty') }}: {{ getNumberTranslate($package_product->qty) }}<br>
                                                                @if($package_product->type != "gift_card" && @$package_product->seller_product_sku->sku->product->product_type == 2)
                                                                    @foreach (@$package_product->seller_product_sku->product_variations as $combination)
                                                                        {{ $combination->attribute->name }}: {{ $combination->attribute_value->value }}<br>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-right ign-product-price">{{ $package_product->unit_club_point * $package_product->qty }}</td>
                                                        <td class="text-right ign-product-price">{{ single_price($package_product->price) }}</td>
                                                        <td class="text-right text-muted">{{ single_price($package_product->tax_amount) }}</td>
                                                        <td class="text-right fw-700">{{ single_price($package_product->price * $package_product->qty + $package_product->tax_amount) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                    
                                </div>
                                <!-- Totals -->
                                <div class="ign-summary-wrapper">
                                    <div class="ign-summary-box">
                                        <div class="ign-summary-row">
                                            <span>{{ __('order.subtotal') }}</span>
                                            <span>{{ single_price($order->sub_total) }}</span>
                                        </div>
                                        <div class="ign-summary-row">
                                            <span>{{ __('common.discount') }}</span>
                                            <span class="text-danger">- {{ single_price($order->discount_total) }}</span>
                                        </div>
                                        <div class="ign-summary-row">
                                            <span>{{ __('common.shipping_charge') }}</span>
                                            <span class="text-success">+ {{ single_price($order->shipping_total) }}</span>
                                        </div>
                                        <div class="ign-summary-row">
                                            <span>{{ __('common.tax') }}/IVA</span>
                                            <span>{{ single_price($order->tax_amount) }}</span>
                                        </div>
                                        <div class="ign-summary-row">
                                            <span>{{ __('common.totals_points') }}</span>
                                            <span>{{ $order->total_points }}</span>
                                        </div>
                                        <div class="ign-summary-row total d-flex flex-wrap gap-4">
                                            <span class="ign-total-label">{{ __('order.grand_total') }}</span>
                                            <span class="ign-total-value">{{ single_price($order->grand_total) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Differentials Tab -->
                        <div id="lif-tab-differentials" style="display:none;">
                            @php
                                $diffSteps = [
                                    ['name' => $order->customer?->first_name . ' ' . $order->customer?->last_name, 'role' => __('tree.buyer'), 'plan' => 'Life', 'percent' => 20, 'diff' => 0, 'amount' => 0, 'type' => 'buyer'],
                                    ['name' => 'Javier Soto', 'role' => __('tree.no_differential'), 'plan' => 'Life', 'percent' => 20, 'diff' => 0, 'amount' => 0, 'type' => 'pass'],
                                    ['name' => 'Marta Rueda', 'role' => __('tree.differential'), 'plan' => 'Life', 'percent' => 28, 'diff' => 8, 'amount' => 186300, 'type' => 'gain'],
                                    ['name' => 'Alejandro H.', 'role' => __('tree.differential'), 'plan' => 'Platino', 'percent' => 40, 'diff' => 12, 'amount' => 124200, 'type' => 'gain'],
                                ];
                            @endphp
                            <div class="ign-order-card">
                                <div class="ign-card-header">
                                    <i class="ti-bar-chart"></i>
                                    <h4 class="ign-card-title">{{ __('tree.differentials_distribution') }}</h4>
                                    <span class="badge badge-success ml-auto">{{ __('tree.open') }}</span>
                                </div>
                                <div class="lif-diff-stepper">
                                    @foreach($diffSteps as $step)
                                        @php
                                            $boxClass = $step['type'] === 'buyer' ? 'dark' : ($step['type'] === 'pass' ? 'gray' : '');
                                            $initials = collect(explode(' ', $step['name']))->map(function($p){
                                                $char = function_exists('mb_substr') ? mb_substr($p, 0, 1) : substr($p, 0, 1);
                                                return strtoupper($char);
                                            })->take(2)->implode('');
                                        @endphp
                                        <div class="lif-diff-step">
                                            <div class="lif-diff-avatar">{{ $initials }}</div>
                                            <div class="lif-diff-box {{ $boxClass }}">
                                                <div class="lif-diff-row">
                                                    <strong>{{ $step['name'] }}</strong>
                                                    <span>{{ $step['role'] }}</span>
                                                </div>
                                                <div class="lif-diff-row" style="margin-top:6px;">
                                                    <span>{{ __('tree.panel_plan') }} {{ $step['plan'] }} · {{ $step['percent'] }}%</span>
                                                    @if($step['diff'] > 0)
                                                        <span>{{ $step['diff'] }}% · {{ single_price($step['amount']) }}</span>
                                                    @else
                                                        <span>{{ __('tree.no_differential') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="lif-diff-step">
                                        <div class="lif-diff-avatar">Σ</div>
                                        <div class="lif-diff-box border-success" style="background:#f0fdf4;">
                                            <div class="lif-diff-row">
                                                <strong>{{ __('tree.total_distributed') }}</strong>
                                                <span>{{ __('tree.max_20') }}</span>
                                            </div>
                                            <div class="lif-diff-row" style="margin-top:6px;">
                                                <span>{{ __('tree.base') }}: 1.240.000</span>
                                                <span class="text-success font-weight-bold">{{ single_price(310500) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar (4/12) -->
                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="ign-sidebar-card">
                            <h3 class="ign-sidebar-title">{{ __('order.order_status') }}</h3>
                            
                            @if ($order->is_cancelled != 1)
                                <form action="{{ route('order_manage.order_update_info', $order->id) }}" method="post">
                                    @csrf
                                    <div class="ign-input-group">
                                        <label class="ign-input-label" for="is_confirmed">{{ __('order.order_confirmation') }}</label>
                                        <select class="primary_select w-100" name="is_confirmed" id="is_confirmed">
                                            <option value="0" @if ($order->is_confirmed == 0) selected @endif>{{ __('order.pending') }}</option>
                                            <option value="1" @if ($order->is_confirmed == 1) selected @endif>{{ __('order.confirmed') }}</option>
                                            <option value="2" @if ($order->is_confirmed == 2) selected @endif>{{ __('order.declined') }}</option>
                                        </select>
                                    </div>

                                    <div class="ign-input-group d-none" id="cancel_reason_selector">
                                        <label class="ign-input-label" for="cancel_reason_id">{{ __('common.cancel_reason') }}</label>
                                        <select class="primary_select w-100" name="cancel_reason_id" id="cancel_reason_id">
                                            <option value="" selected>{{ __('common.select_cancel_reason') }}</option>
                                            @foreach($cancel_reasons as $reason)
                                                <option value="{{$reason->id}}">{{ $reason->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="ign-input-group" id="is_paid_div">
                                        <label class="ign-input-label" for="is_paid">{{ __('order.payment_status') }}</label>
                                        <select class="primary_select w-100" name="is_paid" id="is_paid">
                                            <option value="0" @if ($order->is_paid == 0) selected @endif>{{ __('order.pending') }}</option>
                                            <option value="1" @if ($order->is_paid == 1) selected @endif>{{ __('order.paid') }}</option>
                                        </select>
                                    </div>

                                    <div class="ign-input-group" id="is_completed_div">
                                        <label class="ign-input-label" for="is_completed">{{ __('order.preparation_status') }}</label>
                                        <select class="primary_select w-100" name="is_completed" id="is_completed">
                                            <option value="0" @if ($order->is_completed == 0) selected @endif>{{ __('order.pending') }}</option>
                                            <option value="1" @if ($order->is_completed == 1) selected @endif>{{ __('order.complete') }}</option>
                                        </select>
                                    </div>

                                    @if(!isModuleActive('MultiVendor'))
                                        <div class="ign-input-group" id="delivery_status_div">
                                            <label class="ign-input-label" for="delivery_status">{{ __('order.delivery_status') }}</label>
                                            <select class="primary_select w-100" name="delivery_status" id="delivery_status">
                                                @foreach ($processes as $process)
                                                    <option value="{{ $process->id }}" @if (@$order->packages->first()->delivery_status == $process->id) selected @endif>
                                                        {{ $process->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <button class="btn-toolkit btn-primary w-100 mt-3 p-3 fw-600">
                                        {{ __('order.update_order') }}
                                    </button>
                                </form>
                            @else
                                <div class="ign-priority-alert mt-0" style="background-color: #fee2e2;">
                                    <i class="ti-close" style="color: #ef4444;"></i>
                                    <div class="ign-priority-text" style="color: #991b1b;">
                                        <strong>{{ __('defaultTheme.order_cancelled') }}</strong><br>
                                        {{ __('order.reason') }}: {{ @$order->cancel_reason->name }}
                                    </div>
                                </div>
                            @endif

                            <div class="ign-priority-alert">
                                <i class="ti-info-alt"></i>
                                <div class="ign-priority-text">
                                    {{ __('order.priority_logistics_notice') }}
                                </div>
                            </div>
                        </div>
                        <!-- Cost Center -->
                            <div class="row mt-24">
                                <div class="col-12">
                                    <div class="ign-order-card">
                                        <div class="ign-info-label" style="font-size: 13px; color: #8dae58;">
                                            {{__('common.cost_center')}}: Armenia
                                        </div>
                                        <div class="ign-info-value mt-1">
                                            Centro comercial premium planza — Local 1119 Cr 43A # 30-25
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        (function($){
            "use strict";
            $(document).ready(function(){
                $(document).on('click','.gift_card_div', function(){
                    var gift_card_id = $(this).attr("data-gift-card-id");
                    var order_id = $(this).attr("data-order-id");
                    var mail = $(this).attr("data-customer-mail");
                    var qty = $(this).attr("data-qty");
                    $(this).text('Sending.....');
                    var _this = this;
                    $.post('{{ route('send_gift_card_code_to_customer') }}', {_token:'{{ csrf_token() }}', order_id:order_id, mail:mail, gift_card_id:gift_card_id, qty:qty}, function(data){

                        if (data == "true" || data == 1) {
                            toastr.success("{{__('common.mail_has_been_sent_successful')}}","{{__('common.success')}}")
                            $(_this).text('Sent')
                        }else {
                            toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                            $(_this).text("{{__('order.send_code_now')}}")
                        }

                    }).fail(function(response) {
                        if(response.responseJSON.msg){
                            toastr.error(response.responseJSON.msg ,"{{__('common.error')}}");
                            $('#pre-loader').addClass('d-none');
                            $(_this).text('Already Used')
                            return false;
                        }
                    });
                });

                $(document).on('click', '.is_digital_div', function(){
                    var customer_id = $(this).attr("data-customer-id");
                    var seller_id = $(this).attr("data-seller-id");
                    var order_id = $(this).attr("data-order-id");
                    var package_id = $(this).attr("data-package-id");
                    var seller_product_sku_id = $(this).attr("data-seller-sku-id");
                    var product_sku_id = $(this).attr("data-product-sku-id");
                    var mail = $(this).attr("data-customer-mail");
                    var qty = $(this).attr("data-qty");
                    $(this).text('Sending.....');
                    var _this = this;
                    $.post('{{ route('send_digital_file_access_to_customer') }}', {_token:'{{ csrf_token() }}', customer_id:customer_id, seller_id:seller_id, order_id:order_id, package_id:package_id, seller_product_sku_id:seller_product_sku_id, product_sku_id:product_sku_id, mail:mail, qty:qty}, function(data){
                        if (data == "true" || data == 1) {
                            toastr.success("{{__('common.mail_has_been_sent_successful')}}","{{__('common.success')}}")
                            $(_this).text('Sent')
                        }else {
                            toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                            $(_this).text("{{__('order.send_code_now')}}")
                        }
                    });
                });

                $(document).on('change', '#delivery_status', function(event){
                    var current_status = $('#current_package_status').val();
                    var change_status = $('#delivery_status').val();
                    if(current_status != change_status){
                        $('#delivery_note').removeClass('d-none');
                    }else{
                        $('#delivery_note').addClass('d-none');
                    }
                });

                $(document).on('change','#is_confirmed',function(){
                    let selected_status = $(this).val();
                    if(selected_status == 2){
                        $('#cancel_reason_selector').removeClass('d-none');
                        $("#is_paid_div").addClass('d-none');
                        $("#is_completed_div").addClass('d-none');
                        $("#delivery_status_div").addClass('d-none');
                    }else{
                        $('#cancel_reason_selector').addClass('d-none');
                        $("#is_paid_div").removeClass('d-none');
                        $("#is_completed_div").removeClass('d-none');
                        $("#delivery_status_div").removeClass('d-none');
                    }
                })
            });
        })(jQuery);
    </script>
@endpush
