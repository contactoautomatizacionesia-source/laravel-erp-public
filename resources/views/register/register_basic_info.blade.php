<!-- Basic info of register and update -->
<div id="section_personal_information" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class=" section-title">{{__('common.personal_information')}}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="first_name">{{label_case_custom(__('common.first_name'))}} *</label>
        <input data-validate="required|alpha"    name="first_name" id="first_name" value="{{ old('first_name', $customer->first_name ?? '') }}" placeholder="{{ __('common.first_name') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.first_name') }}'" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('first_name') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="last_name">{{label_case_custom(__('common.last_name'))}} *</label>
        <input data-validate="required|alpha"  name="last_name" id="last_name" value="{{ old('last_name', $customer->last_name ?? '') }}"  placeholder="{{ __('common.last_name') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.last_name') }}'" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('last_name') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="middle_name">{{label_case_custom(__('common.middle_name_(optional)'))}}</label>
        <input data-validate="alpha"  name="middle_name" id="middle_name" value="{{ old('middle_name', $customer->middle_name ?? '') }}" placeholder="{{ __('common.middle_name') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.middle_name') }}'" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('middle_name') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="document_type_id">{{label_case_custom(__('common.document_type'))}} *</label>
        <select data-validate="required" name="document_type_id" id="document_type_id" class="nice-select-regular w-100 ">
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($typeDocuments as $key => $type)
            <option value="{{ $type->id }}" @if(old('document_type_id') || $customerProfile?->document_type_id==$type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('document_type_id') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="document_number">{{label_case_custom(__('common.document_number'))}} *</label>
        <input data-validate="required|document|data_unique"  name="document_number" id="document_number" value="{{ old('document_number', $customerProfile->document_number ?? '') }}"  placeholder="{{ __('common.document_number') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.document_number') }}'" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('document_number') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="date_of_birth">{{label_case_custom(__('common.date_of_birth'))}} *</label>
        <input data-validate="required|minAge" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', (isset($customerProfile->date_of_birth) && $customerProfile->date_of_birth) ? \Carbon\Carbon::parse($customerProfile->date_of_birth)->format('Y-m-d') : '') }}" placeholder="{{ __('common.date_of_birth') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.date_of_birth') }}'" class="general-input radius_5px" type="date" max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}" min="1900-01-01">
        <span class="text-danger" >{{ $errors->first('date_of_birth') }}</span>
    </div>
