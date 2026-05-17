@php
    $isEdit = isset($customer) && $customer->id;
@endphp
<div id="section_security" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class="section-title text-uppercase">{{ __('common.security') }}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="password">{{ label_case_custom(__('common.password')) }} * </label>
        <input
        name="password"
        id="password" placeholder="{{__('amazy.Min. 8 Character')}}"
        class="general-input radius_5px"
        type="password"
        autocomplete="off"
        @if(!$isEdit)
            data-validate="required|minLength:8"
        @else
            data-validate="minLength:8"
        @endif

        >

        <span class="text-danger" >{{ $errors->first('password') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="password-confirm">{{ label_case_custom(__('common.confirm_password'))}} * </label>
        <input
        name="password_confirmation"
        id="password-confirm"
        placeholder="{{__('amazy.Min. 8 Character')}}"
        class="general-input radius_5px" type="password"
        @if(!$isEdit)
            data-validate="required|passwordMatch"
        @else
            data-validate="passwordMatch"
        @endif
        >
        <span class="text-danger" >{{ $errors->first('password_confirmation') }}</span>
    </div>
    <div class="mt-2 col-12">
        <ul class="list-unstyled mt-2 mb-0 small" id="passwordRequirements">
            <li id="req-length" class="text-muted">
                • {{__('common.minimum_8_charecter')}}
            </li>
            
        </ul>
    </div>
</div>
