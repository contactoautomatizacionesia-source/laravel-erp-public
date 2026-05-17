<!-- Basic info of register and update -->
<div id="section_bank_information" class="row mx-0 wrap-section-multistep px-xl-5 px-2 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class=" section-title">{{__('common.bank_information')}}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="bank">{{label_case_custom(__('common.bank'))}} *</label>
        <select data-validate="required" name="bank" id="bank" class="nice-select-regular w-100 ">
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($banks as $key => $type)
            <option value="{{ $type->id }}" @if(old('bank') || $customerFinancialProfile?->bank_id==$type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('bank') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="account_number">{{label_case_custom(__('common.account_number'))}} * </label>
        <input data-validate="required|integer"    name="account_number" id="account_number" value="{{ old('account_number', $customerFinancialProfile->account_number ?? '') }}" placeholder="{{ __('common.account_number') }}" onfocus="this.placeholder = ''" onblur="this.placeholder = '{{ __('common.account_number') }}'" class="general-input radius_5px" type="text">
        <span class="text-danger" >{{ $errors->first('account_number') }}</span>
    </div>
    
   
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="account_type">{{label_case_custom(__('common.account_type'))}} *</label>
        <select data-validate="required" name="account_type" id="account_type" class="nice-select-regular w-100 ">
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($accountTypes as $key => $type)
            <option value="{{ $type->id }}" @if(old('account_type') || $customerFinancialProfile?->bank_account_type_id==$type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('account_type') }}</span>
    </div>
    
    
</div>
