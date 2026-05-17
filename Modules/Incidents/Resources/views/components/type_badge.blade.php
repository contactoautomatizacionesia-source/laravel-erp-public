@php
$map = [
    'transfer'        => 'type_transfer',
    'inventory_count' => 'type_inventory_count',
];
$key = $map[$type] ?? 'type_' . $type;
@endphp
<span class="badge_1">{{ __('incidents::messages.' . $key) }}</span>
