
<div class="row mb-30">
    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box ">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>{{__('affiliate.Current Balance')}}</h3>
                        <p class="mb-0">{{__('affiliate.Affiliate Account Current Balance')}}</p>
                    </div>
                    <h1 class="gradient-color2 text-nowrap">{{$user->affiliateWallet ? single_price($user->affiliateWallet->amount) : 0}}</h1>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box ">
                <div class="d-flex justify-content-between">
                    <div class="row">
                        <div class="col-12">
                           <div class="row d-flex justify-content-between">
                               <div>
                                   <h3>{{__('affiliate.Total Earning')}}</h3>
                               </div>
                               <h1 class="gradient-color2 text-nowrap">
                                   @if($start_date && $end_date)
                                       {{single_price($user->affiliateCommissions->where('status',1)->whereBetween('date',[$start_date,$end_date])->sum('amount'))}}
                                   @else
                                       {{single_price($user->affiliateCommissions->where('status',1)->sum('amount'))}}
                                   @endif
                               </h1>
                           </div>
                        </div>
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Pending')}}</h3>
                                </div>
                                <h1 class="gradient-color2 text-nowrap">
                                    @if($start_date && $end_date)
                                        {{single_price($user->affiliateCommissions->where('status',0)->whereBetween('date',[$start_date,$end_date])->sum('amount'))}}
                                    @else
                                        {{single_price($user->affiliateCommissions->where('status',0)->sum('amount'))}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box ">
                <div class="d-flex justify-content-between">
                    <div class="row">
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Withdrawn')}}</h3>
                                </div>
                                <h1 class="gradient-color2 text-nowrap">
                                    @if($start_date && $end_date)
                                        {{single_price($user->affiliateTransaction->where('status',1)->where('payment_type','!=',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
                                    @else
                                        {{single_price($user->affiliateTransaction->where('status',1)->where('payment_type','!=',3)->sum('withdraw_amount'))}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Pending')}}</h3>
                                </div>
                                <h1 class="gradient-color2 text-nowrap">
                                    @if($start_date && $end_date)
                                        {{single_price($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->whereBetween('request_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
                                    @else
                                        {{single_price($user->affiliateTransaction->where('status',0)->where('payment_type','!=',3)->sum('withdraw_amount'))}}
                                    @endif
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="#" class="d-block">
            <div class="white-box ">
                <div class="d-flex justify-content-between">
                    <div class="row">
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Transfer To User Wallet')}}</h3>
                                </div>
                                <h1 class="gradient-color2 text-nowrap">
                                    @if($start_date && $end_date)
                                        {{single_price($user->affiliateTransaction->where('status',1)->where('payment_type',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
                                    @else
                                        {{single_price($user->affiliateTransaction->where('status',1)->where('payment_type',3)->sum('withdraw_amount'))}}
                                    @endif

                                </h1>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row d-flex justify-content-between">
                                <div>
                                    <h3>{{__('affiliate.Pending')}}</h3>
                                </div>
                                <h1 class="gradient-color2 text-nowrap">
                                    @if($start_date && $end_date)
                                        {{single_price($user->affiliateTransaction->where('status',0)->where('payment_type',3)->whereBetween('confirm_date',[$start_date,$end_date])->sum('withdraw_amount'))}}
                                    @else
                                        {{single_price($user->affiliateTransaction->where('status',0)->where('payment_type',3)->sum('withdraw_amount'))}}
                                    @endif

                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
