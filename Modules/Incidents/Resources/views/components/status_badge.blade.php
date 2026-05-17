@php
$map = [
    'pending'             => ['class' => 'badge_3',   'key' => 'status_pending'],
    'awaiting_statement'  => ['class' => 'badge_2',    'key' => 'status_awaiting'],
    'under_investigation' => ['class' => 'badge_6',    'key' => 'status_investigating'],
    'closed'              => ['class' => 'badge_1',   'key' => 'status_closed'],
    'voided'              => ['class' => 'badge_5', 'key' => 'status_voided'],
];
$info = $map[$status] ?? ['class' => 'badge_6', 'key' => 'status_' . $status];
@endphp
<span class="ml-2 {{ $info['class'] }}">{{ __('incidents::messages.' . $info['key']) }}</span>
