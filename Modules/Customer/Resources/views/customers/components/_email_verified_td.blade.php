@if (empty($customer->email))
    -
@elseif ((int) $customer->is_verified === 1)
    <span class="badge_1">{{ __('common.yes') }}</span>
@else
    <span class="badge_4">{{ __('common.no') }}</span>
@endif