</div>
<div id="section_document_information" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class=" section-title">{{__('common.document_information')}}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $oldCityId = old('birth_city_id', $customerProfile?->birth_city_id ?? '');
            $oldCityName = old(
                'birth_city_name',
                $customerProfile?->birthCity?->name &&
                $customerProfile?->birthCity?->state?->name &&
                $customerProfile?->birthCity?->state?->country?->name
                    ? $customerProfile?->birthCity->name . ' - ' .
                    $customerProfile?->birthCity->state->name . ' - ' .
                    $customerProfile?->birthCity->state->country->name
                    : ''
            );
        @endphp

        <label class="general-lable" for="birth_city_id">{{label_case_custom(__('common.place_of_birth'))}} *</label>
        <select
        name="birth_city_id"
        id="birth_city_id"
        data-validate="required"
        class="nice-select-ajax style2 wide "
        data-url="{{ url('/location/city/search-for-select') }}"
        data-initial="true"
        data-sync-text="true"
        data-text-target="birth_city_name"
        >
            @if($oldCityId && $oldCityName)
                <option value="{{ $oldCityId }}" selected>
                    {{ $oldCityName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('birth_city_id') }}</span>
        <input type="hidden"
            name="birth_city_name"
            id="birth_city_name"
            value="{{ $oldCityName ?? '' }}">
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="issue_date">{{label_case_custom(__('common.data_issue_document'))}} *</label>
        <input data-validate="required|issueDateValid" name="issue_date" id="issue_date" value="{{ old('issue_date', (isset($customerProfile->issue_date) && $customerProfile->issue_date) ? \Carbon\Carbon::parse($customerProfile->issue_date)->format('Y-m-d') : '') }}" placeholder="{{ __('common.issue_date') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.issue_date') }}'" class="general-input radius_5px" type="date" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" min="1900-01-01">
        <span class="text-danger" >{{ $errors->first('issue_date') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $oldissueCityId = old('issue_city_id', $customerProfile?->issue_city_id ?? '');
            $oldissueCityName = old(
                'issue_city_name',
                $customerProfile?->issueCity?->name &&
                $customerProfile?->issueCity?->state?->name &&
                $customerProfile?->issueCity?->state?->country?->name
                    ? $customerProfile?->issueCity->name . ' - ' .
                    $customerProfile?->issueCity->state->name . ' - ' .
                    $customerProfile?->issueCity->state->country->name
                    : ''
            );
        @endphp
        <label class="general-lable" for="issue_city_id">{{label_case_custom(__('common.place_issue_document'))}} *</label>
        <select
            name="issue_city_id"
            id="issue_city_id"
            data-validate="required"
            class="nice-select-ajax style2 wide "
            data-url="{{ url('/location/city/search-for-select') }}"
            data-initial="true"
            data-sync-text="true"
            data-text-target="issue_city_name"
        >
            @if($oldissueCityId && $oldissueCityName)
                <option value="{{ $oldissueCityId }}" selected>
                    {{ $oldissueCityName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('issue_city_id') }}</span>
        <input type="hidden"
            name="issue_city_name"
            id="issue_city_name"
            value="{{ $oldissueCityName ?? '' }}">
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="expiration_date">{{label_case_custom(__('common.document_expiration_date_(optional)'))}}</label>
        <input data-validate="expirationDateValid" name="expiration_date" id="expiration_date" value="{{ old('expiration_date', (isset($customerProfile->expiration_date) && $customerProfile->expiration_date) ? \Carbon\Carbon::parse($customerProfile->expiration_date)->format('Y-m-d') : '') }}" placeholder="{{ __('common.expiration_date') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.expiration_date') }}'" class="general-input radius_5px" type="date">
        <span class="text-danger" >{{ $errors->first('expiration_date') }}</span>
    </div>
    
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="gender">{{label_case_custom(__('common.gender'))}} *</label>
        <select data-validate="required" name="gender_id" id="gender" class="nice-select-regular w-100 ">
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($genders as $key => $type)
            <option value="{{ $type->id }}" @if(old('gender_id') || $customerProfile?->gender?->id==$type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('gender') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $nationalityId = old('nationality_id', $customerProfile?->nationality_id ?? '');
            $nationalityName = old('nationality_name', $customerProfile?->nationalityCountry->name ?? '');
        @endphp
        <label class="general-lable" for="nationality_id">{{label_case_custom(__('common.nationality'))}} *</label>
        <select
            name="nationality_id"
            id="nationality_id"
            data-validate="required"
            class="nice-select-ajax style2 wide"
            data-url="{{ url('/location/country/search') }}"
            data-initial="true"
            data-sync-text="true"
            data-text-target="nationality_name"
        >
            @if($nationalityId && $nationalityName)
                <option value="{{ $nationalityId }}" selected>
                    {{ $nationalityName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('nationality') }}</span>
        <input type="hidden"
            name="nationality_name"
            id="nationality_name"
            value="{{ $nationalityName ?? '' }}">
    </div>
</div>

<script>
(function () {
    // Limits per document_type_id: { maxlength, minlength, pattern, inputmode }
    // 1: Cédula de Ciudadanía   → 10 digits
    // 2: Cédula de Extranjería  → 6–12 digits
    // 3: NIT                    → 9–10 digits
    // 4: Pasaporte              → alphanumeric, up to 20 chars
    // 5: Tarjeta de Identidad   → 10–11 digits
    var docRules = {
        '1': { maxlength: 10, minlength: 10, pattern: '[0-9]{10}',         inputmode: 'numeric' },
        '2': { maxlength: 20, minlength: 6,  pattern: '[0-9]{6,20}',       inputmode: 'numeric' },
        '3': { maxlength: 10, minlength: 9,  pattern: '[0-9]{9,10}',       inputmode: 'numeric' },
        '4': { maxlength: 20, minlength: 6,  pattern: '[A-Za-z0-9]{6,20}', inputmode: 'text'    },
        '5': { maxlength: 11, minlength: 10, pattern: '[0-9]{10,11}',      inputmode: 'numeric' },
    };

    var docTypeSelect  = document.getElementById('document_type_id');
    var docNumberInput = document.getElementById('document_number');

    function applyRules(typeId) {
        var rules = docRules[typeId];
        if (rules) {
            docNumberInput.setAttribute('maxlength',  rules.maxlength);
            docNumberInput.setAttribute('minlength',  rules.minlength);
            docNumberInput.setAttribute('pattern',    rules.pattern);
            docNumberInput.setAttribute('inputmode',  rules.inputmode);
            docNumberInput._numericOnly = (rules.inputmode === 'numeric');
        } else {
            docNumberInput.removeAttribute('maxlength');
            docNumberInput.removeAttribute('minlength');
            docNumberInput.removeAttribute('pattern');
            docNumberInput.removeAttribute('inputmode');
            docNumberInput._numericOnly = false;
        }
        if (rules && docNumberInput.value.length > rules.maxlength) {
            docNumberInput.value = docNumberInput.value.slice(0, rules.maxlength);
        }
    }

    docNumberInput.addEventListener('keypress', function (e) {
        if (!this._numericOnly) return;
        if (e.key && !/[0-9]/.test(e.key) && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
        }
    });

    docNumberInput.addEventListener('input', function () {
        var maxlen = parseInt(this.getAttribute('maxlength'));
        if (this._numericOnly) {
            this.value = this.value.replace(/[^0-9]/g, '');
        }
        if (!isNaN(maxlen) && this.value.length > maxlen) {
            this.value = this.value.slice(0, maxlen);
        }
    });

    docTypeSelect.addEventListener('change', function () {
        applyRules(this.value);
        docNumberInput.value = '';
    });

    applyRules(docTypeSelect.value);
})();
</script>
