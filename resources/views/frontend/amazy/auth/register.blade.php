@extends('frontend.amazy.auth.layouts.app')
@php
    $customer = $customer ?? null;
    $customerProfile = $customer?->customerProfile ?? null;
    $customerFinancialProfile = $customer?->customerFinancialProfile ?? null;
@endphp
@push('styles')
    <style>
        .primary_bulet_checkbox .checkmark{
            top: 2px;
        }
        .term_link_set, .policy_link_set{
            color: var(--base_color);
        }
        #registerPolicyModal .modal-header {
            padding-bottom: 0.25rem;
        }
        #registerPolicyModal .modal-body {
            padding-top: 0.5rem !important;
        }
        #registerPolicyModal .policy-list {
            padding-left: 1.25rem;
            list-style: disc;
            margin-bottom: 0 !important;
        }
        #registerPolicyModal .policy-list li {
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 0.25rem !important;
            padding: 0 !important;
        }
        #registerPolicyModal .policy-footnote {
            font-size: 0.875rem;
            line-height: 1.5;
            border-top: 1px solid #eee;
            padding-top: 0.6rem;
            margin-top: 0.6rem;
            margin-bottom: 0 !important;
        }
    </style>
     <link rel="stylesheet" href="{{ asset('public/css/register-form.css') }}">
