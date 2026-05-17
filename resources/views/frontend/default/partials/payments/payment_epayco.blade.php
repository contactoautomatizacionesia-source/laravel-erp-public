<form action="{{route('frontend.order_payment')}}" method="post" class="epayco_form_payment d-none">
    @csrf
    <input type="hidden" name="method" value="EPAYCO">
    <input type="hidden" name="purpose" value="order_payment">
    <input type="hidden" name="amount" value="{{$total_amount - $coupon_am}}">

    <button type="submit" class="btn_1 order_submit_btn epayco_btn d-none">{{ __('defaultTheme.process_to_payment') }}</button>
</form>
