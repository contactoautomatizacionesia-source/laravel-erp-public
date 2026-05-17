@extends('backEnd.master')

@php
    $customerProfile = $customer?->customerProfile ?? null;
    $customerFinancialProfile = $customer?->customerFinancialProfile ?? null;
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
                        <h3 class="mb-0 mr-30">{{ __('common.update') }} {{ __('common.customer') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="white_box_30px box_shadow_white">
                    <x-FormWizard.multistep-form
                    action="{{ route('admin.customer.update', $customer->id) }}"
                    method="POST"
                    id="staff_addForm"
                    data-customer="{{$customer->id ?? ''}}"
                    class="general-register-form edit-customer-form"
                    enctype="multipart/form-data"
                    autocomplete="off"
                    :steps="[
                        'common.basic_data',
                        'common.aditional_information',
                        'common.financial_information',
                        'common.security'
                    ]"
                    
                    >
                    <x-FormWizard.multistep-step step="1">
                            @csrf
                            <div class="row mx-0 wrap-section-multistep px-lg-5 px-2 py-4 mb-4">
                                <div class="col-xl-4">
                                    <div class="primary_input">
                                        <div class=" mb-3">
                                            <h3 class="section-title ">{{ __('common.status') }}</h3>
                                        </div>
                                        <ul id="theme_nav" class="permission_list sms_list ">
                                            <li>
                                                <label data-id="bg_option" class="primary_checkbox d-flex mr-12 extra_width">
                                                    <input name="status" id="status_active" value="1" {{$customer->is_active == 1?'checked':''}} class="active"
                                                        type="radio">
                                                    <span class="checkmark"></span>
                                                </label>
                                                <p>{{ __('common.active') }}</p>
                                            </li>
                                            <li>
                                                <label data-id="color_option" class="primary_checkbox d-flex mr-12 extra_width">
                                                    <input name="status" value="0" id="status_inactive" class="de_active" type="radio" {{$customer->is_active == 0?'checked':''}}>
                                                    <span class="checkmark"></span>
                                                </label>
                                                <p>{{ __('common.inactive') }}</p>
                                            </li>
                                        </ul>
                                        <span class="text-danger" id="error_status"></span>
                                    </div>
                                </div>

                               
                            </div>

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
                            <!-- Documents -->
                                @include('register.register_documents')
                                
                                @include('register.register_password')

                        </x-FormWizard.multistep-step>
                        <x-slot name="actions">
                            <div class="d-flex justify-content-center gap-4">
                                <button type="button"
                                        class="btn-toolkit btn-secondary-outline btn-icon mx-1  "
                                        data-action="prev">
                                    <i class="ti-angle-left mr-2"></i>
                                    {{__('common.back')}}
                                </button>

                                <button type="button"
                                        class="btn-toolkit btn-secondary-outline btn-icon mx-1  "
                                        data-action="next"
                                        >
                                    {{__('common.next')}}
                                    <i class="ti-angle-right ml-2"></i>
                                </button>
                            </div>

                            <div class="col-lg-12 text-center">
                                <div class="d-flex justify-content-center pt_20">
                                    <button type="submit" class="btn-toolkit btn-primary btn-icon btn-update-customer"
                                        id="save_button_parent">{{ __('common.update') }}<i class="ti-check ml-2"></i></button>
                                </div>
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
