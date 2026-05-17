@php
    $styles = $planContext['current_plan']['styles'] ?? [];
    if (is_string($styles)) {
        $styles = json_decode($styles, true) ?? [];
    }
    $icon  = $styles['icon'] ?? null;
    $color = $styles['primaryColor'] ?? null;
    $label = $planContext['display_name'] ?? __('common.no_plan');
@endphp
<div class="d-flex align-items-center" style="font-size: 11px">
    <x-plan-badge
        :label="$label"
        :color="$color"
        :icon="$icon"
        svgSize="svg-xs"
    />
</div>
