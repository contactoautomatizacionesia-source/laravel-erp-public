@props([
    'name',
    'id',
    'label',
    'checked'  => false,
    'disabled' => false,
    'value'    => '1',
    'hint'     => null,
])

<div class="form-group">
    <span class="primary_input_label" id="{{ $id }}_label">{{ $label }}</span>
    <label class="switch_toggle mt-2" for="{{ $id }}">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ $value }}"
            aria-labelledby="{{ $id }}_label"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
        >
        <span class="slider round" aria-hidden="true"></span>
    </label>
    @if($hint)
        <span class="primary_input_help">{{ $hint }}</span>
    @endif
</div>
