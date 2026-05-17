@props(['id', 'name', 'field', 'required' => false, 'accept' => null, 'help' => null, 'multiple' => false ])

@php
    $error_container = 'parsley_'.$name.'_error';
@endphp

<div class="primary_input mb-25">
    <label class="primary_input_label {{ $required ? 'required' : '' }}" for="{{ $name.'_button' }}">
        {{ $field }}
        @if($accept)
        <small>({{ $accept }}) </small>
        @endif
        <x-backEnd.help :help="$help" />
    </label>
    <div class="primary_file_uploader">
        <input class="primary-input input-placeholder" type="text" id="{{ $id.'_placeholder' }}" placeholder="{{ __('common.no_file_selected') }}" readonly="">
        <span id="{{ $error_container }}"></span>
        <button class="" type="button">
            <label class="primary-btn small fix-gr-bg" for="{{ $id }}">{{ __('common.browse') }}</label>
            <input type="file" class="d-none input-file" name="{{ $name }}" id="{{ $id }}" accept="{{ $accept }}" {{ $required ? 'required' : '' }} {{ $multiple ? 'multiple' : '' }}>
        </button>
    </div>
    <div id="{{ $id }}_file_list" class="file-list mt-2"></div>

</div>

