@if(permissionCheck('affiliate.confirm_withdraw') && $row->status == 0)
<a class="primary-btn radius_30px text-white fix-gr-bg withdraw_confirm" href="#" data-id="{{$row->id}}">{{__('affiliate.Confirm')}}</a>
@endif
