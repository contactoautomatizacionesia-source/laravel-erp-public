@php
    $min_withdraw = affiliateConfig('min_withdraw');
    $user_balance = $user->affiliateWallet ? $user->affiliateWallet->amount : 0;
    $withdraw_flag = true;
    $withdraw_tooltip = "";
    if($user_balance < $min_withdraw){
        $withdraw_tooltip = "Your balance less then minimum payout amount.";
        $withdraw_flag = false;
    }
@endphp
<div class="row">
    <div class="col-md-3 date-range-block">
        <div class="primary_input mb-15 date_range">
            <div class="primary_datepicker_input filter">
                <label class="primary_input_label" for="">{{__('affiliate.Select Date Range')}}</label>
                <div class="no-gutters input-right-icon">
                    <div class="col">
                        <div class="">
                            <input  readonly class="primary_input_field filter_date_input_field"  type="text" name="date_range_filter" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mt-30">
        <div class="d-flex">
            <button id="reset-date-filter" type="button" class="primary-btn mr-10 fix-gr-bg">{{__('affiliate.Filter')}}</button>
            <a type="button" href="{{route('affiliate.my_affiliate.index')}}" class="primary-btn  fix-gr-bg">{{__('shipping.reset')}}</a>
        </div>
    </div>
    <div class="col mt-30">
        <div class="d-flex justify-content-end">
            <a href="#" id="balance_transfer_btn" type="button" class="primary-btn mr-10 fix-gr-bg btn-line-height">{{__('affiliate.Balance Transfer To Wallet')}}</a>
            <a title="{{$withdraw_tooltip}}" id="withdraw_request_btn" type="button" href="#" class="primary-btn fix-gr-bg mr-10 btn-line-height {{!$withdraw_flag ? 'cursor-not-allowed' :''}}">{{__('affiliate.Withdraw Request')}}</a>
        </div>
    </div>
</div>
