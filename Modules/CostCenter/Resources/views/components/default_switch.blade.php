@php
    $canToggleDefault = $table !== 'deleted' && (int) $row->status === 1;
@endphp

<label class="switch_toggle">
    <input
        type="checkbox"
        data-id="{{ $row->id }}"
        @if($canToggleDefault)
            class="default_toggle"
        @else
            disabled
        @endif
        {{ (int) $row->is_default === 1 ? 'checked' : '' }}
    >
    <div class="slider round"></div>
</label>
