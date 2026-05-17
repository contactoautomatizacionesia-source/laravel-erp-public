@php
    $map = [
        'pending'   => ['label' => __('inventorycount::messages.status_pending'),   'class' => 'badge_3'],
        'correct'   => ['label' => __('inventorycount::messages.status_correct'),   'class' => 'badge_1'],
        'incorrect' => ['label' => __('inventorycount::messages.status_incorrect'), 'class' => 'badge_2'],
        'closed'    => ['label' => __('inventorycount::messages.status_closed'),    'class' => 'badge_5'],
    ];
    $item = $map[$row->status] ?? ['label' => $row->status, 'class' => 'badge-secondary'];
@endphp
<span class="{{ $item['class'] }}">{{ $item['label'] }}</span>
