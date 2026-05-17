@extends('backEnd.master')
@section('styles')

<link rel="stylesheet" href="{{asset(asset_path('modules/attendance/css/style.css'))}}" />
<style>
    .primary-btn.primary-circle {

        padding: 9px !important;
    }

    tr.new_row_unsave td{
        background-color: #bababa33;
    }
    tr.new_row_unsave td:last-child{
        border-radius: 0 20px 20px  0 ;
        
    }
    tr.new_row_unsave td:first-child{
        border-radius: 20px 0 0 20px  ;
        
    }

    .sticky-save{
        position: sticky;
        bottom: 15px;
        background: #ffffffa3;
        backdrop-filter: blur(1px);
        padding: 10px 0;
        z-index: 10;
        border-radius: 10px;
    }
</style>
@endsection
@section('mainContent')

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                <div class="col-lg-12 mb-3">
                    <div class="white_box_30px box_shadow_white">
                        <form class="" action="{{ route('holidays.store') }}" method="POST" id="holidays-form">@csrf
                            <div class="row">

                                <div class="col-lg-12">
                                    <div class="primary_input mb-15">
                                        <label class="primary_input_label" for="">{{ __('common.year') }} <span class="text-danger">*</span></label>
                                        <div class="primary_datepicker_input">
                                            <div class="no-gutters input-right-icon">
                                                <div class="col">
                                                    <div class="">
                                                        <input placeholder="{{ __('common.year') }}"
                                                               class="primary_input_field primary-input datepicker form-control"
                                                               type="text" id="year"
                                                               name="year" value="{{ getNumberTranslate(date('Y')) }}"
                                                               autocomplete="off" required>
                                                    </div>
                                                </div>
                                                <button class="btn-date" data-id="#year" type="button">
                                                    <i class="ti-calendar"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <span class="text-danger">{{$errors->first('date')}}</span>
                                    </div>
                                </div>
                            </div>
                            @if (permissionCheck('last.year.data'))
                                <div class="row">
                                    <div class="col-lg-12 text-center  mb-2 mt-3">
                                        <a id="copy_previous_year_btn" href="{{route('last.year.data', date('Y'))}}"
                                           class="btn-toolkit btn-secondary">{{__('hr.copy_previous_year_settings')}}</a>
                                    </div>
                                </div>
                            @endif
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table ">

                                    <!-- table-responsivaae -->
                                    <div class="table-responsiv">
                                        <div class="main-title d-md-flex">
                                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">
                                                {{ __('hr.create_holiday') }}
                                            </h3>
                                        </div>
                                        <table class="table">
                                            <thead><th></th></thead>
                                            <tbody class="holiday_table">
                                            <tr class="template_row">
                                                <td>
                                                    <div class="primary_input mb-15 min-width-150">
                                                        <label class="primary_input_label" for="">
                                                            {{__('hr.holiday_name')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" name="holiday_name[]" id="holiday_row_1_name"
                                                               class="primary_input_field"
                                                               placeholder="{{__('hr.holiday_name')}}" value="">
                                                        <span
                                                            class="text-danger">{{$errors->first('holiday_name')}}</span>
                                                    </div>
                                                </td>
                                                <td id="holiday_row_1_type">
                                                    <div class="primary_input mb-15 min-width-150">
                                                        <label class="primary_input_label"
                                                               for="">{{__('common.select_type')}} <span class="text-danger">*</span></label>
                                                        <select class="primary_select mb-15 type"
                                                                name="type[]">
                                                            <option value="0">{{__('common.single_day')}}</option>
                                                            <option value="1">{{__('common.multiple_day')}}</option>
                                                        </select>
                                                        <span class="text-danger">{{$errors->first('type')}}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="single_date">
                                                        <div class="primary_input mb-15 min-width-150">
                                                            <label class="primary_input_label" for="">
                                                                {{ __('common.date') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="primary_datepicker_input">
                                                                <div class="no-gutters input-right-icon">
                                                                    <div class="col">
                                                                        <div class="">
                                                                            <input placeholder="{{ __('common.date') }}" id="single_date"
                                                                                   class="primary_input_field primary-input date form-control"
                                                                                   type="text"
                                                                                   name="date[]"
                                                                                   value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}"
                                                                                   autocomplete="off">
                                                                        </div>
                                                                    </div>
                                                                    <button class="btn-date" data-id="#single_date" type="button">
                                                                        <i class="ti-calendar"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span class="text-danger">{{$errors->first('date')}}</span>
                                                        </div>
                                                    </div>
                                                    <div class="multiple_date d-none">
                                                        <div class="primary_input mb-15 min-width-150">
                                                            <label class="primary_input_label"
                                                                   for="">{{ __('common.start_date') }}
                                                                <span class="text-danger">*</span></label>
                                                            <div class="primary_datepicker_input">
                                                                <div class="no-gutters input-right-icon">
                                                                    <div class="col">
                                                                        <div class="">
                                                                            <input placeholder="{{ __('common.date') }}" id="first_row_start_date"
                                                                                   class="primary_input_field primary-input date form-control"
                                                                                   type="text"
                                                                                   name="start_date[]"
                                                                                   value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}"
                                                                                   autocomplete="off">
                                                                        </div>
                                                                    </div>
                                                                    <button class="btn-date" data-id="#first_row_start_date" type="button">
                                                                        <i class="ti-calendar"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span
                                                                class="text-danger">{{$errors->first('start_date')}}</span>
                                                        </div>
                                                        <div class="primary_input mb-15 min-width-150">
                                                            <label class="primary_input_label" for="">
                                                                {{ __('common.end_date') }}
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <div class="primary_datepicker_input">
                                                                <div class="no-gutters input-right-icon">
                                                                    <div class="col">
                                                                        <div class="">
                                                                            <input
                                                                                placeholder="{{ __('common.date') }}" id="end_row_start_date"
                                                                                class="primary_input_field primary-input date form-control"
                                                                                type="text"
                                                                                name="end_date[]"
                                                                                value="{{ \Carbon\Carbon::now()->format('m/d/Y') }}"
                                                                                autocomplete="off"
                                                                            >
                                                                        </div>
                                                                    </div>
                                                                    <button class="btn-date" data-id="#end_row_start_date" type="button">
                                                                        <i class="ti-calendar"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <span
                                                                class="text-danger">{{$errors->first('end_date')}}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a
                                                        class="primary-btn primary-circle fix-gr-bg text-white"
                                                        id="add_row_btn"
                                                        href=""
                                                    >
                                                        <i class="ti-plus"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @if (session()->has('holidays'))
                                                @php
                                                    $data = session()->get('holidays');
                                                @endphp
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="main-title d-md-flex mb-3">
                                                            <h3 class="mb-0">
                                                                {{ __('hr.list_holidays') }}
                                                            </h3>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @foreach ($data['holiday_name'] as $key=> $holiday)
                                                    <tr class="add_row">
                                                        <td>
                                                            <div class="primary_input mb-15 min-width-150">
                                                                <label class="primary_input_label" for="">
                                                                    {{__('hr.holiday_name')}}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" name="holiday_name[]"
                                                                       class="primary_input_field"
                                                                       placeholder="{{__('hr.holiday_name')}}"
                                                                       value="{{$data['holiday_name'][$key]}}" required>
                                                                <span
                                                                    class="text-danger">{{$errors->first('holiday_name')}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="primary_input mb-15 min-width-150">
                                                                <label class="primary_input_label"
                                                                       for="">{{__('common.select_type')}} <span class="text-danger">*</span></label>
                                                                <select class="primary_select mb-15 type" name="type[]">
                                                                    <option
                                                                        value="0" {{$data['type'][$key] == 0 ? 'selected' : ''}}>{{__('common.single_day')}}</option>
                                                                    <option
                                                                        value="1" {{$data['type'][$key] == 1 ? 'selected' : ''}}>{{__('common.multiple_day')}}</option>
                                                                </select>
                                                                <span
                                                                    class="text-danger">{{$errors->first('type')}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="single_date @if ($data['type'][$key] == 1) d-none @endif">
                                                                <div class="primary_input mb-15 min-width-150">
                                                                    <label class="primary_input_label" for="">
                                                                        {{ __('common.date') }}
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <div class="primary_datepicker_input">
                                                                        <div class="no-gutters input-right-icon">
                                                                            <div class="col">
                                                                                <div class="">
                                                                                    <input
                                                                                        placeholder="{{ __('common.date') }}"
                                                                                        class="primary_input_field primary-input date form-control"
                                                                                        type="text"
                                                                                        id="holiday_date_{{$data['holiday_ids'][$key]}}"
                                                                                        name="date[]"
                                                                                        value="{{ $data['type'][$key] == 0 ? \Carbon\Carbon::parse($data['date'][$key])->format('m/d/Y') : '' }}"
                                                                                        autocomplete="off"
                                                                                    >
                                                                                </div>
                                                                            </div>
                                                                            <button class="btn-date" data-id="#holiday_date_{{$data['holiday_ids'][$key]}}" type="button">
                                                                                <i class="ti-calendar"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <span
                                                                        class="text-danger">{{$errors->first('date')}}</span>
                                                                </div>
                                                            </div>

                                                            <div class="multiple_date @if ($data['type'][$key] == 0) d-none @endif">
                                                                <div class="primary_input mb-15 min-width-150">
                                                                    <label class="primary_input_label" for="">
                                                                        {{ __('common.start_date') }}
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <div class="primary_datepicker_input">
                                                                        <div class="no-gutters input-right-icon">
                                                                            <div class="col">
                                                                                <div class="">
                                                                                    <input
                                                                                        placeholder="{{ __('common.date') }}"
                                                                                        class="primary_input_field primary-input date form-control"
                                                                                        type="text"
                                                                                        name="start_date[]"
                                                                                        id="holiday_start_date_{{$data['holiday_ids'][$key]}}"
                                                                                        value="{{ !empty($data['start_date'][$key]) ? \Carbon\Carbon::parse($data['start_date'][$key])->format('m/d/Y') : \Carbon\Carbon::now()->format('m/d/Y') }}"
                                                                                        autocomplete="off"
                                                                                    >
                                                                                </div>
                                                                            </div>
                                                                            <button class="btn-date" data-id="#holiday_start_date_{{$data['holiday_ids'][$key]}}" type="button">
                                                                                <i class="ti-calendar"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <span
                                                                        class="text-danger">{{$errors->first('start_date')}}</span>
                                                                </div>
                                                                <div class="primary_input mb-15 min-width-150">
                                                                    <label class="primary_input_label"
                                                                           for="">{{ __('common.end_date') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <div class="primary_datepicker_input">
                                                                        <div class="no-gutters input-right-icon">
                                                                            <div class="col">
                                                                                <div class="">
                                                                                    <input
                                                                                        placeholder="{{ __('common.date') }}"
                                                                                        class="primary_input_field primary-input date form-control"
                                                                                        type="text"
                                                                                        name="end_date[]"
                                                                                        id="holiday_end_date_{{$data['holiday_ids'][$key]}}"
                                                                                        value="{{ !empty($data['end_date'][$key]) ? \Carbon\Carbon::parse($data['end_date'][$key])->format('m/d/Y') : \Carbon\Carbon::now()->format('m/d/Y') }}"
                                                                                        autocomplete="off"
                                                                                    >
                                                                                </div>
                                                                            </div>
                                                                            <button class="btn-date" data-id="#holiday_end_date_{{$data['holiday_ids'][$key]}}" type="button">
                                                                                <i class="ti-calendar"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-danger">{{$errors->first('end_date')}}</span>
                                                            </div>
                                                        </td>
                                                        <td><a href="javascript:void(0)" class="delete_row"><i class="ti-trash"></i></a></td>
                                                    </tr>
                                                @endforeach

                                            @else
                                                <tr>
                                                    <td colspan="4" style="padding: 0; border: none;">
                                                        <div class="main-title d-md-flex mb-2 mt-2">
                                                            <h3 class="mb-0">{{ __('hr.list_holidays') }}</h3>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @foreach ($holidays as $key => $holiday)
                                                    <tr class="add_row">
                                                        <td>
                                                            <input type="hidden" name="holiday_ids[]" value="{{ $holiday->id }}">
                                                            <div class="primary_input mb-15">
                                                                <label class="primary_input_label" for="">
                                                                    {{__('hr.holiday_name')}}
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" name="holiday_name[]"
                                                                       class="primary_input_field"
                                                                       placeholder="{{__('hr.holiday_name')}}"
                                                                       value="{{$holiday->name}}" required>
                                                                <span
                                                                    class="text-danger">{{$errors->first('holiday_name')}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="primary_input mb-15">
                                                                <label class="primary_input_label"
                                                                       for="">{{__('common.select_type')}} <span class="text-danger">*</span></label>
                                                                <select class="primary_select mb-15 type" name="type[]">
                                                                    <option
                                                                        value="0" {{$holiday->type == 0 ? 'selected' : ''}}>{{__('common.single_day')}}</option>
                                                                    <option
                                                                        value="1" {{$holiday->type == 1 ? 'selected' : ''}}>{{__('common.multiple_day')}}</option>
                                                                </select>
                                                                <span
                                                                    class="text-danger">{{$errors->first('type')}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="single_date @if ($holiday->type == 1) d-none @endif">
                                                                <div class="primary_input mb-15">
                                                                    <label class="primary_input_label"
                                                                           for="">{{ __('common.date') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <div class="primary_datepicker_input">
                                                                        <div class="no-gutters input-right-icon">
                                                                            <div class="col">
                                                                                <div class="">
                                                                                    <input
                                                                                        placeholder="{{ __('common.date') }}"
                                                                                        class="primary_input_field primary-input date form-control"
                                                                                        type="text"
                                                                                        name="date[]"
                                                                                        id="holiday_date_{{$holiday->id}}"
                                                                                        value="{{ $holiday->type == 0 ? \Carbon\Carbon::parse($holiday->date)->format('m/d/Y') : '' }}"
                                                                                        autocomplete="off"
                                                                                    >
                                                                                </div>
                                                                            </div>
                                                                            <button class="btn-date" data-id="#holiday_date_{{$holiday->id}}" type="button">
                                                                                <i class="ti-calendar"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <span
                                                                        class="text-danger">{{$errors->first('date')}}</span>
                                                                </div>
                                                            </div>
                                                            @php
                                                                $start_date = '';
                                                                $end_date = '';
                                                                $date = [];
                                                                if ($holiday->type == 1) {
                                                                    $date = explode(',', $holiday->date);
                                                                    $start_date = trim($date[0]);
                                                                    $end_date = trim($date[1]);
                                                                }
                                                            @endphp
                                                            <div class="multiple_date @if ($holiday->type == 0) d-none @endif">
                                                                <div class="primary_input mb-15">
                                                                    <label class="primary_input_label"
                                                                           for="">{{ __('common.start_date') }}
                                                                        <span class="text-danger">*</span></label>
                                                                    <div class="primary_datepicker_input">
                                                                        <div class="no-gutters input-right-icon">
                                                                            <div class="col">
                                                                                <div class="">
                                                                                    <input
                                                                                        placeholder="{{ __('common.date') }}"
                                                                                        class="primary_input_field primary-input date form-control"
                                                                                        type="text"
                                                                                        name="start_date[]"
                                                                                        id="holiday_start_{{$holiday->id}}"
                                                                                        value="{{ !empty($date) ? \Carbon\Carbon::parse(trim($date[0]))->format('m/d/Y') : \Carbon\Carbon::now()->format('m/d/Y') }}"
                                                                                        autocomplete="off"
                                                                                    >
                                                                                </div>
                                                                            </div>
                                                                            <button class="btn-date" data-id="#holiday_start_{{$holiday->id}}" type="button">
                                                                                <i class="ti-calendar"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <span
                                                                        class="text-danger">{{$errors->first('start_date')}}</span>
                                                                </div>
                                                                <div class="primary_input mb-15">
                                                                    <label class="primary_input_label" for="">
                                                                        {{ __('common.end_date') }}
                                                                        <span class="text-danger">*</span>
                                                                    </label>
                                                                    <div class="primary_datepicker_input">
                                                                        <div class="no-gutters input-right-icon">
                                                                            <div class="col">
                                                                                <div class="">
                                                                                    <input
                                                                                        placeholder="{{ __('common.date') }}"
                                                                                        class="primary_input_field primary-input date form-control"
                                                                                        type="text"
                                                                                        name="end_date[]"
                                                                                        id="holiday_end_{{$holiday->id}}"
                                                                                        value="{{ !empty($date) ? \Carbon\Carbon::parse(trim($date[1]))->format('m/d/Y') : \Carbon\Carbon::now()->format('m/d/Y') }}"
                                                                                        autocomplete="off"
                                                                                    >
                                                                                </div>
                                                                            </div>
                                                                            <button class="btn-date" data-id="#holiday_end_{{$holiday->id}}" type="button">
                                                                                <i class="ti-calendar"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-danger">{{$errors->first('end_date')}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a class="primary-btn primary-circle delete_row fix-gr-bg text-white"
                                                                   href="javascript:void(0)"> <i
                                                                        class="ti-trash"></i></a>
                                                        </td>
                                                    </tr>

                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if (permissionCheck('holidays.store'))
        <div class="sticky-save">
            <div class="row justify-content-center mt-2">
                <button type="submit" class=" btn-toolkit btn-primary btn-icon" id="save-holidays">
                    {{__('common.save_changes')}}
                </button>
            </div>
        </div>
        @endif
    </section>
@endsection
@push('scripts')
    <script>
        
        (function($){
            "use strict";

            var isAddingRow = false;

           

            $(document).ready(function() {
                $('#save-holidays').on('click', function(){
                    $('#holidays-form').submit();
                })

                $('form').on('submit', function() {
                    $('.template_row').find('input, select').prop('disabled', true);
                });

                $(".primary-input.datepicker").datepicker({
                    autoclose: true,
                    format: "yyyy",
                    viewMode: "years",
                    minViewMode: "years"
                });
                $(document).on('change', '#year', function(){
                    changeYear();
                });

                $(document).on('click', '#add_row_btn', function(event){
                    event.preventDefault();
                    if (isAddingRow) return false;
                    addRow();
                });

                $(document).on('change', '.type', function () {
                    let value = $(this).val();
                    var whichtr = $(this).closest("tr");
                    if (value == 0) {
                        whichtr.find($('.single_date')).removeClass('d-none');
                        whichtr.find($('.multiple_date')).addClass('d-none');
                    } else {
                        whichtr.find($('.single_date')).addClass('d-none');
                        whichtr.find($('.multiple_date')).removeClass('d-none');
                    }
                });

                $(document).on('click', '.delete_row', function () {
                    var whichtr = $(this).closest("tr");
                    whichtr.remove();
                });

                function changeYear() {
                    let year = $('#year').val();
                    $('#pre-loader').removeClass('d-none');
                    let baseUrl = $('#url').val();
                    let pre_year_route = baseUrl + "/attendance/last-year-data/" + year;
                    $('#copy_previous_year_btn').attr('href', pre_year_route);
                    $.ajax({
                        url: "{{route('add.row')}}",
                        method: "POST",
                        data: {
                            year: year,
                            _token: "{{csrf_token()}}",
                        },
                        success: function (result) {
                            $(".add_row").each(function (index, element) {
                                element.remove();
                            });
                            $(".holiday_table").append(result);
                            $('#pre-loader').addClass('d-none');
                        },
                        error: function(response) {
                            if(response.responseJSON.error){
                                toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                                $('#pre-loader').addClass('d-none');
                                return false;
                            }
                            $('#pre-loader').addClass('d-none');
                            toastr.error('{{ __("common.error_message") }}');
                        }
                    });
                }

                function addRow() {
                    var $template   = $('.template_row');
                    var holidayName = $template.find('input[name="holiday_name[]"]').val();
                    var type        = $template.find('select.type').val();
                    var singleDate  = $template.find('.single_date input[name="date[]"]').val();
                    var startDate   = $template.find('.multiple_date input[name="start_date[]"]').val();
                    var endDate     = $template.find('.multiple_date input[name="end_date[]"]').val();

                    if (!holidayName.trim()) {
                        toastr.error('{{ __("common.field_is_required", ["field" => __("hr.holiday_name")]) }}');
                        $template.find('input[name="holiday_name[]"]').focus();
                        return false;
                    }
                    if (type == 0 && !singleDate.trim()) {
                        toastr.error('{{ __("common.field_is_required", ["field" => __("common.date")]) }}');
                        return false;
                    }
                    if (type == 1 && !startDate.trim()) {
                        toastr.error('{{ __("common.field_is_required", ["field" => __("common.start_date")]) }}');
                        return false;
                    }
                    if (type == 1 && !endDate.trim()) {
                        toastr.error('{{ __("common.field_is_required", ["field" => __("common.end_date")]) }}');
                        return false;
                    }

                    var uid = 'row_' + Date.now();

                    var newRow = `
                        <tr class="add_row new_row_unsave">
                            <td>
                                <input type="hidden" name="holiday_ids[]" value="">
                                <div class="primary_input mb-15 min-width-150">
                                    <label class="primary_input_label">{{ __('hr.holiday_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="holiday_name[]"
                                        class="primary_input_field"
                                        placeholder="{{ __('hr.holiday_name') }}"
                                        value="${$('<div>').text(holidayName).html()}" required>
                                </div>
                            </td>
                            <td>
                                <div class="primary_input mb-15 min-width-150">
                                    <label class="primary_input_label">{{ __('common.select_type') }} <span class="text-danger">*</span></label>
                                    <select class="primary_select mb-15 type" name="type[]">
                                        <option value="0" ${type == 0 ? 'selected' : ''}>{{ __('common.single_day') }}</option>
                                        <option value="1" ${type == 1 ? 'selected' : ''}>{{ __('common.multiple_day') }}</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="single_date ${type == 1 ? 'd-none' : ''}">
                                    <div class="primary_input mb-15 min-width-150">
                                        <label class="primary_input_label">{{ __('common.date') }} <span class="text-danger">*</span></label>
                                        <div class="primary_datepicker_input">
                                            <div class="no-gutters input-right-icon">
                                                <div class="col">
                                                    <input placeholder="{{ __('common.date') }}"
                                                        class="primary_input_field primary-input date form-control"
                                                        type="text" name="date[]"
                                                        id="holiday_date_${uid}"
                                                        value="${singleDate}" autocomplete="off">
                                                </div>
                                                <button class="btn-date" data-id="#holiday_date_${uid}" type="button">
                                                    <i class="ti-calendar"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="multiple_date ${type == 0 ? 'd-none' : ''}">
                                    <div class="primary_input mb-15 min-width-150">
                                        <label class="primary_input_label">{{ __('common.start_date') }} <span class="text-danger">*</span></label>
                                        <div class="primary_datepicker_input">
                                            <div class="no-gutters input-right-icon">
                                                <div class="col">
                                                    <input placeholder="{{ __('common.date') }}"
                                                        class="primary_input_field primary-input date form-control"
                                                        type="text" name="start_date[]"
                                                        id="holiday_start_${uid}"
                                                        value="${startDate}" autocomplete="off">
                                                </div>
                                                <button class="btn-date" data-id="#holiday_start_${uid}" type="button">
                                                    <i class="ti-calendar"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="primary_input mb-15 min-width-150">
                                        <label class="primary_input_label">{{ __('common.end_date') }} <span class="text-danger">*</span></label>
                                        <div class="primary_datepicker_input">
                                            <div class="no-gutters input-right-icon">
                                                <div class="col">
                                                    <input placeholder="{{ __('common.date') }}"
                                                        class="primary_input_field primary-input date form-control"
                                                        type="text" name="end_date[]"
                                                        id="holiday_end_${uid}"
                                                        value="${endDate}" autocomplete="off">
                                                </div>
                                                <button class="btn-date" data-id="#holiday_end_${uid}" type="button">
                                                    <i class="ti-calendar"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a class="primary-btn primary-circle delete_row fix-gr-bg text-white" href="javascript:void(0)">
                                    <i class="ti-trash"></i>
                                </a>
                            </td>
                        </tr>`;

                    var $newRow = $(newRow);

                    var firstRow = $('.holiday_table .add_row').first();
                    if (firstRow.length) {
                        firstRow.before($newRow);
                    } else {
                        $('.template_row').after($newRow);
                    }

                    // Reinicializar todos los selects y solo los datepickers nuevos
                    $('select').niceSelect();
                    $newRow.find('.date').datepicker({ autoclose: true });

                    // Limpiar template
                    $template.find('input[name="holiday_name[]"]').val('');
                    $template.find('select.type').val('0').trigger('change');
                    $template.find('select.type').niceSelect('update');
                    $template.find('input[name="date[]"]').val('');
                    $template.find('input[name="start_date[]"]').val('');
                    $template.find('input[name="end_date[]"]').val('');
                }
            });
        })(jQuery);


    </script>
@endpush
