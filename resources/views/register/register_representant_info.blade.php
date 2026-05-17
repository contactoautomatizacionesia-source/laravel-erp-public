<!-- Basic info of register and update -->
<div id="section_represent_information" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class=" section-title">{{__('common.represent_information')}}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $representativeId = old('representative', $customerProfile->representative->id ?? '');
            $representativeName = old('representative_name', $customerProfile->representative->name ?? '');
            $representativeCode = $representativeId
                ? (\Modules\Marketing\Entities\ReferralCode::where('user_id', $representativeId)->where('status', 1)->value('referral_code') ?? '')
                : '';
        @endphp
        <label class="general-lable" for="representative">{{label_case_custom(__('common.representative'))}} *</label>
        <select
        name="representative"
        id="representative"
        data-validate="required"
        class="nice-select-ajax style2 wide"
        data-url="{{ url('/customer/get-active-customers') }}"
        data-initial="true"
        data-sync-text="true"
        data-text-target="representative_name"
        >
            @if($representativeId && $representativeName)
                <option value="{{ $representativeId }}" data-code="{{ $representativeCode }}" selected>
                    {{ $representativeName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('representative') }}</span>
        <input type="hidden"
            name="representative_name"
            id="representative_name"
            value="{{ $representativeName ?? '' }}">
    </div>

    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $existingReferralCode = old('referral_code',
                \Modules\Marketing\Entities\ReferralCode::where('user_id', $representativeId ?: null)
                    ->where('status', 1)
                    ->value('referral_code') ?? ''
            );
        @endphp
        <label class="general-lable" for="referral_code">{{label_case_custom(__('common.referral_code'))}}  </label>
        <input name="referral_code" id="referral_code" value="{{ $existingReferralCode }}" placeholder="{{ __('common.referral_code') }}" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('referral_code') }}</span>
    </div>

    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="registration_date">{{label_case_custom(__('common.date_of_register'))}} *</label>
        <input data-validate="required" name="registration_date" id="registration_date"
        value="{{ old('registration_date', (isset($customerProfile->registration_date) && $customerProfile->registration_date) ? \Carbon\Carbon::parse($customerProfile->registration_date)->format('Y-m-d') : '') }}"
        placeholder="{{ __('common.date_of_register') }}"  class="general-input radius_5px" type="date">
        <span class="text-danger" >{{ $errors->first('registration_date') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="contract_type_id">{{label_case_custom(__('common.contract_type'))}} *</label>
        <select data-validate="required" name="contract_type_id" id="contract_type_id" class="nice-select-regular w-100 ">
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($contractTypes as $key => $type)
            <option value="{{ $type->id }}" @if(old('contract_type_id') || $customerProfile?->contract_type_id==$type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('contract_type_id') }}</span>
    </div>
    <script>
        $(document).on('change', '#representative', function () {
            const $selected = $(this).find('option:selected');
            const code = $selected.data('code') ?? '';
            $('#referral_code').val(code);
        });
    </script>
    
</div>


