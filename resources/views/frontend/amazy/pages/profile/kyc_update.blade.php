@extends('frontend.amazy.auth.layouts.app')

@php
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
        /* Ajuste para darle más espacio al formulario multistep en la pantalla dividida */
        .amazy_login_form {
            max-width: 800px !important;
            width: 100%;
            padding: 40px 20px;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('public/css/register-form.css') }}">
    <link rel="stylesheet" href="{{ asset('public/css/ign_custom.css') }}">
@endpush

@section('content')
<div class="amazy_login_area" style="grid-template-columns: 1fr;">
    <div class="amazy_login_area_left d-flex align-items-center justify-content-center">
        <div class="amazy_login_form">
            <a href="{{url('/')}}" class="logo mb-50 d-block text-center">
                <img src="{{showImage(app('general_setting')->logo)}}" alt="{{app('general_setting')->company_name}}" title="{{app('general_setting')->company_name}}">
            </a>
            <div class="text-center mb-4">
                <h3 class="m-0">{{ __('common.update') }} {{ __('common.customer') }}</h3>
                <p class="support_text">{{ __('general_settings.update_customer_desc') }}</p>
            </div>

            <x-FormWizard.multistep-form
                action="{{ route('kyc.update.store') }}"
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
                    {{-- Información del representante --}}
                    {{-- @include('register.register_representant_info') --}}
                    @include('register.register_basic_info')
                </x-FormWizard.multistep-step>

                <x-FormWizard.multistep-step step="2">
                    @include('register.register_contact_info')
                    @include('register.register_personal_info')
                    @include('register.register_labor_information')
                </x-FormWizard.multistep-step>

                <x-FormWizard.multistep-step step="3">
                    @include('register.register_bank_information')
                    {{-- Información financiera --}}
                    @include('register.register_financial_information')
                    @include('register.register_tax_information')
                    {{-- Información moneda extrangera --}}
                    @include('register.register_foreign_currency')
                </x-FormWizard.multistep-step>

                <x-FormWizard.multistep-step step="4">
                    @include('register.register_documents')
                    @include('register.register_password')
                </x-FormWizard.multistep-step>

                <x-slot name="actions">
                    <div class="d-flex justify-content-center gap-4 mt-4">
                        <button type="button"
                                class="btn-toolkit btn-secondary-outline btn-icon mx-1"
                                data-action="prev">
                            <i class="ti-angle-left mr-2"></i>
                            {{__('common.back')}}
                        </button>

                        <button type="button"
                                class="btn-toolkit btn-secondary-outline btn-icon mx-1"
                                data-action="next">
                            {{__('common.next')}}
                            <i class="ti-angle-right ml-2"></i>
                        </button>
                    </div>

                    <div class="col-lg-12 text-center mt-2">
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
@endsection

@push('scripts')
<script>
    const registerTranslations = @json(trans('js'));
</script>
<script src="{{ asset('public/js/nice-ajax.js') }}" defer></script>
<script src="{{ asset('public/js/register-form.js') }}" defer></script>
<script src="{{ asset('public/js/currency-mask.js') }}" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Obtenemos el array mixto (nombres de campos o IDs de secciones)
        const itemsToHide = @json($kyc_hidden_fields ?? []);

        if (itemsToHide.length > 0) {
            itemsToHide.forEach(function(itemName) {
                
                // 1. Buscamos el elemento por ID
                const sectionElement = document.getElementById(itemName);
                
                // DISCRIMINACIÓN CLAVE: Verificamos que exista y que REALMENTE sea una sección
                if (sectionElement && sectionElement.classList.contains('wrap-section-multistep')) {
                    
                    sectionElement.style.setProperty('display', 'none', 'important');
                    sectionElement.classList.add('kyc-hidden-section');
                    
                    // Quitamos la validación a TODOS los inputs dentro de la sección
                    sectionElement.querySelectorAll('input, select, textarea').forEach(function(inputElement) {
                        inputElement.removeAttribute('required');
                        inputElement.removeAttribute('data-validate');
                        if(inputElement.tagName === 'SELECT') {
                            inputElement.classList.remove('required');
                        }
                    });
                } 
                // 2. Si no es una sección (o es un input que casualmente tiene el mismo ID), lo tratamos como campo individual
                else {
                    const inputElement = document.querySelector(`[name="${itemName}"]`);
                    
                    if (inputElement) {
                        // Buscamos el contenedor padre (.reg-group) y lo ocultamos completo
                        const wrapper = inputElement.closest('.reg-group') || inputElement.closest('[class*="col-"]');
                        if (wrapper) {
                            wrapper.style.setProperty('display', 'none', 'important');
                            wrapper.classList.add('kyc-hidden-field');
                        }

                        // Quitamos su validación
                        inputElement.removeAttribute('required');
                        inputElement.removeAttribute('data-validate');
                        if(inputElement.tagName === 'SELECT') {
                            inputElement.classList.remove('required');
                        }
                    }
                }
            });

            // 3. LIMPIEZA INTELIGENTE (Auto-ocultar títulos de secciones vacías)
            document.querySelectorAll('.wrap-section-multistep').forEach(function(section) {
                // Contamos cuántos '.reg-group' (campos) siguen visibles
                const visibleFields = Array.from(section.querySelectorAll('.reg-group')).filter(group => {
                    return window.getComputedStyle(group).display !== 'none';
                });

                // Si no queda ninguno visible, ocultamos la sección completa
                if (visibleFields.length === 0) {
                    section.style.setProperty('display', 'none', 'important');
                }
            });
        }
    });
</script>
@endpush