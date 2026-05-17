<div class="white_box_30px">
    <!-- SMTP form  -->
    <div class="main-title mb-25">
        <h3 class="mb-0">{{ __('general_settings.email_settings') }}</h3>
    </div>

    <form action="{{ route('smtp_gateway_credentials_update') }}" method="post" id="smtp_form">
        @csrf
        <div class="row">
            <div class="col-xl-12">
                <div class="primary_input">
                    <label class="primary_input_label" for="">{{ __('common.active_gateway') }} <span class="text-danger">*</span></label>
                    <ul id="theme_nav" class="permission_list sms_list ">
                        <li>
                            <label data-id="bg_option" class="primary_checkbox d-flex mr-12">
                                <input name="mail_gateway" id="status_active" value="smtp" @if(app('general_setting')->mail_protocol == 'smtp') checked @endif class="active mail_gateway"
                                    type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('general_settings.smtp') }}</p>
                        </li>
                        <li>
                            <label data-id="color_option" class="primary_checkbox d-flex mr-12">
                                <input name="mail_gateway" value="sendmail" id="status_inactive" @if(app('general_setting')->mail_protocol == 'sendmail') checked @endif class="de_active mail_gateway" type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('general_settings.php_mail') }}</p>
                        </li>
                        <!-- SendGrid Option -->
                        <li>
                            <label data-id="color_option" class="primary_checkbox d-flex mr-12">
                                <input
                                    name="mail_gateway"
                                    value="sendgrid"
                                    id="status_sendgrid"
                                    @if(app('general_setting')->mail_protocol == 'sendgrid') checked @endif class="de_active mail_gateway" type="radio"
                                >
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('general_settings.sendgrid') }}</p>
                        </li>
                    </ul>
                    <span class="text-danger" id="status_error"></span>
                </div>
            </div>
        </div>
        <div class="row" id="smtp">
            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="MAIL_FROM_NAME">
                    <label class="primary_input_label" for="">{{ __('general_settings.from_name') }}<span class="text-danger">*</span></label>
                    <input class="primary_input_field" placeholder="-" type="text" name="MAIL_FROM_NAME"
                        value="{{ config('mail.from.name') }}">
                </div>
            </div>
            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="MAIL_FROM_ADDRESS">
                    <label class="primary_input_label" for="">{{ __('general_settings.from_mail') }}<span class="text-danger">*</span></label>
                    <input class="primary_input_field" placeholder="-" type="email" name="MAIL_FROM_ADDRESS" value="{{ config('mail.from.address') }}">
                </div>
            </div>

            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="MAIL_HOST">
                    <label class="primary_input_label" for="">{{ __('general_settings.mail_host') }}<span class="text-danger">*</span></label>
                    <input class="primary_input_field" placeholder="-" type="text" name="MAIL_HOST"
                        value="{{ config('mail.mailers.smtp.host') }}">
                </div>
            </div>

            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="MAIL_PORT">
                    <label class="primary_input_label" for="">{{ __('general_settings.mail_port') }}<span class="text-danger">*</span></label>
                    <input class="primary_input_field"
                        placeholder="-" type="number"
                        name="MAIL_PORT"
                        value="{{ config('mail.mailers.smtp.port') }}"
                        min="1"
                        max="65535"
                        oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5);"
                        onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                </div>
            </div>

            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="MAIL_USERNAME">
                    <label class="primary_input_label" for="">{{ __('general_settings.mail_username') }}<span class="text-danger">*</span></label>
                    <input class="primary_input_field" placeholder="-" type="text" name="MAIL_USERNAME"
                        value="{{ config('mail.mailers.smtp.username') }}">
                </div>
            </div>

            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="MAIL_PASSWORD">
                    <label class="primary_input_label" for="">{{ __('general_settings.mail_password') }}<span class="text-danger">*</span></label>
                    <input class="primary_input_field" placeholder="-" type="password" name="MAIL_PASSWORD"
                        value="{{ config('mail.mailers.smtp.password') }}">
                </div>
            </div>

            <div class="col-xl-6">
                <div class="primary_input">
                    <input type="hidden" name="types[]" value="MAIL_ENCRYPTION">
                    <label class="primary_input_label" for="">{{ __('general_settings.mail_encryption') }}<span class="text-danger">*</span></label>
                    <select name="MAIL_ENCRYPTION" class="primary_select mb-25">
                        <option value="ssl" @if (config('mail.mailers.smtp.encryption')=="ssl" ) selected @endif>{{__('common.ssl')}}</option>
                        <option value="tls" @if (config('mail.mailers.smtp.encryption')=="tls" ) selected @endif>{{__('common.tls')}}</option>
                    </select>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="MAIL_CHARSET">
                    <label class="primary_input_label" for="">{{ __('general_settings.email_charset') }}</label>
                    <input class="primary_input_field" placeholder="utf-8" type="text" name="MAIL_CHARSET"
                        value="{{ config('mail.mailers.smtp.charset') }}">
                </div>
            </div>
        </div>
        <div class="row" id="sendmail">

            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="SENDER_NAME">
                    <label class="primary_input_label" for="">{{ __('general_settings.sender_name') }}</label>
                    <input class="primary_input_field" placeholder="-" type="text" name="SENDER_NAME"
                        value="{{ config('mail.sender.name') }}">
                </div>
            </div>

            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="SENDER_MAIL">
                    <label class="primary_input_label" for="">{{ __('general_settings.sender_email') }}</label>
                    <input class="primary_input_field" placeholder="-" type="text" name="SENDER_MAIL"
                        value="{{ config('mail.sender.email') }}">
                </div>
            </div>
        </div>

        <!-- SendGrid Fields -->
        <div class="row" id="sendgrid">
            <!-- Email Field -->
            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="EMAIL">
                    <label class="primary_input_label" for="">
                        {{ __('common.email') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input
                        class="primary_input_field"
                        placeholder="-"
                        type="email"
                        name="EMAIL"
                        value="{{ config('mail.mailers.sendgrid.email') }}"
                        id="sendgrid_email"
                    >
                </div>
            </div>

            <!-- SendGrid API Key Field -->
            <div class="col-xl-6">
                <div class="primary_input mb-25">
                    <input type="hidden" name="types[]" value="SENDGRID_API_KEY">
                    <label class="primary_input_label" for="">
                        {{ __('general_settings.sendgrid_api_key') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input
                        class="primary_input_field"
                        placeholder="-"
                        type="text"
                        name="SENDGRID_API_KEY"
                        value="{{ config('mail.mailers.sendgrid.key') }}"
                        id="sendgrid_key"
                    >
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-6">
                <div class="primary_input">
                    <label class="primary_input_label" for="">{{ __('Mail Send Type') }} <span class="text-danger">*</span></label>
                    <ul id="theme_nav" class="permission_list sms_list ">
                        <li>
                            <label data-id="bg_option" class="primary_checkbox d-flex mr-12">
                                <input name="QUEUE_CONNECTION" id="instant_send" value="sync" @if(config('queue.default') == 'sync') checked @endif class="active send_type"
                                    type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('common.instant_send') }}</p>
                        </li>
                        <li>
                            <label data-id="color_option" class="primary_checkbox d-flex mr-12">
                                <input name="QUEUE_CONNECTION" value="database" id="via_queue" @if(config('queue.default') != 'sync') checked @endif class="de_active send_type" type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('common.via_queue') }}</p>
                        </li>
                    </ul>
                </div>
            </div>
            <div id="send_type_cron" class="col-xl-6 @if(config('queue.default') == 'sync') d-none @endif">
                <div class="primary_input mb-25">
                    <label class="primary_input_label" for="">{{ __('Mail Send Cron URL') }}</label>
                    <input class="primary_input_field" readonly type="text" name=""
                        value="{{ route('mail-send-via-queue') }}">
                </div>
            </div>
        </div>

        <div class="row">


            @if (permissionCheck('smtp_gateway_credentials_update'))
            <div class="col-12 mb-45 pt_15">
                <div class="submit_btn text-center">
                    <button class="btn-toolkit btn-primary" type="submit"> <i class="ti-check"></i>
                        {{ __('common.save') }}</button>
                </div>
            </div>
            @else
            <div class="col-lg-12 text-center mt-2">
                <span class="alert alert-warning" role="alert">
                    <strong>{{ __('common.you_don_t_have_this_permission') }}</strong>
                </span>
            </div>
            @endif
        </div>
    </form>
    <hr>
    <form action="{{ route('test_mail.send') }}" method="post">
        @csrf
        <div class="row">
            <div class="col-xl-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label" for="">{{ __('general_settings.send_a_test_email_to') }} <span
                            class="text-danger">*</span></label>
                    <input class="primary_input_field" type="email" name="email" value="{{old('email')}}"
                        placeholder="">
                    <span class="text-danger">{{$errors->first('email')}}</span>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label" for="">{{ __('general_settings.mail_text') }} <span
                            class="text-danger">*</span></label>
                    <input class="primary_input_field" placeholder="-" type="text" value="{{old('content')}}"
                        name="content">
                    <span class="text-danger">{{$errors->first('content')}}</span>
                </div>
            </div>
        </div>
        <div class="submit_btn text-center mb-100 pt_15">
            <button class="btn-toolkit btn-primary" type="submit">{{ __('general_settings.send_test_mail') }}</button>
        </div>
    </form>

    <!--/ SMTP_form  -->
