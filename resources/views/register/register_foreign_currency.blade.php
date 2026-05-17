<!-- Foreign currency operations -->
<div id="section_foreign_currency" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">

    <div class="col-12 mb-3">
        <h3 class=" section-title">{{__('common.foreign_currency_transaction')}}</h3>
    </div>

    <!-- Realiza operaciones en moneda extranjera -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="ops_foreign_currency">{{label_case_custom(__('common.transactions_foreign_currency'))}}</label>
        <select data-validate="required" name="ops_foreign_currency" id="ops_foreign_currency" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('ops_foreign_currency', $customerFinancialProfile->ops_foreign_currency ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('ops_foreign_currency', $customerFinancialProfile->ops_foreign_currency ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('ops_foreign_currency') }}</span>
    </div>

    <!-- ¿Cuáles operaciones? -->
    <div class="reg-group col-12 col-md-8 mb_20">
        <label class="general-lable" for="ops_foreign_desc">{{label_case_custom(__('common.which_operations'))}}</label>
        <input
            data-validate="required_if:ops_foreign_currency,SI|alphaNumeric"
            
            name="ops_foreign_desc"
            id="ops_foreign_desc"
            value="{{ old('ops_foreign_desc', $customerFinancialProfile->ops_foreign_desc ?? '') }}"
            placeholder="{{__('common.which_operations')}}"
            class="general-input radius_5px"
            type="text">
        <span class="text-danger">{{ $errors->first('ops_foreign_desc') }}</span>
    </div>

    <!-- Posee cuentas en moneda extranjera -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="has_foreign_accounts">{{label_case_custom(__('common.has_accounts_foreign_currency'))}}</label>
        <select data-validate="required" name="has_foreign_accounts" id="has_foreign_accounts" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('has_foreign_accounts', $customerFinancialProfile->has_foreign_accounts ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('has_foreign_accounts', $customerFinancialProfile->has_foreign_accounts ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('has_foreign_accounts') }}</span>
    </div>

    <!-- Banco extranjero -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="foreign_bank">{{label_case_custom(__('common.Bank_(Foreign)'))}}</label>
        <input
            data-validate="required_if:has_foreign_accounts,SI|alphaNumeric"
            
            name="foreign_bank"
            id="foreign_bank"
            value="{{ old('foreign_bank', $customerFinancialProfile->foreign_bank ?? '') }}"
            placeholder="{{__('common.Bank_(Foreign)')}}"
            class="general-input radius_5px"
            type="text">
        <span class="text-danger">{{ $errors->first('foreign_bank') }}</span>
    </div>

    <!-- Número de cuenta -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="foreign_account_number">{{label_case_custom(__('common.account_number_(Foreign)'))}}</label>
        <input
            data-validate="required_if:has_foreign_accounts,SI|alphaNumeric"
            
            name="foreign_account_number"
            id="foreign_account_number"
            value="{{ old('foreign_account_number', $customerFinancialProfile->foreign_account_number ?? '') }}"
            placeholder="{{__('common.account_number_(Foreign)')}}"
            class="general-input radius_5px"
            type="text">
        <span class="text-danger">{{ $errors->first('foreign_account_number') }}</span>
    </div>

    <!-- Moneda -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="foreign_currency">{{label_case_custom(__('common.currency'))}}</label>
        <input
            data-validate="required_if:has_foreign_accounts,SI|alphaNumeric"
            
            name="foreign_currency"
            id="foreign_currency"
            value="{{ old('foreign_currency', $customerFinancialProfile->foreign_currency ?? '') }}"
            placeholder="{{__('common.currency')}}"
            class="general-input radius_5px"
            type="text">
        <span class="text-danger">{{ $errors->first('foreign_currency') }}</span>
    </div>

    <!-- País -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $countryDeclarationId = old('foreign_country_id', $customerFinancialProfile?->foreign_country_id ?? '');
            $countryDeclarationName = old('declaration_country_name', $customerFinancialProfile?->foreignCountry?->name ?? '' );
        @endphp
        <label class="general-lable" for="foreign_country_id">{{label_case_custom(__('common.country'))}}</label>
        <select
        name="foreign_country_id"
        id="foreign_country_id"
        class="nice-select-ajax style2 wide"
        autocomplete="off"
        data-validate="required_if:has_foreign_accounts,SI"
        data-url="{{ url('/location/country/search') }}"
        data-initial="true"
        data-sync-text="true"
        data-text-target="declaration_country_name"
        >
            @if($countryDeclarationId && $countryDeclarationName)
                <option value="{{ $countryDeclarationId }}" selected>
                    {{ $countryDeclarationName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('foreign_country_id') }}</span>
        <input type="hidden"
           name="declaration_country_name"
           id="declaration_country_name"
           value="{{ $countryDeclarationName ?? '' }}">
    </div>

    <!-- Ciudad -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $foreingCityId = old('foreign_city_id', $customerFinancialProfile?->foreign_city_id ?? '');
            $foreingCityName = old(
                'foreign_city_name',
                $customerFinancialProfile?->foreignCity?->name &&
                $customerFinancialProfile?->foreignCity?->state?->name &&
                $customerFinancialProfile?->foreignCity?->state?->country?->name
                    ? $customerFinancialProfile?->foreignCity->name . ' - ' .
                    $customerFinancialProfile?->foreignCity->state->name . ' - ' .
                    $customerFinancialProfile?->foreignCity->state->country->name
                    : ''
            );
        @endphp
        <label class="general-lable" for="foreign_city_id">{{label_case_custom(__('common.city'))}}</label>
        <select
        name="foreign_city_id"
        id="foreign_city_id"
        data-validate="required_if:has_foreign_accounts,SI"
        class="nice-select-ajax style2 wide"
        data-url="{{ url('/location/city/search-for-select') }}"
        data-initial="true"
        data-sync-text="true"
        data-text-target="foreign_city_name"
        >
            @if($foreingCityId && $foreingCityName)
                <option value="{{ $foreingCityId }}" selected>
                    {{ $foreingCityName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('foreign_city_id') }}</span>
        <input type="hidden"
           name="foreign_city_name"
           id="foreign_city_name"
           value="{{ $foreingCityName ?? '' }}">
    </div>

</div>
