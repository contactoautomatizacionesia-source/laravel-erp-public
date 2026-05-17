@push('styles')
    <link rel="stylesheet" href="{{ asset('/public/css/customer_profile.css') }}">
@endpush
<div class="cd-card mb-4 d-flex position-relative">
    
                
    <button id="toggleSidebarMobile" class="btn btn-sm text-white d-inline-flex align-items-center gap-2 d-lg-none"
    style="background-color: var(--base_color);padding: 8px 10px;border-radius: 8px;position: absolute;z-index: 999;top: -12px;left: -10px;">
        <i class="fas fa-bars"></i>
    </button>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- PROFILE HERO                                   --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="cd-profile-hero">
        <div class="d-flex align-items-start gap-3">

            <div class="cd-avatar-wrap position-relative">
                <img
                    src="{{ auth()->user()->avatar ? showImage(auth()->user()->avatar) : showImage('frontend/default/img/avatar.jpg') }}"
                    alt="{{ auth()->user()->first_name }}"
                >
                
                {{-- Edit profile --}}
                <a href="{{ url('/profile') }}" class=" edit-btn badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    {{-- {{ __('common.edit') }} --}}
                </a>
            </div>

            <div class="flex-grow-1 min-w-0 mt-2">
                <h5 class="cd-user-name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h5>
                <span class="d-flex  gap-2">
                    <span class="svg-icon-plan svg-xs">
                        {!! data_get($planContext, 'current_plan.styles.icon')!!}
                    </span>
                    <span class="fs-18 mt-1">{{data_get($planContext, 'display_name', '-')}}</span>
                </span>
                <div class="cd-actions my-1">

                    {{-- Share referral --}}
                    <a href="{{ url('/profile/referral') }}" class="text-underline btn-ghost d-flex align-items-center">
                        <svg class="me-1" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        {{ __('marketing.refer') }}
                    </a>

                    {{-- Mi Red --}}
                    <a href="{{ url('/profile/network') }}" class="text-underline btn-ghost d-flex align-items-center">
                        <svg class="me-1" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="5" r="3"/><circle cx="5" cy="19" r="3"/><circle cx="19" cy="19" r="3"/><path d="M12 8v5"/><path d="M12 13l-5 3"/><path d="M12 13l5 3"/></svg>
                        {{ __('tree.network') }}
                    </a>

                    {{-- Affiliate --}}
                    @if(isModuleActive('Affiliate'))
                        @if(empty(auth()->user()->affiliate_request))
                            <a href="{{ route('affiliate.customerJoinAffiliate') }}" class="text-underline btn-ghost d-flex align-items-center">
                                <svg class="me-1" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                {{ __('common.join_affiliate') }}
                            </a>
                        @elseif(auth()->user()->affiliate_request == 1)
                            @if(auth()->user()->accept_affiliate_request == 0)
                                <a href="javascript:void(0)" class="text-underline btn-ghost d-flex align-items-center cd-btn-affiliate-pending">
                                    <svg class="me-1"  width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ __('common.join_affiliate') }}
                                </a>
                            @elseif(auth()->user()->accept_affiliate_request == 1)
                                <a href="{{ route('affiliate.my_affiliate.index') }}" target="_blank" class="text-underline btn-ghost d-flex align-items-center">
                                    <svg class="me-1"  width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                    {{ __('common.affiliate_profile') }}
                                </a>
                            @endif
                        @endif
                    @endif

                </div>
            </div>
            
        </div>
        <div class="">
           
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- LEVEL + PROGRESS (hardcoded until logic ready) --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="cd-level-block">
        <div>
            <div class="row justify-content-between align-items-end">
                <div class="col-auto">
                    <p class="text-muted mb-1">Proximo Rango:
                        <span class="svg-icon-plan svg-xs  ms-1">
                            {!! data_get($planContext, 'next_plan.styles.icon') !!}
                        </span>
                        <span class="fs-20 text-color fw-500" >{{ data_get($planContext, 'next_plan_child.display_name') }}</span>
                        @if (data_get($planContext, 'next_plan.scale_type'))
                            <span class="badge_micro">{{ __('common.'.data_get($planContext, 'next_plan.scale_type')) }}</span>
                        @endif
                    </p>
                </div>
                <div class="col-auto">
                    <span class="fs-20 fw-500">{{ data_get($planContext, 'progress_to_next_plan', 0) }}%</span>
                </div>
            </div>
        </div>
        <div style="width:100%; position: relative; margin-top: 5px;">
            {{-- Background Barra --}}
            <div class="my-2" style="width:100%; background:#e9ecef; border-radius:10px; height:8px; overflow: hidden;">
                {{-- Relleno Barra --}}
                <div style="
                    width: {{ data_get($planContext, 'progress_to_next_plan', 0) }}%;
                    height: 100%;
                    background: var(--base_color);
                    border-radius: 10px;
                    transition: width 0.5s ease;
                "></div>
            </div>

            {{-- Meta --}}
            <div >
                <div class="row justify-content-between align-items-end mb-2">
                    <div class="col-auto">
                        <span class="text-muted mb-1">{{ data_get($planContext, 'current_points', 0) }}</span> / <span class="text-muted">{{ data_get($planContext, 'next_plan_target_points', 0) }}</span>
                    </div>
                    <div class="col-auto">
                        <span class="text-muted" style="font-style:italic">Faltan {{ data_get($planContext, 'next_plan_target_points', 0) - data_get($planContext, 'current_points', 0) }} puntos</span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    

    {{-- ══════════════════════════════════════════════ --}}
    {{-- WALLET                                         --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="cd-wallet-block">
        <p class="text-muted mb-1">{{ __('wallet.wallet_balance') }}</p>
        <p class="cd-wallet-amount">
            {{ auth()->check() ? single_price(auth()->user()->CustomerCurrentWalletAmounts) : single_price(0.00) }}
        </p>

        <div class="d-flex gap-2 flex-wrap">
            @if(url()->current() != url('/wallet/my-wallet-create'))
                <a href="#" data-bs-toggle="modal" data-bs-target="#recharge_wallet" class="cd-btn cd-btn-primary flex-grow-1 justify-content-center">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    {{ __('wallet.recharge') }}
                </a>
            @endif
        </div>
    </div>

    

    
</div>
@include(theme('pages.profile.wallets.components._recharge_modal'))