</div>

<dialog class="modal fade" id="confirmSaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti-alert mr-1"></i> {{ __('common.confirm_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body text-center">
                <p>{{ __('common.confirm_smtp_message') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn-toolkit btn-primary" id="confirm_submit_btn"><i class="ti-check"></i> {{ __('common.confirm') }}</button>
            </div>
        </div>
    </div>
</dialog>

<script>
    $(document).ready(function() {
        let formToSubmit = $('#smtp_form');

        formToSubmit.on('submit', function(e) {
            e.preventDefault();

            // 1. Obtener el gateway seleccionado
            let selectedGateway = $("input[name='mail_gateway']:checked").val();

            // 2. Validación condicional para SendGrid
            if (selectedGateway === 'sendgrid') {
                let sgEmail = $("input[name='EMAIL']").val();
                let sgKey = $("input[name='SENDGRID_API_KEY']").val();

                if (!sgEmail || !sgKey) {
                    // Usamos Toastr para mantener el estilo del proyecto
                    toastr.error("{{ __('general_settings.sendgrid_fields_required') }}", "{{ __('common.error') }}");
                    return false;
                }
            }

            $('#confirmSaveModal').modal('show');
        });

        $('#confirm_submit_btn').on('click', function() {
            $('#confirmSaveModal').modal('hide');
            $('#pre-loader').removeClass('d-none');
            formToSubmit[0].submit();
        });
    });
</script>
