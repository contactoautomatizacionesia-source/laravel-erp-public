<!-- Basic info of register and update -->
<div id="section_labor_information" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class=" section-title">{{__('common.labor_information')}}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="company_name">{{label_case_custom(__('common.company_name'))}}  *</label>
        <input data-validate="required|alphaNumeric"    name="company_name" id="company_name" value="{{ old('company_name', $customerFinancialProfile->company_name ?? '') }}" placeholder="{{ __('common.company_name') }}"  class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('company_name') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="job_title">{{label_case_custom(__('common.job_title'))}}  *</label>
        <input data-validate="required|alphaNumeric"    name="job_title" id="job_title" value="{{ old('job_title', $customerFinancialProfile->job_title ?? '') }}" placeholder="{{ __('common.job_title') }}"class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('job_title') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="work_address">{{label_case_custom(__('common.work_address'))}}  *</label>
        <input data-validate="required|alphaNumeric"    name="work_address" id="work_address" value="{{ old('work_address', $customerFinancialProfile->work_address ?? '') }}" placeholder="{{ __('common.work_address') }}" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('work_address') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="public_resources">{{label_case_custom(__('common.public_resources'))}} *</label>
        <select data-validate="required" name="public_resources" id="public_resources" class="nice-select-regular w-100 ">
            <option value="SI" {{ old_bool('public_resources', $customerFinancialProfile->public_resources ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('public_resources', $customerFinancialProfile->public_resources ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger" >{{ $errors->first('public_resources') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="marital_society">{{label_case_custom(__('common.marital_society'))}} *</label>
        <select data-validate="required" name="marital_society" id="marital_society" class="nice-select-regular w-100 ">
            <option value="SI" {{ old_bool('marital_society', $customerFinancialProfile->marital_society ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('marital_society', $customerFinancialProfile->marital_society ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger" >{{ $errors->first('marital_society') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="is_pep">{{label_case_custom(__('common.is_pep'))}} *</label>
        <select data-validate="required" name="is_pep" id="is_pep" class="nice-select-regular w-100 ">
            <option value="SI" {{ old_bool('is_pep', $customerFinancialProfile->is_pep ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('is_pep', $customerFinancialProfile->is_pep ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger" >{{ $errors->first('is_pep') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="pep_family">{{label_case_custom(__('common.pep_family'))}} *</label>
        <select data-validate="required" name="pep_family" id="pep_family" class="nice-select-regular w-100 ">
            <option value="SI" {{ old_bool('pep_family', $customerFinancialProfile->pep_family ?? '') === 1 ? 'selected' : '' }}>SI</option>
            <option value="NO" {{ old_bool('pep_family', $customerFinancialProfile->pep_family ?? 0 ) === 0 ? 'selected' : '' }}>NO</option>
        </select>
        <span class="text-danger" >{{ $errors->first('pep_family') }}</span>
    </div>
</div>
