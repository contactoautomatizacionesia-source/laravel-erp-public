@extends('frontend.amazy.pages.profile.layouts._profile_layout')

@section('profile_content')
    <style>
        .cd-card  {display: none!important;}
    </style>
    {{-- ══ BANNER: FIRMA DE DOCUMENTOS PENDIENTE ══ --}}
    @if(!empty($pendingSignatureBatch) && $pendingSignatureBatch->documents->isNotEmpty())
    @php
        $pendingCount = $pendingSignatureBatch->documents->count();
        $totalDocs = $pendingSignatureBatch->total_docs;
    @endphp
    <div class="row mb-3">
        <div class="col-12">
            <div style="background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%); border: 1.5px solid #f59e0b; border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <div style="flex-shrink:0; width:42px; height:42px; background:#f59e0b; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div style="flex:1; min-width:200px;">
                    <p style="margin:0; font-weight:700; font-size:14px; color:#92400e;">
                        Tienes {{ $pendingCount }} {{ $pendingCount === 1 ? 'documento pendiente' : 'documentos pendientes' }} de firma
                    </p>
                    <p style="margin:4px 0 0; font-size:12px; color:#78350f;">
                        Para activar tu cuenta correctamente debes firmar {{ $pendingCount === $totalDocs ? 'todos los contratos asociados' : "los contratos restantes ($pendingCount de $totalDocs)" }} a tu registro.
                        Revisa tu WhatsApp o SMS — ProtecData te ha enviado el enlace de firma a tu número registrado.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
    {{-- ════════════════════════════════════════════ --}}

    <div class="row">
        <div class="col-12" id="dashboardContent">
            <div class="row">
                <div class="col-12">
                    <div class="form-card">
                        <div class="row align-items-center">
                            <div class="col-md-auto col-12  mb-md-0 mb-3 text-center">
                                    
                                <div class="d-inline-block rounded-circle position-relative"
                                    style="border: 3px solid var(--border_color);">
                                    <a href="{{ url('/profile') }}" class=" edit-btn badge">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        {{-- {{ __('common.edit') }} --}}
                                    </a>
                                    <img class="rounded-circle"
                                        src="{{ auth()->user()->avatar ? showImage(auth()->user()->avatar) : showImage('frontend/default/img/avatar.jpg') }}"
                                        alt="avatar"
                                        style="width: 110px; height: 110px; object-fit: cover;">
                                        <div class="cd-level-badge text-uppercase">
                                            @if (auth()->user()->is_active == 1)
                                                {{ __('common.active') }}
                                            @elseif ($customer->is_active == 0)
                                            {{ __('common.disabled') }}
                                            @else
                                                {{ __('common.in-active') }}
                                            @endif
                                        </div>
                                </div>
                            </div>
                            <div class="col-md-auto col-12 flex-1">
                                <div class="row align-items-center">
                                    <div class="col-md-auto col-12 flex-1 mb-md-0 mb-3 text-md-left text-center">
                                        <h2 class="mb-2 fs-18 text-dark-green fw-bold">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h2>
                                        
                                        <p class="mt-2">{{ auth()->user()->email }} • {{ auth()->user()->phone }}</p>
                                        <p class="text-muted">{{__('common.member_since')}} {{dateConvert(auth()->user()->created_at)}} </p>
                                    </div>
                                    <div class="col-md-auto col-12 text-md-left text-center">
                                        {{-- Share referral --}}
                                        <a href="{{ url('/profile/referral') }}" class="cd-btn cd-btn-referral mb-1">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                            {{ __('marketing.refer') }}
                                        </a>
                                        {{-- Affiliate --}}
                                        @if(isModuleActive('Affiliate'))
                                            @if(empty(auth()->user()->affiliate_request))
                                                <a href="{{ route('affiliate.customerJoinAffiliate') }}" class="cd-btn cd-btn-affiliate mb-1">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                    {{ __('common.join_affiliate') }}
                                                </a>
                                            @elseif(auth()->user()->affiliate_request == 1)
                                                @if(auth()->user()->accept_affiliate_request == 0)
                                                    <a href="javascript:void(0)" class="cd-btn cd-btn-affiliate-pending mb-1">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                        {{ __('common.join_affiliate') }}
                                                    </a>
                                                @elseif(auth()->user()->accept_affiliate_request == 1)
                                                    <a href="{{ route('affiliate.my_affiliate.index') }}" target="_blank" class="cd-btn cd-btn-affiliate mb-1">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                                        {{ __('common.affiliate_profile') }}
                                                    </a>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-8 mx-auto">
                    @if(!empty($planContext))
                        <x-plan-card :planContext="$planContext">
                            <x-slot name="header">
                                @if(data_get($planContext, 'current_explicit_discount.discount_quantity') !== null)
                                <p><span class="badge_5">{{ $planContext['current_explicit_discount']['discount_quantity'] }}% {{ __('common.discount') }}</span></p>
                                @endif
                            </x-slot>

                            <x-slot name="requirements">
                                @if(!empty($planContext['next_plan']))
                                    <button class="btn-toolkit btn-primary" data-bs-toggle="modal" data-bs-target="#next_plan_rules_modal" id="open_rules_modal">{{__('common.requirements')}}</button>
                                @endif
                            </x-slot>

                            <x-slot name="actions">
                                <button class="btn-toolkit btn-secondary" data-bs-toggle="modal" data-bs-target="#benefits_modal" id="open_benefits_modal" data-plan-color="{{ data_get($planContext, 'current_plan.styles.primaryColor', '#2b3c8d') }}">{{__('common.current_benefits')}}</button>
                            </x-slot>
                        </x-plan-card>
                    @else
                        <x-plan-card-empty />
                    @endif

                    <div class="form-card">
                        <h3 class="mb-3">
                            {{ __('common.plans_history') }}
                        </h3>
                        <x-plan-timeline :history="$planHistory" type="dashboard" :empty-text="__('common.no_results_found')" />
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-card">
                                <h3>{{__('common.red_details')}}</h3>
                                <p>
                                    <span>Decendientes: </span>
                                    <span class="cd-wallet-amount d-inline-block"><i class="ti-user mx-1 small"></i>{{ $descendantsCount }}</span>
                                </p>

                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ url('/profile/network') }}" class="cd-btn cd-btn-primary flex-grow-1 justify-content-center">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="5" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M12 8v5"/><path d="M12 13l-5 3"/><path d="M12 13l5 3"/></svg>
                                        {{ __('tree.network') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-card">
                                <h3>{{ __('wallet.wallet_balance') }}</h3>
                                <p class="cd-wallet-amount">
                                    {{ auth()->check() ? single_price(auth()->user()->CustomerCurrentWalletAmounts) : single_price(0.00) }}
                                </p>

                                <div class="d-flex gap-2 flex-wrap">
                                    @if(url()->current() != url('/wallet/my-wallet-create'))
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#recharge_wallet" class="cd-btn cd-btn-primary flex-grow-1 justify-content-center">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                            {{ __('wallet.recharge_wallet') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-card">
                        <h3>{{__('common.summary')}}</h3>
                        @php
                            $dashboardCards = [
                                [
                                    'icon' => 'ti-shopping-cart',
                                    'label' => __('amazy.Total Order'),
                                    'value' => getNumberTranslate($total_order_count),
                                ],
                                [
                                    'icon' => 'ti-heart',
                                    'label' => __('customer_panel.my_wishlist'),
                                    'value' => getNumberTranslate($total_wishlist_count),
                                ],
                                [
                                    'icon' => 'ti-reload',
                                    'label' => __('refund.refund_success'),
                                    'value' => getNumberTranslate($total_success_refund),
                                ],
                                [
                                    'icon' => 'ti-bag',
                                    'label' => __('amazy.Product in Cart'),
                                    'value' => getNumberTranslate($total_item_in_carts),
                                ],
                                [
                                    'icon' => 'ti-check-box',
                                    'label' => __('amazy.Completed Order'),
                                    'value' => getNumberTranslate($total_completed_order_count),
                                ],
                            ];
                        @endphp
                        <div class="dashBoard_cart_boxs mb_25 dynamic_svg">
                            @foreach ($dashboardCards as $card)
                                <div class="single_cart_box d-flex align-items-center justify-content-center text-center flex-column">
                                    <div class="icon d-flex align-items-center justify-content-center text-center">
                                        <i class="{{ $card['icon'] }}"></i>
                                    </div>
                                    <span class="font_14 f_w_500 mt-2">{{ $card['label'] }}</span>
                                    <h2 class="font_24 f_w_700 m-0">{{ $card['value'] }}</h2>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-card ">
                        <div class=" d-flex align-items-center gap_15 pb_10 mb_5 justify-content-between">
                            <h3 class="">{{ __('amazy.Purchase History') }}</h3>
                            @if(count($purchase_histories) > 0)
                            <a href="{{ route('frontend.my_purchase_histories') }}"
                                class="amaz_badge_btn2 text-uppercase text-nowrap">{{ __('common.see_all') }}</a>
                            @endif
                        </div>
                        <div class="dashboard_white_box_body">
                            <div class="table-responsive">
                                <table class="table amazy_table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="font_14 f_w_700" scope="col">{{ __('common.details') }}</th>
                                            <th class="font_14 f_w_700 border-start-0 border-end-0" scope="col">
                                                {{ __('common.amount') }}</th>
                                            <th class="font_14 f_w_700" scope="col">{{ __('common.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($purchase_histories as $key => $order)
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <p class="font_14 f_w_700 mb-0 lh-base">{{ __('common.order') }}:
                                                            {{ getNumberTranslate(@$order->order->order_number) }}</p>
                                                        @if (isModuleActive('MultiVendor'))
                                                            <p class="font_14 f_w_600 mb-1 lh-base">
                                                                {{ __('common.package') }}:
                                                                {{ getNumberTranslate(@$order->package_code) }}</p>
                                                        @endif
                                                        <p class="font_14 f_w_500 mb-0 lh-1">
                                                            {{ dateConvert($order->created_at) }}</p>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $total_price =
                                                            $order->products->sum('total_price') +
                                                            $order->shipping_cost +
                                                            $order->tax_amount;
                                                    @endphp
                                                    <h4 class="font_16 f_w_500 m-0 ">{{ single_price($total_price) }}</h4>
                                                </td>
                                                <td>
                                                    @if ($order->is_cancelled)
                                                        <a
                                                            class="table_badge_btn style_5 text-nowrap">{{ __('common.cancelled') }}</a>
                                                    @elseif($order->delivery_status == 1)
                                                        <a
                                                            class="table_badge_btn style3 text-nowrap">{{ __('common.pending') }}</a>
                                                    @elseif($order->delivery_status == 2)
                                                        <a
                                                            class="table_badge_btn text-nowrap">{{ __('defaultTheme.processing') }}</a>
                                                    @elseif($order->delivery_status == 3)
                                                        <a
                                                            class="table_badge_btn text-nowrap">{{ __('common.shipped') }}</a>
                                                    @elseif($order->delivery_status == 4)
                                                        <a
                                                            class="table_badge_btn text-nowrap">{{ __('amazy.Received') }}</a>
                                                    @elseif($order->delivery_status >= 5)
                                                        <a
                                                            class="table_badge_btn style4 text-nowrap">{{ !empty($order->delivery_process) ? $order->delivery_process->name : '' }}</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="form-card ">
                        <div class="d-flex align-items-center gap_15 pb_10 mb_5 justify-content-between">
                            <h3 class="">{{ __('customer_panel.my_wishlist') }}</h3>
                            @if(count($wishlists) > 0)
                            <a href="{{ route('frontend.my-wishlist') }}"
                                class="amaz_badge_btn2 text-uppercase">{{ __('common.see_all') }}</a>
                            @endif
                        </div>
                        <div class="dashboard_white_box_body">
                            <div class="dash_product_lists">
                                @forelse ($wishlists as $key => $product)
                                    @if ($product->type == 'product')
                                        <a href="{{ singleProductURL(@$product->product->seller->slug, @$product->product->slug) }}"
                                            class="dashboard_order_list d-flex align-items-center flex-wrap  gap_20">
                                            <div class="thumb">
                                                <img class="img-fluid"
                                                    src="@if (@$product->product->thum_img != null) {{ showImage(@$product->product->thum_img) }} @else {{ showImage(@$product->product->product->thumbnail_image_source) }} @endif"
                                                    alt="@if (@$product->product->product_name) {{ \Illuminate\Support\Str::limit(@$product->product->product_name, 28, $end = '...') }}  @else {{ \Illuminate\Support\Str::limit(@$product->product->product->product_name, 28, $end = '...') }} @endif"
                                                    title="@if (@$product->product->product_name) {{ \Illuminate\Support\Str::limit(@$product->product->product_name, 28, $end = '...') }}  @else {{ \Illuminate\Support\Str::limit(@$product->product->product->product_name, 28, $end = '...') }} @endif">
                                            </div>
                                            <div class="dashboard_order_content">
                                                <h4 class="font_14 f_w_700 mb-1 lh-base theme_hover">
                                                    @if (@$product->product->product_name)
                                                        {{ \Illuminate\Support\Str::limit(@$product->product->product_name, 28, $end = '...') }}
                                                    @else
                                                        {{ \Illuminate\Support\Str::limit(@$product->product->product->product_name, 28, $end = '...') }}
                                                    @endif
                                                </h4>
                                                <p class="font_14 f_w_500 d-flex align-items-center gap-2">
                                                    @if (getProductwitoutDiscountPrice(@$product->product) != single_price(0))
                                                        <span
                                                            class="discount_prise text-decoration-line-through">{{ getProductwitoutDiscountPrice(@$product->product) }}</span>
                                                    @endif
                                                    <span
                                                        class="secondary_text">{{ getProductDiscountedPrice(@$product->product) }}</span>
                                                </p>
                                            </div>
                                        </a>
                                    @else
                                        <a href="{{ route('frontend.gift-card.show', @$product->product->seller->slug, @$product->product->slug) }}"
                                            class="dashboard_order_list d-flex align-items-center flex-wrap  gap_20">
                                            <div class="thumb">
                                                <img class="img-fluid"
                                                    src="{{ showImage(@$product->giftcard->thumbnail_image) }}"
                                                    alt="{{ \Illuminate\Support\Str::limit(@$product->giftcard->name, 28, $end = '...') }}"
                                                    title="{{ \Illuminate\Support\Str::limit(@$product->giftcard->name, 28, $end = '...') }}">
                                            </div>
                                            <div class="dashboard_order_content">
                                                <h4 class="font_16 f_w_700 mb-1 lh-base theme_hover">
                                                    {{ \Illuminate\Support\Str::limit(@$product->giftcard->name, 28, $end = '...') }}
                                                </h4>
                                                <p class="font_14 f_w_500 d-flex align-items-center gap-2">
                                                    @if (getGiftcardwithoutDiscountPrice(@$product->giftcard) != single_price(0))
                                                        <span
                                                            class="discount_prise text-decoration-line-through">{{ getGiftcardwithoutDiscountPrice(@$product->giftcard) }}</span>
                                                    @endif
                                                    <span
                                                        class="secondary_text">{{ getProductDiscountedPrice(@$product->product) }}</span>
                                                </p>
                                            </div>
                                        </a>
                                    @endif
                                @empty
                                    <p class="text-center py-3">Aún no hay elementos en la lista</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
              
                <!-- cta::start  -->
                <div class="col-xl-12 mb_20">
                    <x-random-ads-component />
                </div>
                <!-- cta::end  -->
                <div class="col-lg-6 ">
                    <div class="dashboard_white_box bg-white mb_25 amazy_full_height">
                        <div class="dashboard_white_box_header d-flex align-items-center gap_15 amazy_bb3 pb_10 mb_5">
                            <h3 class="font_20 f_w_700 mb-0  flex-fill">{{ __('order.recent_order') }}</h3>
                            <a href="{{ url('/my-purchase-orders') }}"
                                class="amaz_badge_btn2 text-uppercase">{{ __('common.see_all') }}</a>
                        </div>
                        <div class="dashboard_white_box_body">
                            <div class="dash_product_lists">
                                @foreach ($recent_order_products as $key => $product)
                                    @if ($product->type == 'product')
                                        <a href="{{ singleProductURL(@$product->seller_product_sku->product->seller->slug, @$product->seller_product_sku->product->slug) }}"
                                            class="dashboard_order_list d-flex align-items-center flex-wrap  gap_20">
                                            <div class="thumb">
                                                <img class="img-fluid"
                                                    src="
                                                        @if (@$product->seller_product_sku->product->product->product_type == 1) {{ showImage(@$product->seller_product_sku->product->product->thumbnail_image_source) }}
                                                        @else
                                                            {{ showImage(@$product->seller_product_sku->sku->variant_image ? @$product->seller_product_sku->sku->variant_image : @$product->seller_product_sku->product->product->thumbnail_image_source) }} @endif
                                                        "
                                                    alt="{{ textLimit($product->seller_product_sku->product->product_name, 22) }}"
                                                    title="{{ textLimit($product->seller_product_sku->product->product_name, 22) }}">
                                            </div>
                                            <div class="dashboard_order_content">
                                                <h4 class="font_16 f_w_700 mb-1 lh-base theme_hover">
                                                    {{ textLimit($product->seller_product_sku->product->product_name, 22) }}
                                                </h4>
                                                <p class="font_14 f_w_500 d-flex align-items-center gap-2">
                                                    @if (getProductwitoutDiscountPrice(@$product->seller_product_sku->product) != single_price(0))
                                                        <span
                                                            class="discount_prise text-decoration-line-through">{{ getProductwitoutDiscountPrice(@$product->seller_product_sku->product) }}
                                                        </span>
                                                    @endif
                                                    <span
                                                        class="secondary_text">{{ getProductDiscountedPrice(@$product->seller_product_sku->product) }}</span>
                                                </p>
                                            </div>
                                        </a>
                                    @else
                                        @if ($product->giftcard->sku)
                                            <a href="{{ route('frontend.gift-card.show', @$product->giftcard->sku) }}"
                                                class="dashboard_order_list d-flex align-items-center flex-wrap  gap_20">
                                                <div class="thumb">
                                                    <img class="img-fluid"
                                                        src="{{ showImage(@$product->giftCard->thumbnail_image) }}"
                                                        alt="{{ textLimit($product->giftCard->name, 22) }}"
                                                        title="{{ textLimit($product->giftCard->name, 22) }}">
                                                </div>
                                                <div class="dashboard_order_content">
                                                    <h4 class="font_16 f_w_700 mb-1 lh-base theme_hover">
                                                        {{ textLimit($product->giftCard->name, 22) }}</h4>
                                                    <p class="font_14 f_w_500 d-flex align-items-center gap-2">
                                                        @if (getGiftcardwithoutDiscountPrice(@$product->giftcard) != single_price(0))
                                                            <span
                                                                class="discount_prise text-decoration-line-through">{{ getGiftcardwithoutDiscountPrice(@$product->giftcard) }}</span>
                                                        @endif
                                                        <span
                                                            class="secondary_text">{{ getGiftcardwithDiscountPrice(@$product->giftcard) }}</span>
                                                    </p>
                                                </div>
                                            </a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 ">
                    <div class="dashboard_white_box bg-white mb_25 amazy_full_height">
                        <div class="dashboard_white_box_header d-flex align-items-center gap_15 amazy_bb3 pb_10 mb_5">
                            <h3 class="font_20 f_w_700 mb-0  flex-fill">{{ __('amazy.Product in Cart') }}</h3>
                            <a href="{{ url('/cart') }}"
                                class="amaz_badge_btn2 text-uppercase">{{ __('common.see_all') }}</a>
                        </div>
                        <div class="dashboard_white_box_body">
                            <div class="dash_product_lists">
                                @foreach ($carts as $key => $cart)
                                    @if ($cart->product_type == 'product')
                                        <a href="{{ singleProductURL($cart->seller->slug, $cart->product->product->slug) }}"
                                            class="dashboard_order_list d-flex align-items-center flex-wrap  gap_20">
                                            <div class="thumb">
                                                <img class="img-fluid"
                                                    src="
                                                        @if (@$cart->product->product->product->product_type == 1) {{ showImage(@$cart->product->product->product->thumbnail_image_source) }}
                                                        @else
                                                            {{ showImage(@$cart->product->sku->variant_image ? @$cart->product->sku->variant_image : @$cart->product->product->product->thumbnail_image_source) }} @endif
                                                        "
                                                    alt="{{ textLimit($cart->product->product->product_name, 28) }}"
                                                    title="{{ textLimit($cart->product->product->product_name, 28) }}">
                                            </div>
                                            <div class="dashboard_order_content">
                                                <h4 class="font_16 f_w_700 mb-1 lh-base theme_hover">
                                                    {{ textLimit($cart->product->product->product_name, 28) }}</h4>
                                                <p class="font_14 f_w_500 d-flex align-items-center gap-2">
                                                    @if (getProductwitoutDiscountPrice(@$cart->product->product) != single_price(0))
                                                        <span
                                                            class="discount_prise text-decoration-line-through">{{ getProductwitoutDiscountPrice(@$cart->product->product) }}
                                                        </span>
                                                    @endif
                                                    <span class="secondary_text">{{ single_price($cart->price) }}</span>
                                                </p>
                                            </div>
                                        </a>
                                    @else
                                        @if ($cart->giftCard->sku)
                                            <a href="{{ route('frontend.gift-card.show', $cart->giftCard->sku) }}"
                                                class="dashboard_order_list d-flex align-items-center flex-wrap  gap_20">
                                                <div class="thumb">
                                                    <img class="img-fluid"
                                                        src="{{ showImage(@$cart->giftCard->thumbnail_image) }}"
                                                        alt="{{ textLimit(@$cart->giftCard->name, 28) }}"
                                                        title="{{ textLimit(@$cart->giftCard->name, 28) }}">
                                                </div>
                                                <div class="dashboard_order_content">
                                                    <h4 class="font_16 f_w_700 mb-1 lh-base theme_hover">
                                                        {{ textLimit(@$cart->giftCard->name, 28) }}</h4>
                                                    <p class="font_14 f_w_500 d-flex align-items-center gap-2">
                                                        @if (getGiftcardwithoutDiscountPrice(@$cart->giftCard) != single_price(0))
                                                            <span
                                                                class="discount_prise text-decoration-line-through">{{ getGiftcardwithoutDiscountPrice(@$cart->giftCard) }}</span>
                                                        @endif
                                                        <span
                                                            class="secondary_text">{{ single_price($cart->price) }}</span>
                                                    </p>
                                                </div>
                                            </a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
      {{-- Modals --}}
    @include(theme('pages.profile.partials._benefits_modal'))
    @include(theme('pages.profile.partials._next_plan_rules_modal'))
@endsection

@push('scripts')
    <script src="{{ asset('/public/js/plan-badge.js') }}"></script>
    <script src="{{ asset('/public/js/modal_rule_followed.js') }}"></script>
    <script>
        (function(){
            const el = document.querySelector('.lif-plan-name');
            if (!el || !window.LifPlanBadge) return;
            const color = el.dataset.planColor || '';
            el.style.color = LifPlanBadge.darkenColor(color, 0.45);

            const btn = document.querySelector('.lif-plan-btn');
            if (btn) {
                const btnColor = btn.dataset.planColor || color;
                btn.style.backgroundColor = LifPlanBadge.darkenColor(btnColor, 0.45);
                btn.style.borderColor = LifPlanBadge.darkenColor(btnColor, 0.45);
            }
        })();
    </script>
@endpush
