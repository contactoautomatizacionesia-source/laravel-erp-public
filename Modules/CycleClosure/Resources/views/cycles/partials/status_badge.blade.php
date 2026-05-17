@php
    $map = [
        'running'          => 'badge_5',
        'needs_review'     => 'badge_3',
        'pre_validation'   => 'badge_3',
        'pending_approval' => 'badge_5',
        'processing'       => 'badge_5',
        'closed'           => 'badge_1',
        'cancelled'        => 'badge_2',
    ];
    $class = $map[$cycle->status] ?? 'badge_3';
@endphp
<span class="{{ $class }}">
    {{ __('cycleclosure::messages.status_' . $cycle->status) }}
</span>
