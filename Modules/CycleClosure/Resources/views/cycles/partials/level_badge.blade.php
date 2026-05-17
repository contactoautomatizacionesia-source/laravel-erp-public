@php
    $map = [
        'info'    => 'badge_5',
        'warning' => 'badge_3',
        'error'   => 'badge_2',
        'success' => 'badge_1',
    ];
    $class = $map[$log->level] ?? 'badge_5';
@endphp
<span class="{{ $class }}">{{ ucfirst($log->level) }}</span>
