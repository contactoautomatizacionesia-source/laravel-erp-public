
<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false"> {{__('common.select')}}
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
        @if(permissionCheck('affiliate.user.show'))
        <a href="{{route('affiliate.user.show',$row->id)}}" class="dropdown-item" type="button">{{__('common.details')}}</a>
        @endif
        @if($row->accept_affiliate_request == 0)
            @if(permissionCheck('affiliate.users.approved'))
            <a class="user_confirm dropdown-item" href="#" data-id="{{$row->id}}">{{__('affiliate.approve')}}</a>
            @endif
        @else
            @if(permissionCheck('affiliate.users.disable_enable'))
            <a href="" class="dropdown-item user_disable" data-id="{{$row->id}}">@if($row->accept_affiliate_request == 2) {{__('affiliate.enable')}} @else {{__('affiliate.disable')}} @endif</a>
            @endif
        @endif
    </div>
</div>