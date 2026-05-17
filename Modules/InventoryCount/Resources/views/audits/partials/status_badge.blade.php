@php
    $map = [
        'pending'  => ['label' => __('inventorycount::messages.audit_pending'),  'class' => 'badge_3'],
        'rejected' => ['label' => __('inventorycount::messages.audit_rejected'), 'class' => 'badge_2'],
        'approved' => ['label' => __('inventorycount::messages.audit_approved'), 'class' => 'badge_1'],
    ];
    $item = $map[$row->status] ?? ['label' => $row->status, 'class' => 'badge-secondary'];
@endphp
<span class="{{ $item['class'] }}">{{ $item['label'] }}</span>
