@props([
    'name',
    'label' => '',
    'value' => '',
    'placeholder' => '',
    'max' => 255,
    'min' => 0,
    'rows' => 3,
    'id' => $name
])

<div class="mb-3">
    @if($label)
        <label for="{{ $id }}" class="primary_input_label">{{ $label }}</label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        class="primary_textarea textarea-counter"
        placeholder="{{ $placeholder }}"
        data-max="{{ $max }}"
        data-min="{{ $min }}"
    >{{ old($name, $value) }}</textarea>

    <div class="d-flex justify-content-between mt-1 small">
        <span class="text-muted">
            Mínimo: {{ $min }} caracteres
        </span>
        <span class="char-count text-muted">
            0 / {{ $max }}
        </span>
    </div>

    <span id="error-{{ $id }}" class="error-text-counter d-none text-danger"></span>
</div>
