@if($status === 'dispatched')
    <span class="badge_3">{{ __('costcenter::inventory.in_transit') }}</span>
@elseif($status === 'received')
    <span class="badge_1">{{ __('costcenter::inventory.received_total') }}</span>
@elseif($status === 'received_with_discrepancies')
    <span class="badge_2 text-white">{{ __('costcenter::inventory.received_with_novelty') }}</span>
@else
    <span class="badge badge-secondary">{{ __('common.unknown') }}</span>
@endif