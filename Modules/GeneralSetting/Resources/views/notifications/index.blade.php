@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('modules/generalsetting/css/style.css'))}}" />
@endsection
@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('common.system') }} {{ __('common.notification') }} {{ __('common.setting') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="QA_section QA_section_heading_custom check_box_table">
                    <div class="QA_table ">
                        <!-- table-responsive -->
                        <div class="">
                            <table class="table Crm_table_active3">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('common.sl') }}</th>
                                        <th scope="col">{{ __('hr.event') }}</th>
                                        <th scope="col" style="min-width: 200px">{{ __('common.type') }}</th>
                                        <th scope="col">{{ __('common.message') }}</th>
                                        <th scope="col">{{ __('common.action')  }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- shortby  -->
                                    </td>
                                    </tr>
                                    @foreach($notificationSettings as $notificationSetting)
                                        @if(!$notificationSetting->module or isModuleActive($notificationSetting->module))
                                        <tr>
                                            <th>{{ getNumberTranslate($loop->index +1) }}</th>
                                            <td style="white-space: normal; word-wrap: break-word; max-width: 350px;">
                                                @if(mb_strlen($notificationSetting->event) > 30)
                                                    <div class="subject-cell">
                                                        <span class="subject-short">{{ mb_substr($notificationSetting->event, 0, 30) }}<span class="subject-ellipsis">...</span></span>
                                                        <span class="subject-full d-none">{{ $notificationSetting->event }}</span>
                                                        <a href="javascript:void(0)" class="subject-toggle ml-1" style="color:var(--base_color); font-size:11px; white-space:nowrap;">
                                                            <i class="ti-plus"></i> {{ __('common.show_more') }}
                                                        </a>
                                                    </div>
                                                @else
                                                    {{ $notificationSetting->event }}
                                                @endif
                                            </td>
                                            <td>
                                                <label data-id="bg_option" class="margin-type primary_checkbox w-100 d-flex mr-12">
                                                    <input disabled  name="status" id="status" value="1"
                                                    @if (Str::contains($notificationSetting->type,'email')) checked @endif
                                                    type="checkbox">
                                                    <span class="checkmark"></span> &nbsp;{{__('common.email')}}
                                                </label>
                                                <label data-id="bg_option" class="margin-type primary_checkbox w-100 d-flex mr-12">
                                                    <input disabled  name="status" id="status" value="1"
                                                    @if (Str::contains($notificationSetting->type,'mobile')) checked @endif
                                                    type="checkbox">
                                                    <span class="checkmark"></span> &nbsp;{{__('common.mobile')}}
                                                </label>
                                                <label data-id="bg_option" class="margin-type primary_checkbox w-100 d-flex mr-12">
                                                    <input disabled  name="status" id="status" value="1"
                                                    @if (Str::contains($notificationSetting->type,'sms')) checked @endif
                                                    type="checkbox">
                                                    <span class="checkmark"></span> &nbsp;{{__('common.sms')}}
                                                </label>
                                                <label data-id="bg_option" class="margin-type primary_checkbox w-100 d-flex mr-12">
                                                    <input disabled  name="status" id="status" value="1"
                                                    @if (Str::contains($notificationSetting->type,'system')) checked @endif
                                                    type="checkbox">
                                                    <span class="checkmark"></span> &nbsp;{{__('common.system')}}
                                                </label>
                                            </td>
                                            <td style="white-space: normal; word-wrap: break-word; max-width: 350px;">
                                                @if(mb_strlen($notificationSetting->message) > 30)
                                                    <div class="subject-cell">
                                                        <span class="subject-short">{{ mb_substr($notificationSetting->message, 0, 30) }}<span class="subject-ellipsis">...</span></span>
                                                        <span class="subject-full d-none">{{ $notificationSetting->message }}</span>
                                                        <a href="javascript:void(0)" class="subject-toggle ml-1" style="color:var(--base_color); font-size:11px; white-space:nowrap;">
                                                            <i class="ti-plus"></i> {{ __('common.show_more') }}
                                                        </a>
                                                    </div>
                                                @else
                                                    {{ $notificationSetting->message }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(permissionCheck('notificationsetting.edit'))
                                                    <button data-value="{{$notificationSetting}}" class="primary-btn radius_30px mr-10 fix-gr-bg edit_notification" >{{ __('common.edit') }}</button>
                                                @endif
                                            </td>
                                        </tr>

                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('generalsetting::notifications.edit_modal')
</section>
@endsection
@push('scripts')
    <script>
        (function($){
            "use strict";
            var showMoreMessage = "{{ __('common.show_more') }}";
            var showLessMessage = "{{ __('common.show_less') }}";
            $(document).on('click', '.subject-toggle', function () {
                var cell = $(this).closest('.subject-cell');
                var short = cell.find('.subject-short');
                var full  = cell.find('.subject-full');

                if (full.hasClass('d-none')) {
                    short.addClass('d-none');
                    full.removeClass('d-none');
                    $(this).html(`<i class="ti-minus"></i> ${showLessMessage}`);
                } else {
                    full.addClass('d-none');
                    short.removeClass('d-none');
                    $(this).html(`<i class="ti-plus"></i> ${showMoreMessage}`);
                }
            });
            $(document).ready(function(){
                $(document).on('click', '.edit_notification', function(event){
                    let notification = $(this).data('value');
                    
                    @if(isModuleActive('FrontendMultiLang'))
                    if (notification.event != null) {
                        $.each(notification.event, function( key, value ) {
                            $("#event_"+key).val(value);
                        });
                    }else{
                        $("#event_{{auth()->user()->lang_code}}").val(notification.translateevent);
                    }
                    if (notification.message != null) {
                        $.each(notification.message, function( key, value ) {
                            $("#message_"+key).val(value);
                        });
                    }else{
                        $("#message_{{auth()->user()->lang_code}}").val(notification.Translatemessage);
                    }
                    if (notification.admin_msg != null) {
                        $.each(notification.admin_msg, function( key, value ) {
                            $("#admin_msg_"+key).val(value);
                        });
                    }else{
                        $("#admin_msg_{{auth()->user()->lang_code}}").val(notification.Translateadminmessage);
                    }
                    @else

                    // Identificamos el idioma del usuario actual
                    let currentLang = '{{ auth()->user()->lang_code }}';

                    // Función de extracción segura que prefiere el idioma actual, luego inglés, luego el primer disponible
                    function getTranslatedValue(field, fallbackField) {
                        if (typeof field === 'object' && field !== null) {
                            return field[currentLang] || field['en'] || Object.values(field)[0] || '';
                        }
                        // Si no es objeto, intentamos parsear por si viene como string JSON
                        try {
                            let parsed = JSON.parse(field);
                            return parsed[currentLang] || parsed['en'] || Object.values(parsed)[0] || '';
                        } catch (e) {
                            // Si todo falla, usamos el campo de respaldo (fallback) plano
                            return field || fallbackField || '';
                        }
                    }

                    // Aplicamos la lógica a los campos de la modal usando .val()
                    $('#event').val(getTranslatedValue(notification.event, notification.translateevent));
                    $('#message').val(getTranslatedValue(notification.message, notification.Translatemessage));
                    $('#admin_msg').val(getTranslatedValue(notification.admin_msg, notification.Translateadminmessage));

                    @endif
                    $('#notificaion_id').val(notification.id);
                    if(notification.type.includes('email')){
                        $('#notification_email').prop('checked',true);
                    }else{
                        $('#notification_email').prop('checked',false);
                    }
                    if(notification.type.includes('mobile')){
                        $('#notification_mobile').prop('checked',true);
                    }else{
                        $('#notification_mobile').prop('checked',false);
                    }
                    if(notification.type.includes('system')){
                        $('#notification_system').prop('checked',true);
                    }else{
                        $('#notification_system').prop('checked',false);
                    }
                    if(notification.type.includes('sms')){
                        $('#notification_sms').prop('checked',true);
                    }else{
                        $('#notification_sms').prop('checked',false);
                    }
                    $('#edit_modal').modal('show');
                });
            $('#edit_modal').on('hidden.bs.modal', function () {
                $('#edit_form')[0].reset();
                resetValidationErrors();
                $('#notificaion_id').val('');
            });
            $(document).on('submit', '#edit_form', function(event) {
                event.preventDefault();
                $("#pre-loader").removeClass('d-none');

                // Limpiar espacios en blanco de inputs y textareas antes de serializar
                $(this).find('input[type="text"], textarea').each(function() {
                    $(this).val($.trim($(this).val()));
                });

                let id = $('#notificaion_id').val()
                let formElement = $(this).serializeArray()
                let formData = new FormData();
                formElement.forEach(element => {
                    formData.append(element.name, element.value);
                });
                formData.append('_token', "{{ csrf_token() }}");
                resetValidationErrors();
                $.ajax({
                    url: "{{ route('notificationsetting.update')}}",
                    type: "POST",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(response) {
                        window.location.reload();
                        toastr.success("{{__('common.updated_successfully')}}", "{{__('common.success')}}");
                        $("#pre-loader").addClass('d-none');
                    },
                    error: function(response) {
                        $("#pre-loader").addClass('d-none');
                        toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                        showValidationErrors(response.responseJSON.errors);
                    }
                });
            });
            function showValidationErrors(errors) {
            @if(isModuleActive('FrontendMultiLang'))
                $('#error_event_{{auth()->user()->lang_code}}').text(errors['event.{{auth()->user()->lang_code}}']);
                $('#error_message_{{auth()->user()->lang_code}}').text(errors['message.{{auth()->user()->lang_code}}']);
                $('#error_admin_msg_{{auth()->user()->lang_code}}').text(errors['admin_msg.{{auth()->user()->lang_code}}']);
            @else
                $('#error_event').text(errors.event);
                $('#error_message').text(errors.message);
                $('#error_admin_msg').text(errors.admin_msg);
            @endif
                $('#error_type').text(errors.type);
            }
            function resetValidationErrors(){
                @if(isModuleActive('FrontendMultiLang'))
                $('#error_event_{{auth()->user()->lang_code}}').text('');
                $('#error_message_{{auth()->user()->lang_code}}').text('');
                $('#error_admin_msg_{{auth()->user()->lang_code}}').text('');
                @else
                $('#error_event').text('');
                $('#error_message').text('');
                $('#error_admin_msg').text('');
                @endif
                $('#error_type').text('');
            }
            });
        })(jQuery);
    </script>
@endpush