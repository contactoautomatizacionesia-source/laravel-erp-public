{{--
    Props:
        name        — nombre del campo (se envía como name[])
        label       — etiqueta visible (opcional)
        options     — array ['value'=>'label'] o [['value'=>x,'label'=>y],...]
        selected    — valores preseleccionados (array o scalar)
        required    — si el campo es obligatorio
        disabled    — deshabilitar control
        help        — texto de ayuda debajo del campo
        id          — id HTML (se genera automáticamente si se omite)
        placeholder — texto cuando no hay selección
        maxHeight   — altura máxima del dropdown
--}}
@props([
    'name',
    'label'       => null,
    'options'     => [],
    'selected'    => [],
    'required'    => false,
    'disabled'    => false,
    'help'        => null,
    'id'          => null,
    'placeholder' => null,
    'maxHeight'   => '220px',
])
@php
    $fieldId = $id ?? 'ms_' . Str::replace(['[', ']', '.'], ['_', '', '_'], $name);

    // Normalizar options a [['value'=>..., 'label'=>...], ...]
    $normalizedOptions = [];
    foreach ($options as $k => $v) {
        if (is_array($v) && isset($v['value'])) {
            $normalizedOptions[] = $v;
        } else {
            $normalizedOptions[] = ['value' => $k, 'label' => $v];
        }
    }

    $selectedValues = array_map('strval', is_array($selected) ? $selected : ($selected !== null && $selected !== '' ? [$selected] : []));
@endphp

<div class="ign-multiselect-wrapper form-group"
    data-id="{{ $fieldId }}"
    data-name="{{ $name }}"
    data-required="{{ $required ? 'true' : 'false' }}"
    data-placeholder="{{ $placeholder ?? __('common.select_options') }}">

    @if($label)
        <label class="primary_input_label" for="{{ $fieldId }}_search">
            {{ $label }}
            @if($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    {{-- Select nativo: listbox semántico accesible + envío del formulario --}}
    <select multiple
        class="ign-ms-native-select"
        id="{{ $fieldId }}_listbox"
        name="{{ $name }}"
        tabindex="-1"
        style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;"
        {{ $disabled ? 'disabled' : '' }}>
        @foreach($normalizedOptions as $opt)
            <option value="{{ $opt['value'] }}" {{ in_array((string)$opt['value'], $selectedValues) ? 'selected' : '' }}>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    {{-- Control visible --}}
    <div class="ign-ms-control {{ $disabled ? 'ign-ms-disabled' : '' }} {{ $required ? 'ign-ms-required' : '' }}"
        id="{{ $fieldId }}"
        tabindex="{{ $disabled ? -1 : 0 }}">

        <div class="ign-ms-chips-area">
            <div class="ign-ms-chips" id="{{ $fieldId }}_chips"></div>
            <input type="text"
                class="ign-ms-search"
                id="{{ $fieldId }}_search"
                role="combobox"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="{{ $fieldId }}_listbox"
                autocomplete="off"
                {{ $disabled ? 'disabled' : '' }}
                placeholder="{{ count($selectedValues) === 0 ? ($placeholder ?? __('common.select_options')) : '' }}">
        </div>

        <span class="ign-ms-arrow" aria-hidden="true"><i class="ti-angle-down"></i></span>
    </div>

    {{-- Dropdown visual (decorativo, sin roles ARIA) --}}
    <div class="ign-ms-dropdown" id="{{ $fieldId }}_dropdown" aria-hidden="true" style="max-height:{{ $maxHeight }};display:none;">
        <div class="ign-ms-options" id="{{ $fieldId }}_options">
            @foreach($normalizedOptions as $opt)
                <div class="ign-ms-option {{ in_array((string)$opt['value'], $selectedValues) ? 'selected' : '' }}"
                    data-value="{{ $opt['value'] }}">
                    <span class="ign-ms-check"><i class="ti-check"></i></span>
                    <span class="ign-ms-option-label">{{ $opt['label'] }}</span>
                </div>
            @endforeach
            <div class="ign-ms-empty" style="display:none;">{{ __('common.no_results') }}</div>
        </div>
    </div>

    @if($help)
        <span class="primary_input_help">{{ $help }}</span>
    @endif

    {{-- Input fantasma para validación HTML5 required --}}
    @if($required)
        <input type="text"
            class="ign-ms-validator"
            style="opacity:0;height:0;width:0;position:absolute;pointer-events:none;"
            tabindex="-1"
            id="{{ $fieldId }}_validator">
    @endif

    {{-- Inicialización de este campo --}}
    <script>
    (function() {
        var _opts = @json($normalizedOptions);
        var _sel  = @json($selectedValues);
        var _id   = '{{ $fieldId }}';

        function doInit() {
            var el = document.querySelector('.ign-multiselect-wrapper[data-id="' + _id + '"]');
            if (el && window.ignMultiselect) {
                window.ignMultiselect.init(el, _opts, _sel);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', doInit);
        } else if (window.ignMultiselect) {
            doInit();
        } else {
            var attempts = 0;
            var poll = setInterval(function() {
                attempts++;
                if (window.ignMultiselect) { clearInterval(poll); doInit(); }
                else if (attempts > 50) clearInterval(poll);
            }, 100);
        }
    })();
    </script>
</div>

{{-- JS del motor — se incluye una sola vez por página --}}
@once
@push('scripts')
@include('components.admin.multiselect-engine')
@endpush
@endonce
