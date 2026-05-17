<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false"> {{__('common.select')}}
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
        @if(isset($type) && $type == 'deleted_lists')
            @if (permissionCheck('customer.restore'))
                <a data-value="{{route('customer.restore', $customer->id)}}" class="dropdown-item restore_customer"
                    type="button">
                    {{__('common.restore')}}
                </a>
            @endif
        @elseif(isset($type) && $type == 'pending_approval_lists')
            @if (permissionCheck('customer.show_details'))
                <a href="{{route('customer.show_details', $customer->id)}}" class="dropdown-item"
                    type="button">{{__('common.details')}}</a>
            @endif
            @if (permissionCheck('admin.customer.edit'))
                <a href="#" class="dropdown-item update_approval_status"
                    data-id="{{ $customer->id }}" data-status="{{ \App\Models\User::APPROVAL_STATUS_APPROVED }}"
                    data-current-status="{{ $customer->approval_status ?? \App\Models\User::APPROVAL_STATUS_APPROVED }}" type="button">
                    {{ __('common.approve') }}
                </a>
                <a href="#" class="dropdown-item update_approval_status"
                    data-id="{{ $customer->id }}" data-status="{{ \App\Models\User::APPROVAL_STATUS_REJECTED }}"
                    data-current-status="{{ $customer->approval_status ?? \App\Models\User::APPROVAL_STATUS_APPROVED }}" type="button">
                    {{ __('common.reject') }}
                </a>
            @endif
        @elseif(isset($type) && $type == 'rejected_approval_lists')
            @if (permissionCheck('customer.show_details'))
                <a href="{{route('customer.show_details', $customer->id)}}" class="dropdown-item"
                    type="button">{{__('common.details')}}</a>
            @endif
            @if (permissionCheck('admin.customer.edit'))
                <a href="#" class="dropdown-item update_approval_status"
                    data-id="{{ $customer->id }}" data-status="{{ \App\Models\User::APPROVAL_STATUS_APPROVED }}"
                    data-current-status="{{ $customer->approval_status ?? \App\Models\User::APPROVAL_STATUS_APPROVED }}" type="button">
                    {{ __('common.approve') }}
                </a>
            @endif
        @else
            @if (permissionCheck('customer.show_details'))
                <a href="{{route('customer.show_details', $customer->id)}}" class="dropdown-item"
                    type="button">{{__('common.details')}}</a>
            @endif
            @if (permissionCheck('admin.customer.edit'))
                <a href="{{route('admin.customer.edit', $customer->id)}}" class="dropdown-item"
                    type="button">{{__('common.edit')}}</a>
            @endif
            @if (permissionCheck('admin.customer.edit'))
                <a data-id="{{$customer->id}}" class="dropdown-item change_plan_customer"
                    type="button">
                    {{__('common.change_plan')}}
                </a>
            @endif
            @if (permissionCheck('admin.customer.destroy'))
                <a data-value="{{route('admin.customer.destroy', $customer->id)}}" class="dropdown-item delete_customer"
                    type="button">
                    {{__('common.delete')}}
                </a>
            @endif
        @endif
    </div>
</div>
