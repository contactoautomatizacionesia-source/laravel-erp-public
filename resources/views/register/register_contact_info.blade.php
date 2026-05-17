<div id="section_ubication_contact" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class=" section-title">{{ __('common.ubication and contact') }}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $countryId = old('country', $customer->customerAddress?->country ?? '');
            $countryName = old('country_name', $customer->customerAddress?->getCountry->name ?? '');
        @endphp
        <label class="general-lable" for="country">{{label_case_custom(__('common.country'))}} *</label>
        <select
        name="country"
        id="country"
        class="nice-select-ajax style2 wide"
        autocomplete="off"
        data-validate="required"
        data-url="{{ url('/location/country/search') }}"
        data-initial="true"
        data-sync-text="true"
        data-text-target="country_name"
        >
            @if($countryId && $countryName)
                <option value="{{ $countryId }}" selected>
                    {{ $countryName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('country') }}</span>
        <input type="hidden"
            name="country_name"
            id="country_name"
            value="{{ $countryName ?? '' }}">
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $stateId = old('state', $customer->customerAddress?->state ?? '');
            $stateName = old('state_name', $customer->customerAddress?->getState->name ?? '');
        @endphp
        <label class="general-lable" for="state">{{label_case_custom(__('common.state'))}} *</label>
        <select
        name="state"
        id="state"
        data-validate="required"
        class="nice-select-ajax style2 wide"
        data-url="{{ url('/location/state/search') }}"
        data-depends-on="country"
        data-param="country_id"
        data-sync-text="true"
        data-text-target="state_name"
        >
            @if($stateId && $stateName)
                <option value="{{ $stateId }}" selected>
                    {{ $stateName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('state') }}</span>
        <input type="hidden"
            name="state_name"
            id="state_name"
            value="{{ $stateName ?? '' }}">
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $cityId = old('city', $customer->customerAddress?->city ?? '');
            $cityName = old('city_name', $customer->customerAddress?->getCity->name ?? '');
        @endphp
        <label class="general-lable" for="city">{{label_case_custom(__('common.city'))}} *</label>
        <select
        name="city"
        id="city"
        data-validate="required"
        class="nice-select-ajax style2 wide"
        data-url="{{ url('/location/city/search') }}"
        data-depends-on="state"
        data-param="state_id"
        data-sync-text="true"
        data-text-target="city_name"
        >
            @if($cityId && $cityName)
                <option value="{{ $cityId }}" selected>
                    {{ $cityName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('city') }}</span>
        <input type="hidden"
            name="city_name"
            id="city_name"
            value="{{ $cityName ?? '' }}">
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="address">{{label_case_custom(__('common.address'))}} *</label>
        <input data-validate="required|minLength:6" name="address" id="address" value="{{ old('address', $customer->customerAddress?->address ?? '') }}" placeholder="{{ __('common.address') }}"  class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('address') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="whatsapp">{{label_case_custom(__('common.whatsapp'))}} *</label>
        <div class="d-flex wrap-code-phone">
            <select name="whatsapp_country_code_id"
                    class="nice-select-regular">
                @foreach ($countryPhoneCodes as $key => $type)
                <option value="{{ $type->id }}" @if(old('whatsapp_country_code_id', $customerProfile?->whatsapp_country_code_id ?? '57') == $type->id) selected @endif>
                    {{ $type->name }}
                </option>
                @endforeach
            </select>
            <input
            data-validate="required|phone|data_unique"
            name="whatsapp"
            id="whatsapp"
            value="{{ old('whatsapp', $customerProfile->whatsapp ?? '') }}"
            placeholder = "3001234567"
            class="general-input radius_5px" type="tel">
            
        </div>
        <span class="text-danger" >{{ $errors->first('whatsapp') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="phone_calls">{{label_case_custom(__('common.phone_calls_(optional)'))}}</label>
        <div class="d-flex wrap-code-phone">
            <select name="phone_calls_code_id"
                    class="nice-select-regular">
                @foreach ($countryPhoneCodes as $key => $type)
                <option value="{{ $type->id }}" @if(old('phone_calls_code_id', $customerProfile?->phone_calls_code_id ?? '57') == $type->id) selected @endif>
                    {{ $type->name }}
                </option>
                @endforeach
            </select>
            <input
            data-validate="phone|data_unique"
            name="phone_calls"
            id="phone_calls"
            value="{{ old('phone_calls', $customerProfile->phone_calls ?? '') }}"
            placeholder = "3001234567"
            class="general-input radius_5px" type="tel">
        </div>
        <span class="text-danger" >{{ $errors->first('phone_calls') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="phone_office">{{label_case_custom(__('common.phone_office_(optional)'))}}</label>
        <div class="d-flex wrap-code-phone">
            <select name="phone_office_code_id"
                class="nice-select-regular ">
                
                @foreach ($countryPhoneCodes as $key => $type)
                <option value="{{ $type->id }}" @if(old('phone_office_code_id', $customerProfile?->phone_office_code_id ?? '57') == $type->id) selected @endif>
                    {{ $type->name }}
                </option>
                @endforeach
            </select>
            <input
            data-validate="phone|data_unique"
            name="phone_office"
            id="phone_office"
            value="{{ old('phone_office', $customerProfile->phone_office ?? '') }}"
            placeholder = "3001234567"
            class="general-input radius_5px" type="tel">
            
        </div>
        <span class="text-danger" >{{ $errors->first('phone_office') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="email">{{label_case_custom(__('common.email'))}} *</label>
        <input data-validate="required|email|data_unique" name="email" id="email" value="{{ old('email', $customer->email ?? '') }}" placeholder="{{ __('common.email') }}" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('email') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="secondary_email">{{label_case_custom(__('common.secondary_email_(optional)'))}} </label>
        <input data-validate="email|data_unique" name="secondary_email" id="secondary_email" value="{{ old('secondary_email', $customerProfile->secondary_email ?? '') }}" placeholder="{{ __('common.secondary_email') }}" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('secondary_email') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="civil_status_id">{{label_case_custom(__('common.civil_status_id'))}} *</label>
        <select
        name="civil_status_id"
        data-validate="required"
        id="civil_status_id"
        class="nice-select-regular w-100 "
        >
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($maritalStatus as $key => $type)
            <option value="{{ $type->id }}" @if(old('civil_status_id', $customerProfile?->civil_status_id ?? '') == $type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('civil_status_id') }}</span>
    </div>
</div>
