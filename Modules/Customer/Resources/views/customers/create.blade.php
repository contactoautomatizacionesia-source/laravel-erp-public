@extends('backEnd.master')

@php
    $customerProfile =  null;
    $customer =  null;
    $customerFinancialProfile = null;
@endphp

@section('styles')

<link rel="stylesheet" href="{{asset(asset_path('backend/css/backend_page_css/staff_create.css'))}}" />

<link rel="stylesheet" href="{{ asset('public/css/register-form.css') }}">
@endsection

@section('mainContent')

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="box_header">
                    <div class="main-title d-md-flex align-items-baseline gap-2">
                        <x-backEnd.back-button :text="false" />
                        <h3 class="mb-0 mr-30">{{ __('common.add_new') }} {{ __('common.customer') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="white_box_30px box_shadow_white">
                    <x-FormWizard.multistep-form
                    action="{{ route('admin.customer.store') }}"
                    method="POST"
                    id="staff_addForm"
                    class="general-register-form"
                    autocomplete="off"
                    enctype="multipart/form-data"
                    :steps="[
                        'common.basic_data',
                        'common.additional_information',
                        'common.financial_information',
                        'common.security'
                    ]">
                        <x-FormWizard.multistep-step step="1">
                            @csrf
                            <!-- Documents -->
                            @include('register.register_documents')
                            {{-- Información del representante --}}
                            @include('register.register_representant_info')
                            <!-- Informacion de basica -->
                            @include('register.register_basic_info')
                        </x-FormWizard.multistep-step>
                        <x-FormWizard.multistep-step step="2">
                            <!-- Informacion de contacto -->
                            @include('register.register_contact_info')
                        
                            <!-- Informacion personal -->
                            @include('register.register_personal_info')
                            
                            <!-- Labor Info -->
                            @include('register.register_labor_information')
                            
                        </x-FormWizard.multistep-step>
                        <x-FormWizard.multistep-step step="3">
                            <!-- Informacion bancaría -->
                            @include('register.register_bank_information')
                            {{-- Información financiera --}}
                            @include('register.register_financial_information')
                            <!-- Informacion tributaria -->
                            @include('register.register_tax_information')
                            {{-- Información moneda extrangera --}}
                            @include('register.register_foreign_currency')

                        </x-FormWizard.multistep-step>
                        <x-FormWizard.multistep-step step="4">
                            
                            
                            @include('register.register_password')

                            <div class="col-12">
                                <div class="primary_input">
                                    <label class="primary_input_label" for="">{{ __('common.status') }}</label>
                                    <ul id="theme_nav" class="permission_list sms_list ">
                                        <li>
                                            <label data-id="bg_option" class="primary_checkbox d-flex mr-12 extra_width">
                                                <input name="status" id="status_active" value="1" checked="true" class="active"
                                                    type="radio">
                                                <span class="checkmark"></span>
                                                <p>{{ __('common.active') }}</p>
                                            </label>
                                        </li>
                                        <li>
                                            <label data-id="color_option" class="primary_checkbox d-flex mr-12 extra_width">
                                                <input name="status" value="0" id="status_inactive" class="de_active" type="radio">
                                                <span class="checkmark"></span>
                                                <p>{{ __('common.inactive') }}</p>
                                            </label>
                                        </li>
                                    </ul>
                                    <span class="text-danger" id="error_status"></span>
                                </div>
                            </div>

                        </x-FormWizard.multistep-step>
                        <x-slot name="actions">
                            <div class="d-flex justify-content-center">
                                <button type="button"
                                        class="btn-toolkit btn-secondary-outline btn-icon mx-2"
                                        data-action="prev">
                                        <i class="ti-angle-left mr-2"></i>
                                    {{__('common.back')}}
                                </button>

                                <button type="button"
                                        class="btn-toolkit btn-secondary-outline btn-icon mx-2"
                                        data-action="next"
                                        >
                                        {{__('common.next')}}
                                        <i class="ti-angle-right ml-2"></i>
                                </button>
                                <button data-action="submit" type="submit" class="btn-toolkit btn-primary btn-icon d-none"
                                        id="save_button_parent"><i class="ti-check"></i>{{ __('common.create') }}</button>
                            </div>
                            
                        </x-slot>

                    </x-FormWizard.multistep-form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
@push('scripts')
<script>
    const registerTranslations = @json(trans('js'));
</script>
<script src="{{ asset('public/js/nice-ajax.js') }}" defer></script>
<script src="{{ asset('public/js/register-form.js') }}" defer></script>
<script src="{{ asset('public/js/currency-mask.js') }}" defer></script>
<script type="text/javascript">
    (function($){
        "use strict";

        $(document).ready(function(){


        });

    })(jQuery);

</script>
@endpush
