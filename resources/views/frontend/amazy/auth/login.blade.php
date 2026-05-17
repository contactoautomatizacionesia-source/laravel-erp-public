@extends('frontend.amazy.auth.layouts.app')


@section('content')
<div class="amazy_login_area bg-custom-login ign-login-users">
    <div class="amazy_login_area_left d-flex align-items-center justify-content-center">
        <div class="amazy_login_form">
            @if (session('error'))
                <div id="session-error-alert" class="alert alert-warning alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <a href="{{url('/')}}" class="logo d-block text-center w-100">
                <img src="{{showImage(app('general_setting')->logo)}}" alt="{{app('general_setting')->company_name}}" title="{{app('general_setting')->company_name}}">
            </a>
            <h3 class="m-0 text-center my-3">{{ $loginPageInfo->title ?? __('common.sign_in') }}</h3>
            {{-- <p class="support_text text-center m-0">{{ isset($loginPageInfo->sub_title)? $loginPageInfo->sub_title: __('auth.See your growth and get consulting support!') }}</p> --}}
            @if(config('app.sync'))
                <div class="d-flex justify-content-center mt-1 grid_gap_5 flex-wrap">
                    @if($customer_email)
                        <button class="amaz_primary_btn3 style3 radius_5px text-uppercase  text-center mb_25" id="customer" data-email="{{$customer_email}}">{{ __('common.customer') }}</button>
                    @endif
                </div>
            @endif
           
            
            <form action="{{ route('login') }}" method="POST" name="login" id="login_form">
                @csrf

                @if(config('app.sync'))
                    <input type="hidden" id="auto_login" name="auto_login" value="true">
                @endif
                <div class="row">
                    <div class="col-12 p-0 mb_20">
                        <label class="primary_label2" for="text">{{ __('amazy.Email address or phone') }} <span>*</span> </label>
                        <input name="login" id="text" placeholder="{{ __('amazy.Email address or phone') }}" value="{{old('login')}}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('amazy.Email address or phone') }}'" class="primary_input3 radius_5px" type="text">
                        <span id="email_error" class="text-danger">{{ $errors->first('email') }}</span>
                        <span id="username_error" class="text-danger">{{ $errors->first('username') }}</span>
                    </div>
                    <div class="col-12 p-0 mb_20 position-relative">
                        <label class="primary_label2" for="password">{{ __('common.password') }} <span>*</span></label>
                        <input name="password" id="password" placeholder="{{__('amazy.Min. 8 Character')}}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{__('amazy.Min. 8 Character')}}'" class="primary_input3 radius_5px" type="password" oninput="togglePasswordIcon()">
                        <button class="position-absolute toggle-password" id="password-toggle-icon-container" style="right: 15px; top: 42px; cursor: pointer; z-index: 10; display: none; border: none; background: none; padding: 0;" onclick="togglePasswordLogin()" onkeydown="if(event.key==='Enter' || event.key===' ') { event.preventDefault(); togglePasswordLogin(); }" aria-label="Toggle password visibility" type="button">
                            <svg id="password-toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <span id="password_error" class="text-danger">{{ $errors->first('password') }}</span>
                    </div>
                    @if(env('NOCAPTCHA_FOR_LOGIN') == "true")
                    <div class="col-12 p-0 mb_20">
                        @if(env('NOCAPTCHA_INVISIBLE') != "true")
                        <div class="g-recaptcha" data-callback="callback" data-sitekey="{{env('NOCAPTCHA_SITEKEY')}}"></div>
                        @endif
                        <span class="text-danger" >{{ $errors->first('g-recaptcha-response') }}</span>
                    </div>
                    @endif
                    <div class="col-12 p-0 mb_20">
                        <label class="primary_checkbox d-flex">
                            <input name="remember" id="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}>
                            <span class="checkmark mr_15"></span>
                            <span class="label_name f_w_400 ">{{ __('defaultTheme.remember_me') }}</span>
                        </label>
                    </div>

                    <div class="d-flex gap-2 justify-content-center align-items-center p-0 mb-3">
                        @if (app('general_setting')->google_status)
                        <a href="{{url('/login/google')}}" class="google_logIn d-flex align-items-center justify-content-center">
                            <img src="{{url('/')}}/public/frontend/amazy/img/svg/google_icon.svg" alt="{{__('auth.Sign in with Google')}}" title="{{__('auth.Sign in with Google')}}">
                        </a>
                        @endif
                        @if (app('general_setting')->facebook_status)
                        <a href="{{url('/login/facebook')}}" class="google_logIn d-flex align-items-center justify-content-center">
                            <img src="{{url('/')}}/public/frontend/amazy/img/svg/facebook_icon.svg" alt="{{__('auth.Sign in with Facebook')}}" title="{{__('auth.Sign in with Facebook')}}">
                        </a>
                        @endif
                        @if (app('general_setting')->twitter_status)
                        <a href="{{url('/login/twitter')}}" class="google_logIn d-flex align-items-center justify-content-center">
                            <img src="{{url('/')}}/public/frontend/amazy/img/svg/twitter_icon.svg" alt="{{__('auth.Sign up with Twitter')}}" title="{{__('auth.Sign up with Twitter')}}">
                        </a>
                        @endif
                        @if (app('general_setting')->linkedin_status)
                        <a href="{{url('/login/linkedin')}}" class="google_logIn d-flex align-items-center justify-content-center">
                            <img src="{{url('/')}}/public/frontend/amazy/img/svg/linkedin_icon.svg" alt="{{__('auth.Sign up with LinkedIn')}}" title="{{__('auth.Sign up with LinkedIn')}}">
                        </a>
                        @endif
                    </div>

                    <div class="col-12 p-0">
                        @if(env('NOCAPTCHA_INVISIBLE') == "true")
                        <button type="button" class="g-recaptcha amaz_primary_btn style2 radius_5px  w-100 text-uppercase  text-center mb_25" data-sitekey="{{env('NOCAPTCHA_SITEKEY')}}" data-size="invisible" data-callback="onSubmit">{{__('common.sign_in')}}</button>
                        @else
                        <button class="amaz_primary_btn style2 radius_5px  w-100 text-uppercase  text-center mb_25" id="sign_in_btn">{{__('common.sign_in')}}</button>
                        @endif
                    </div>
                    <div class="col-12 p-0">
                        <p class="sign_up_text text-center">{{__('amazy.Forgot Password Text')}} <a href="{{url('/password/reset')}}">{{__('amazy.Forgot Password Link')}}</a></p>
                    </div>
                    <div class="col-12 p-0">
                        <p class="sign_up_text text-center">{{__('amazy.Don\'t have an Account?')}} <a href="{{url('/register')}}">{{__('amazy.Sign Up')}}</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="amazy_login_area_right d-flex align-items-center justify-content-cente">
        <div class="amazy_login_area_right_inner d-flex align-items-center justify-content-center flex-column">
            <div class="thumb">
                <img class="img-fluid" src="{{showImage($loginPageInfo->cover_img)}}" alt="{{ isset($loginPageInfo->title)? $loginPageInfo->title:'' }}" title="{{ isset($loginPageInfo->title)? $loginPageInfo->title:'' }}">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js" async defer crossorigin="anonymous"></script>
