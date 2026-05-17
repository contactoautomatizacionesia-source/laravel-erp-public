@if($row->accept_affiliate_request == 0)
    <span class="badge_3">{{__('affiliate.Pending')}}</span>
@elseif($row->accept_affiliate_request == 2)
    <span class="badge_2">{{__('affiliate.disable')}}</span>
@else
    <span class="badge_1">{{__('affiliate.Active')}}</span>
@endif
