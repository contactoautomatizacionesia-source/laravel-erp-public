@if($row->status)
    <span class="badge_1">{{ __('common.active') }}</span>
@else
    <span class="badge_2">{{ __('common.inactive') }}</span>
@endif
