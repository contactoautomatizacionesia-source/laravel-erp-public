@if($setting->is_active)
    <span class="badge_1">{{ __('cycleclosure::messages.setting_status_active') }}</span>
@else
    <span class="badge_3">{{ __('cycleclosure::messages.setting_status_superseded') }}</span>
@endif
