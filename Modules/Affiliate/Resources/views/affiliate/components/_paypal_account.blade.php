<div class="row mt-20">
    <div class="col-lg-12">
        <div class="white-box">
            <div class="add-visitor">
                {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.add_or_update_paypal_account',
                       'method' => 'POST']) }}
                <div class="row">
                    <h3 class="mb-30">
                        @if(isset($paypal_account))
                            {{__('affiliate.Update Paypal Account For Withdraw Commissions From System')}}
                        @else
                            {{__('affiliate.Add Paypal Account For Withdraw Commissions From System')}}
                        @endif
                    </h3>

                    <div class="col-lg-12">
                        <div class="primary_input mb-15">
                            <label class="primary_input_label" for="paypal_account"> {{__('affiliate.Paypal Account')}} <span class="required_mark_theme">*</span> </label>
                            <input  class="primary_input_field" name="paypal_account" id="paypal_account" placeholder="{{__('affiliate.Paypal Account')}}" type="text" value="{{isset($paypal_account) ? $paypal_account :''}}">
                            <span class="text-danger">{{$errors->first('paypal_account')}}</span>
                        </div>
                    </div>

                    <div class="col-lg-12 text-center mtr-10">
                        <button class="primary-btn btn-sm fix-gr-bg submit">
                            @if(isset($paypal_account))
                                {{__('affiliate.Update')}}
                            @else
                                {{__('affiliate.Add')}}
                            @endif
                        </button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
