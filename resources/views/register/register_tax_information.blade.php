<!-- Basic info of register and update -->
<div id="section_tax_information" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class=" section-title">{{__('common.tax_information')}}</h3>
    </div>
    <!-- Responsabilidad IVA -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="iva_responsibility">{{label_case_custom(__('common.vat_responsibility'))}} *</label>
        <select data-validate="required" name="iva_responsibility" id="iva_responsibility" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('iva_responsibility', $customerFinancialProfile->iva_responsibility ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('iva_responsibility', $customerFinancialProfile->iva_responsibility ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('iva_responsibility') }}</span>
    </div>

    <!-- Agente Retención Renta -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="rent_retention_agent">{{label_case_custom(__('common.income_tax_withholding_agent'))}} *</label>
        <select data-validate="required" name="rent_retention_agent" id="rent_retention_agent" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('rent_retention_agent', $customerFinancialProfile->rent_retention_agent ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('rent_retention_agent', $customerFinancialProfile->rent_retention_agent ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('rent_retention_agent') }}</span>
    </div>

    <!-- Agente Retención ICA -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="ica_retention_agent">{{label_case_custom(__('common.ica_withholding_agent'))}} *</label>
        <select data-validate="required" name="ica_retention_agent" id="ica_retention_agent" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('ica_retention_agent', $customerFinancialProfile->ica_retention_agent ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('ica_retention_agent', $customerFinancialProfile->ica_retention_agent ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('ica_retention_agent') }}</span>
    </div>

    <!-- Responsable impuestos ventas -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="sales_tax_responsible">{{label_case_custom(__('common.sales_tax_responsible'))}} *</label>
        <select data-validate="required" name="sales_tax_responsible" id="sales_tax_responsible" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('sales_tax_responsible', $customerFinancialProfile->sales_tax_responsible ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('sales_tax_responsible', $customerFinancialProfile->sales_tax_responsible ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('sales_tax_responsible') }}</span>
    </div>

    <!-- Gran contribuyente -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="grand_contributor">{{label_case_custom(__('common.large_taxpayer'))}} *</label>
        <select data-validate="required" name="grand_contributor" id="grand_contributor" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('grand_contributor', $customerFinancialProfile->grand_contributor ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('grand_contributor', $customerFinancialProfile->grand_contributor ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('grand_contributor') }}</span>
    </div>

    <!-- Autorretenedor -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="self_withholder">{{label_case_custom(__('common.self_withholding_agent'))}} *</label>
        <select data-validate="required" name="self_withholder" id="self_withholder" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('self_withholder', $customerFinancialProfile->self_withholder ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('self_withholder', $customerFinancialProfile->self_withholder ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('self_withholder') }}</span>
    </div>

    <!-- Retención en la fuente -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="source_retention">{{label_case_custom(__('common.withholding_tax'))}} *</label>
        <select data-validate="required" name="source_retention" id="source_retention" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('source_retention', $customerFinancialProfile->source_retention ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('source_retention', $customerFinancialProfile->source_retention ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('source_retention') }}</span>
    </div>

    <!-- Motivo no retención -->
    <div class="reg-group col-12 col-md-8 mb_20">
        <label class="general-lable" for="retention_reason">{{label_case_custom(__('common.non_withholding_reason_explanation'))}} *</label>
        <input
            data-validate="required_if:source_retention,NO|alphaNumeric"
            name="retention_reason"
            id="retention_reason"
            value="{{ old('retention_reason', $customerFinancialProfile->retention_reason ?? '') }}"
            placeholder="Explique el motivo"
            class="general-input radius_5px"
            type="text">
        <span class="text-danger">{{ $errors->first('retention_reason') }}</span>
    </div>

    <!-- ICA -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="ica_tax">{{label_case_custom(__('common.industry_and_commerce_tax_ica'))}} *</label>
        <select data-validate="required" name="ica_tax" id="ica_tax" class="nice-select-regular w-100">
            <option value="SI" {{ old_bool('ica_tax', $customerFinancialProfile->ica_tax ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('ica_tax', $customerFinancialProfile->ica_tax ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger">{{ $errors->first('ica_tax') }}</span>
    </div>

    <!-- Tarifa ICA -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="ica_rate">{{label_case_custom(__('common.industry_and_commerce_tax_rate'))}} *</label>
        <input
            data-validate="required_if:ica_tax,SI|numeric"
            name="ica_rate"
            id="ica_rate"
            value="{{ old('ica_rate', $customerFinancialProfile->ica_rate ?? '') }}"
            placeholder="Ej: 9.66"
            class="general-input radius_5px"
            type="number"
            step="0.01">
        <span class="text-danger">{{ $errors->first('ica_rate') }}</span>
    </div>

    <!-- Ciudad declaración -->
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $cityDeclarationId = old('declaration_city_id', $customerFinancialProfile?->declaration_city_id ?? '');
            $cityDeclarationName = old(
                'declaration_city_name',
                $customerFinancialProfile?->declarationCity?->name &&
                $customerFinancialProfile?->declarationCity?->state?->name &&
                $customerFinancialProfile?->declarationCity?->state?->country?->name
                    ? $customerFinancialProfile?->declarationCity->name . ' - ' .
                    $customerFinancialProfile?->declarationCity->state->name . ' - ' .
                    $customerFinancialProfile?->declarationCity->state->country->name
                    : ''
            );
        @endphp
        <label class="general-lable" for="declaration_city_id">{{label_case_custom(__('common.city_declaration'))}} *</label>
        <select
        name="declaration_city_id"
        id="declaration_city_id"
        class="nice-select-ajax style2 wide"
        data-url="{{ url('/location/city/search-for-select') }}"
        data-initial="true"
        data-sync-text="true"
        data-text-target="declaration_city_name"
        >
            @if($cityDeclarationId && $cityDeclarationName)
                <option value="{{ $cityDeclarationId }}" selected>
                    {{ $cityDeclarationName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
        </select>
        <span class="text-danger" >{{ $errors->first('declaration_city_id') }}</span>
        <input type="hidden"
            name="declaration_city_name"
            id="declaration_city_name"
            value="{{ $cityDeclarationName ?? '' }}">
    </div>

    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="has_rut">{{label_case_custom(__('common.rut'))}} *</label>
        <select data-validate="required" name="has_rut" id="has_rut" class="nice-select-regular w-100 ">
            <option value="SI" {{ old_bool('has_rut', $customerFinancialProfile->has_rut ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('has_rut', $customerFinancialProfile->has_rut ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger" >{{ $errors->first('has_rut') }}</span>
    </div>

    <div class="reg-group col-12 col-md-6 mb_20">
        <label class="general-label" for="rut_file">{{ label_case_custom(__('common.rut_file') )}}</label>

        <div class="custom-file">
            <input
                type="file"
                name="rut_file"
                class="custom-file-input"
                accept="application/pdf"
                @if(!$customerFinancialProfile?->rut_file)
                data-validate="required_if:has_rut,SI"
                @endif
            >

             <span class="custom-file-button">
                {{__('common.choose_file')}}
            </span>

            <span class="custom-file-name">
                {{__('common.no_file_selected')}}
            </span>
        </div>
        @if($customerFinancialProfile?->rut_file)
        <div class="file-preview mt-3">
            <a href="{{ digital_file_url($customerFinancialProfile?->rut_file) }}" target="_blank">{{__('common.current_file')}}</a>
        </div>
        @endif
        <span class="text-danger">{{ $errors->first('rut_file') }}</span>
    </div>


</div>