<script>
    function onSubmit(token) {
        document.getElementById("login_form").submit();
    }
    function togglePasswordLogin() {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('password-toggle-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            passwordInput.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }
    function togglePasswordIcon() {
        const passwordInput = document.getElementById('password');
        const iconContainer = document.getElementById('password-toggle-icon-container');
        if (passwordInput.value.length > 0) {
            iconContainer.style.display = 'block';
        } else {
            iconContainer.style.display = 'none';
            // Reset to password type when hiding
            passwordInput.type = 'password';
            const icon = document.getElementById('password-toggle-icon');
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }
</script>
<script>

(function($){
    "use strict";
    $(document).ready(function(){
        $('#submit_btn').removeAttr('disabled');
        $(document).on('submit', '#login_form', function(event){

            $('#email_error').text('');
            $('#password_error').text('');
            $('#username_error').text('');

            let email = $('#text').val();
            let password = $('#password').val();

            let val_check = 0;

            if(email == ''){
                $('#email_error').text('{{ __('validation.email_or_username_required') }}');
                val_check = 1;
            } else {
                // Validar formato de email o username
                // Username: letras, números, guiones bajos, guiones, mínimo 3 caracteres
                let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                let usernameRegex = /^[a-zA-Z0-9_-]{3,}$/;
                
                if (!emailRegex.test(email) && !usernameRegex.test(email)) {
                    $('#email_error').text('{{ __('validation.invalid_email_or_username') }}');
                    val_check = 1;
                }
            }

            if(password == ''){
                $('#password_error').text('{{ __('validation.password_required') }}');
                val_check = 1;
            }

            if(val_check == 1){
                event.preventDefault();
            }
        });

        @if(config('app.sync'))
            $(document).on('click', '#customer', function(event){
                $('#sign_in_btn').attr('disabled', true);
                let email = $(this).data('email');
                $("#text").val('');
                $("#password").val('');
                if(email != ''){
                    $("#text").val(email);
                    $("#password").val('12345678');
                    $('#login_form').submit();
                }else{
                    toastr.error('{{ __('validation.please_create_customer_first') }}', '{{ __('common.error') }}');
                }
            });
            $(document).on('change', '#password', function(){
                let value = $(this).val();
                if($('#auto_login').length){
                    $('#auto_login').val(value == '12345678');
                }
            });
        @endif

        // Lógica para Alertas de Sesión
        let alertElement = $('#session-error-alert');

        if (alertElement.length > 0) {
            // 1. Ocultar automáticamente después de 10 segundos
            let autoHide = setTimeout(function() {
                alertElement.fadeOut(600, function() { $(this).remove(); });
            }, 15000);

            // 2. Ocultar al empezar a escribir (UX Mejorada)
            // Ya no necesitamos detectar el click en la 'X' porque Bootstrap lo hace solo.
            $('input').on('input focus', function() {
                if (alertElement.is(':visible')) {
                    clearTimeout(autoHide); // Cancelamos el timer
                    alertElement.fadeOut(400, function() { $(this).remove(); });
                }
            });
        }

    });
})(jQuery);
</script>
@endpush
