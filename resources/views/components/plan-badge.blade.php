@php
    $label = $label ?? '-';
    $svgSize = $svgSize ?? 'svg-sm';
    $color = $color ?? null;
    $icon = $icon ?? null;
    $isNoPlan = $label === __('common.no_plan');

    $accent = $isNoPlan ? '#9ca3af' : ($color ?: '#f59e0b');
    $bg = hexToRgba($accent, 0.15);
    $text = darkenColor($accent, 0.45);
@endphp

<span {{ $attributes->merge(['class' => 'lif-badge']) }} style="background: {{ $bg }}; color: {{ $text }};">
    @if(!empty($icon))
        <span class="svg-icon-plan {{$svgSize}}">{!! $icon !!}</span>
    @endif
    {{ $label }}
</span>