@endpush
@section('content')
<div class="bg-custom-login row ign-register py-5 mx-0">
    <div class="col-xxl-8 col-lg-10 col-12 mx-auto p-1 p-md-2">
        <div class="amazy_login_form">
            <a href="{{url('/')}}" class="logo d-block text-center">
                <img src="{{showImage(app('general_setting')->logo)}}" alt="{{app('general_setting')->company_name}}" title="{{app('general_setting')->company_name}}">
            </a>
            <h3 class="m-0 text-center my-3">{{__('auth.Sign Up')}}</h3>

            <div class="form_sep2 d-flex align-items-center">
                <span class="sep_line flex-fill"></span>
                <span class="form_sep_text font_14 f_w_500 "></span>
                <span class="sep_line flex-fill"></span>
            </div>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h5><strong>{{__('common.please_fix_the_following_bugs')}}:</strong></h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <x-FormWizard.multistep-form
            action="{{ route('register') }}"
            method="POST"
            name="register"
            id="register_form"
            autocomplete="off"
            class="general-register-form"
            enctype="multipart/form-data"
            :steps="[
                'common.identity',
                'common.basic_data',
                'common.aditional_information',
                'common.security'
            ]"
            >
                <x-FormWizard.multistep-step step="1">
                    @csrf

                    @include('register.register_documents')

                </x-FormWizard.multistep-step>

                <x-FormWizard.multistep-step step="2">

                    <!-- Código de referido -->
                        <div class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
                            <div class="col-12 mb-3">
                                <h3 class=" section-title">{{__('common.referral_code')}}</h3>
                            </div>
                            <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
                                <label class="general-lable" for="referral_code">{{label_case_custom(__('common.referral_code'))}} ({{__('common.optional')}})  </label>
                                <input name="referral_code" id="referral_code" value="{{ old('referral_code', $customerProfile?->code  ?? $referralCode  ?? '') }}" placeholder="{{ __('common.referral_code') }}"  class="general-input radius_5px" type="text">
                                <span class="text-danger" >{{ $errors->first('referral_code') }}</span>
                            </div>
                        </div>
                    <!-- Informacion de basica -->
                        @include('register.register_basic_info')
                </x-FormWizard.multistep-step>
                <x-FormWizard.multistep-step step="3">
                    <!-- Informacion de contacto -->
                        @include('register.register_contact_info')

                    <!-- Informacion personal -->
                        @include('register.register_personal_info')
                </x-FormWizard.multistep-step>
                <x-FormWizard.multistep-step step="4">
                    <!-- Documents -->
                        

                        @include('register.register_password')

                        @if(env('NOCAPTCHA_FOR_REG') == "true")
                        <div class="col-12 mb_20">
                            @if(env('NOCAPTCHA_INVISIBLE') != "true")
                            <div class="g-recaptcha" data-callback="callback" data-sitekey="{{env('NOCAPTCHA_SITEKEY')}}"></div>
                            @endif
                            <span class="text-danger" >{{ $errors->first('g-recaptcha-response') }}</span>
                        </div>
                        @endif


                        @php
                            $rawPolicy = app('general_setting')->register_policy ?? null;
                            $policyItems = [];
                            $policyFootnote = '';
                            if ($rawPolicy) {
                                $decoded = is_array($rawPolicy) ? $rawPolicy : json_decode($rawPolicy, true);
                                if (is_array($decoded)) {
                                    // Si el modelo HasTranslations ya resolvió el locale, llega con items/footnote directo
                                    if (isset($decoded['items'])) {
                                        $locData = $decoded;
                                    } else {
                                        $loc = app()->getLocale();
                                        $locData = $decoded[$loc] ?? $decoded['es'] ?? $decoded['en'] ?? [];
                                    }
                                    $policyItems    = $locData['items'] ?? [];
                                    $policyFootnote = $locData['footnote'] ?? '';
                                }
                            }
                        @endphp
                        <div class="col-lg-12 mb_20 mt_10">
                            <label class="primary_checkbox d-flex">
                                <input id="policyCheck" type="checkbox" >
                                <span class="checkmark mr_15"></span>
                                <p class="label_name f_w_400"> {{__('formBuilder.By signing up, you agree to ')}} <button type="button" class="btn-link-inline" data-bs-toggle="modal" data-bs-target="#registerPolicyModal">{{__('formBuilder.Terms of Service ')}}</button> {{ __('formBuilder.and') }} <button type="button" class="btn-link-inline" data-bs-toggle="modal" data-bs-target="#registerPolicyModal">{{__('formBuilder.Privacy Policy')}}</button></p>
                            </label>
                        </div>

                        <!-- Modal de Políticas -->
                        <div class="modal fade" id="registerPolicyModal" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header border-0">
                                        <h4 class="modal-title">{{ __('shipping.terms_and_conditions') }}</h4>
                                        <button type="button" class="close" data-bs-dismiss="modal"><i class="ti-close"></i></button>
                                    </div>
                                    <div class="modal-body register-policy-content px-4 pt-1 pb-4">
                                        @if(!empty($policyItems))
                                            <ul class="policy-list mb-0">
                                                @foreach($policyItems as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                            @if($policyFootnote)
                                                <p class="policy-footnote">{{ $policyFootnote }}</p>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn-toolkit btn-secondary-outline" data-bs-dismiss="modal">{{ __('common.close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                </x-FormWizard.multistep-step>
                <x-slot name="actions">
                    <div class="d-flex justify-content-center gap-4">
                        <button type="button"
                                class="btn-toolkit btn-secondary btn-icon style2"
                                data-action="prev">
                            <i class="ti-angle-left mr-2"></i>
                            {{__('common.back')}}
                        </button>

                        <button type="button"
                                class="btn-toolkit btn-secondary btn-icon style2"
                                data-action="next"
                                >
                            {{__('common.next')}}
                            <i class="ti-angle-right ml-2"></i>
                        </button>

                        @if(env('NOCAPTCHA_INVISIBLE') == "true")
                            <button data-action="submit" type="button" class="g-recaptcha amaz_primary_btn style2 d-none" data-sitekey="{{env('NOCAPTCHA_SITEKEY')}}" data-size="invisible" data-callback="onSubmit">{{__('auth.Sign Up')}}</button>
                        @else
                            <button data-action="submit" class="btn-toolkit btn-primary btn-icon d-none" id="sign_in_btn">{{__('auth.Sign Up')}}</button>
                        @endif
                    </div>

                </x-slot>

            </x-FormWizard.multistep-form>
            <div class="row mt-5 ">
                <div class="col-12 text-center">
                    <p class="sign_up_text">{{__('auth.Already have an Account?')}}  <a href="{{url('/login')}}">{{__('auth.Sign In')}}</a></p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const registerTranslations = @json(trans('js'));
</script>
<script src="{{ asset('public/js/nice-ajax.js') }}" defer></script>
<script src="{{ asset('public/js/register-form.js') }}" defer></script>
<script src="{{ asset('public/js/currency-mask.js') }}" defer></script>
<script src="https://www.google.com/recaptcha/api.js" async defer crossorigin="anonymous"></script>
<script>
    function onSubmit(token) {
        document.getElementById("register_form").submit();
    }
</script>
<script>
    (function($){
        "use strict";
        $(document).ready(function(){
            $(document).on('submit', '#register_form', function(event){

                const password = $('#password').val();
                const confirmPassword = $('#password-confirm').val();

                if(password !== confirmPassword){
                    event.preventDefault();
                    toastr.error("{{__('auth.passwords_do_not_match')}}","{{__('common.error')}}");
                    return false;
                }

                if($("#policyCheck").prop('checked')!=true){
                    event.preventDefault();
                    toastr.error("{{__('common.please_agree_with_our_policy_privacy')}}","{{__('common.error')}}");
                    return false;
                }

                $("#pre-loader").removeClass('d-none');
            });
        });
    })(jQuery);
</script>
@endpush
