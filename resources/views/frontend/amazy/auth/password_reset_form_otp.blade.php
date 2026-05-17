@extends('frontend.default.auth.layouts.app')
@section('styles')
    <style>
        .login_logo img {
            max-width: 140px;
            margin: 0 auto;
        }
        .register_part {
            
            min-height: 100vh !important;
        }
    </style>
@endsection
@section('content')
<section class="login_area register_part bg-custom-login">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 col-xl-4">
                <div class="register_form_iner">
                    <div class="login_logo text-center mb-3">
                        <a href="{{url('/')}}"><img src="{{showImage(app('general_setting')->logo)}}" alt="{{app('general_setting')->company_name}}" title="{{app('general_setting')->company_name}}"></a>
                    </div>
                    <h2>{{ __('defaultTheme.welcome_back') }}, <br>{{ __('defaultTheme.please_confirm_with_new_password') }}</h2>
                    <form method="POST" class="register_form" action="{{ route('otp_user_password_update') }}">
                        @csrf

                        <div class="form-row">
                            <div class="col-md-12">
                                <label for="password">{{ __('common.password') }}</label>
                                <input type="password" id="password" class="@error('password') is-invalid @enderror" name="password" required placeholder="{{ __('common.password') }}" onfocus="this.placeholder = ''"
                                onblur="this.placeholder = ''" autocomplete="new-password">
                            </div>
                            <div class="col-md-12">
                                <label for="password-confirm">{{ __('common.confirm_password') }}</label>
                                <input type="password" id="password_confirm" name="password_confirmation" required placeholder="{{ __('common.confirm_password') }}" onfocus="this.placeholder = ''"
                                onblur="this.placeholder = ''" autocomplete="new-password">

                            </div>
                            <div class="col-md-12 mt-3">
                                {{-- Contenedor para errores del Backend --}}
                                @if ($errors->has('password'))
                                    <div class="alert alert-danger p-2 radius_5px backend-errors" style="font-size: 13px;">
                                        <ul class="m-0 pl-3">
                                            @foreach ($errors->get('password') as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Contenedor para error de Frontend (JS) --}}
                                <div class="alert alert-danger p-2 radius_5px" id="password_error" style="font-size: 13px; display: none;">
                                    <ul class="m-0 pl-3"><li id="js_error_message">
                                        {{-- El texto se inyectará vía JS --}}
                                    </li></ul>
                                </div>
                            </div>
                            <div class=" col-12 form-text text-muted mb_20 helper-password">
                                {{ __('common.password_requirements') }}
                                <ul class="mb-0 ps-3">
                                    <li>{{ __('common.password_min_length') }}</li>
                                    <li>{{ __('common.password_uppercase') }}</li>
                                    <li>{{ __('common.password_number') }}</li>
                                    <li>{{ __('common.password_special') }}</li>
                                </ul>
                            </div>

                            <div class="col-md-12 text-center">
                                <div class="register_area">
                                    <button type="submit" class="btn_1">{{ __('common.reset_password') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            $(document).on('submit', '.register_form', function(event){
                let password = $('#password').val();
                let password_confirm = $('#password_confirm').val();
                let container = $('#password_error');
                let messageLi = $('#js_error_message');

                if(password !== password_confirm){
                    // Mostramos el error de coincidencia inmediatamente
                     messageLi.text("{{ __('validation.password_mismatch') }}");
                    container.show();
                    event.preventDefault(); // Evita el envío
                } else {
                    container.hide(); // Si coinciden, ocultamos el error de JS
                }
            });

            // Detecta escritura para limpiar mensajes
            $('#password, #password_confirm').on('input', function() {
                // 1. Ocultamos el error de JS (sin eliminarlo del DOM)
                $('#password_error').hide();

                // 2. Desvanecemos errores del backend si existen
                let backendErrors = $('.backend-errors');
                if (backendErrors.length > 0) {
                    backendErrors.fadeOut(300);
                }

                // 3. Limpiamos estilos de validación de Laravel
                $('#password').removeClass('is-invalid');
            });
        });
    </script>
@endpush
