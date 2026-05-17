@extends('frontend.amazy.auth.layouts.app')
@section('content')
<div class="amazy_login_area bg-custom-login ing-reset-passs">
    @php
        $loginPageInfo = \Modules\FrontendCMS\Entities\LoginPage::findOrFail(4);
    @endphp
    <div class="amazy_login_area_left d-flex align-items-center justify-content-center">
        <div class="amazy_login_form">
            <h3 class="m-0">{{ __('amazy.Welcome back') }}</h3>
            <p class="support_text">{{__('amazy.Please confirm with new password.')}}</p>
            @if(config('app.sync'))
                <div class="d-flex justify-content-center mt-20 grid_gap_5 flex-wrap">
                    <button class="amaz_primary_btn style2 radius_5px text-uppercase  text-center mb_25" id="admin" data-email="{{$admin_email}}">{{ __('common.admin') }}</button>
                </div>
            @endif
            <br>
            <form action="{{ route('password.update') }}" method="POST" name="login" id="login_form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="row">
                    <div class="col-12 mb_20">
                        <label class="primary_label2" for="email">{{ __('common.email_address') }} <span>*</span> </label>
                        <input name="email" id="email" value="{{ $email ?? old('email') }}" placeholder="{{ __('common.email_address') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.email_address') }}'" class="primary_input3 radius_5px" type="email">
                        @error('email')
                        <span id="email_error" class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 mb_20">
                        <label class="primary_label2" for="password">{{ __('common.password') }} <span>*</span></label>
                        <input name="password" id="password" required placeholder="{{__('amazy.Min. 8 Character')}}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{__('amazy.Min. 8 Character')}}'" class="primary_input3 radius_5px" type="password">
                    </div>
                    <div class="col-12 mb_20">
                        <label class="primary_label2" for="password-confirm">{{ __('common.confirm_password') }} <span>*</span></label>
                        <input name="password_confirmation" id="password-confirm" placeholder="{{__('amazy.Min. 8 Character')}}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{__('amazy.Min. 8 Character')}}'" class="primary_input3 radius_5px" type="password">
                    </div>
                                       
                    <div class="col-md-12">
                        {{-- Errores del Servidor (Reglas de Password) --}}
                        @if ($errors->has('password'))
                            <div class="alert alert-danger p-2 radius_5px backend-errors" style="font-size: 13px;">
                                <ul class="m-0 pl-3">
                                    @foreach ($errors->get('password') as $error)
                                        <li class="d-flex align-items-start mb-1">
                                            <i class="ti-control-record" style="font-size: 5px; color: #721c24; margin-top: 7px; margin-right: 8px; background-color: #721c24; border-radius: 50%;"></i>
                                            <span>{{ $error }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
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
                    <div class="col-12">
                        <button class="amaz_primary_btn style2 radius_5px  w-100 text-uppercase  text-center mb_25" id="sign_in_btn">{{__('common.reset_password')}}</button>
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
            <div class="login_text d-flex align-items-center justify-content-center flex-column text-center">
                <!-- <h4>{{__('amazy.turn_your_ideas_into_reality')}}</h4> -->
                <!-- <p class="m-0">{{__('amazy.consistent_quality_and_experience_across_all_platforms_and_devices')}}</p> -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function($){
            "use strict";
            $(document).ready(function(){
                $('#submit_btn').removeAttr('disabled');
                $(document).on('submit', '#login_form', function(event){

                    $('#email_error').text('');
                    $('#password_error').text('');

                    let email = $('#text').val();
                    let password = $('#password').val();

                    let val_check = 0;

                    if(email == ''){
                        $('#email_error').text('{{ __('validation.email_or_phone_required') }}');
                        val_check = 1;
                    }

                    if(password == ''){
                        $('#password_error').text('{{ __('validation.password_required') }}');
                        val_check = 1;
                    }

                    if(val_check == 1){
                        event.preventDefault();
                    }

                });
                
                // Limpieza Automática al escribir
                $('#password, #password-confirm').on('input', function() {            
                    // Desvanecer errores de Backend si existen
                    let backendErrors = $('.backend-errors');
                    if (backendErrors.length > 0) {
                        backendErrors.fadeOut(300);
                    }
                });

                @if(config('app.sync'))
                    $(document).on('click', '#admin', function(event){
                        let email = $(this).data('email');
                        $("#text").val('');
                        $("#password").val('');
                        if(email != ''){
                            $("#text").val(email);
                            $("#password").val('12345678');
                            $('#login_form').submit();
                        }else{
                            toastr.error('{{ __('validation.please_create_admin_first') }}', '{{ __('common.error') }}');
                        }
                    });
                    $(document).on('change', '#password', function(){
                        let value = $(this).val();
                        if($('#auto_login').length){
                            $('#auto_login').val(value == '12345678');  
                        }
                    });
                @endif

            });
        })(jQuery);
    </script>
@endpush
