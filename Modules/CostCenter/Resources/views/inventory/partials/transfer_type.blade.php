@if ($source_type === 'main' && $destination_type === 'cost_center')
    <span class="badge_1">{{ __('costcenter::inventory.transfer_type_main_to_center') }}</span>
@elseif ($source_type === 'cost_center' && $destination_type === 'main')
    <span class="badge_2">{{ __('costcenter::inventory.transfer_type_center_to_main') }}</span>
@elseif ($source_type === 'cost_center' && $destination_type === 'cost_center')
    <span class="badge_3">{{ __('costcenter::inventory.transfer_type_center_to_center') }}</span>
@else
    <span class="badge_4">{{ __('costcenter::inventory.transfer_type_unknown') }}</span>
@endif