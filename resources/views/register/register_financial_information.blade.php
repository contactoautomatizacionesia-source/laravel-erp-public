<div id="section_financial_information" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class="section-title text-uppercase">{{ __('common.financial_information') }}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="monthly_income">{{label_case_custom(__('common.monthly_income'))}} *</label>
        <input data-validate="required|currency" step="1"    name="monthly_income" id="monthly_income" value="{{ old('monthly_income', isset($customerFinancialProfile->monthly_income) ? (int) $customerFinancialProfile->monthly_income : '') }}" placeholder="1.000.000"  class="general-input radius_5px currency-mask" type="text">
        <span class="text-danger" >{{ $errors->first('monthly_income') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="monthly_expenses">{{label_case_custom(__('common.monthly_expenses'))}} *</label>
        <input data-validate="required|currency" step="1"    name="monthly_expenses" id="monthly_expenses" value="{{ old('monthly_expenses', isset($customerFinancialProfile->monthly_expenses) ? (int) $customerFinancialProfile->monthly_expenses : '') }}" placeholder="1.000.000"  class="general-input radius_5px currency-mask" type="text">
        <span class="text-danger" >{{ $errors->first('monthly_expenses') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="other_income">{{label_case_custom(__('common.other_income'))}}</label>
        <input data-validate="currency" step="1"    name="other_income" id="other_income" value="{{ old('other_income', isset($customerFinancialProfile->other_income) ? (int) $customerFinancialProfile->other_income : '') }}" placeholder="1.000.000"  class="general-input radius_5px currency-mask" type="text">
        <span class="text-danger" >{{ $errors->first('other_income') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="other_income_desc">{{label_case_custom(__('common.other_income_desc'))}}</label>
        <input data-validate="required_if:other_income,,notEmpty|alphaNumeric" name="other_income_desc" id="other_income_desc" value="{{ old('other_income_desc', $customerFinancialProfile->other_income_desc ?? '') }}" placeholder="{{ __('common.other_income_desc') }}"  class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('other_income_desc') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="total_assets">{{label_case_custom(__('common.total_assets'))}} *</label>
        <input data-validate="required|currency" step="1"    name="total_assets" id="total_assets" value="{{ old('total_assets', isset($customerFinancialProfile->total_assets) ? (int) $customerFinancialProfile->total_assets : '') }}" placeholder="1.000.000"  class="general-input radius_5px currency-mask" type="text">
        <span class="text-danger" >{{ $errors->first('total_assets') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="total_liabilities">{{label_case_custom(__('common.total_liabilities'))}} *</label>
        <input data-validate="required|currency" step="1"    name="total_liabilities" id="total_liabilities" value="{{ old('total_liabilities', isset($customerFinancialProfile->total_liabilities) ? (int)  $customerFinancialProfile->total_liabilities : '') }}" placeholder="1.000.000"  class="general-input radius_5px currency-mask" type="text">
        <span class="text-danger" >{{ $errors->first('total_liabilities') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="total_equity">{{label_case_custom(__('common.total_equity'))}} *</label>
        <input data-validate="required|currency" step="1"    name="total_equity" id="total_equity" value="{{ old('total_equity', isset($customerFinancialProfile->total_equity) ? (int) $customerFinancialProfile->total_equity : '') }}" placeholder="1.000.000"  class="general-input radius_5px currency-mask" type="text">
        <span class="text-danger" >{{ $errors->first('total_equity') }}</span>
    </div>
</div>

