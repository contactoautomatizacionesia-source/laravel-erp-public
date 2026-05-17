@if(permissionCheck('customer.update_active_status'))
    @if(manualActivation() && $customer->approval_status != \App\Models\User::APPROVAL_STATUS_APPROVED)
        @if($customer->approval_status == \App\Models\User::APPROVAL_STATUS_PENDING)
            <span class="badge_4">Pendiente</span>
        @elseif($customer->approval_status == \App\Models\User::APPROVAL_STATUS_REJECTED)
            <span class="badge_2">Rechazada</span>
        @endif
    @else
        <label class="switch_toggle" for="active_checkbox{{ $customer->id }}">
            <input type="checkbox" id="active_checkbox{{ $customer->id }}" @if ($customer->is_active == 1) checked @endif value="{{ $customer->id }}" @if (!permissionCheck('customer.update_active_status')) disabled @endif class="update_active_status" data-id="{{ $customer->id }}">
            <div class="slider round"></div>
        </label>
    @endif
@endif
