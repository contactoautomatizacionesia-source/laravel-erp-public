<div id="section_other_information" class="row mx-0 wrap-section-multistep px-xl-5 px-0 py-4 mb-4">
    <div class="col-12 mb-3">
        <h3 class="section-title text-uppercase">{{ __('common.other information') }}</h3>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="economic_activity_id">{{label_case_custom(__('common.economic_activity'))}} *</label>
        <select
        name="economic_activity_id"
        data-validate="required"
        id="economic_activity_id"
        class="nice-select-regular style2 wide"
        >
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($economicActivities as $key => $type)
            <option value="{{ $type->id }}" @if(old('economic_activity_id') || $customerProfile?->economic_activity_id==$type->id) selected @endif>
                {{ $type->display_name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('economic_activity_id') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="profession_id">{{label_case_custom(__('common.profession'))}} *</label>
        <select
        name="profession_id"
        data-validate="required"
        id="profession_id"
        class="nice-select-regular style2 wide"
        
        >
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($professions as $key => $type)
            <option value="{{ $type->id }}" @if(old('profession_id') || $customerProfile?->profession_id==$type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('profession_id') }}</span>
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        @php
            $product_id = old('product_id', $customerProfile?->product_id ?? '');
            $productName = old('product_name', $customerProfile?->product->product_name ?? '');
        @endphp
        <label class="general-lable" for="product_id">{{label_case_custom(__('common.product'))}} *</label>
        <select
        data-validate="required"
        name="product_id"
        id="product_id"
        class="nice-select-ajax style2 wide"
        data-url="{{ url('/products/get-active-products') }}"
        data-initial="true"
        data-sync-text="true"
        data-text-target="product_name"
        >
            @if($product_id && $productName)
                <option value="{{ $product_id }}" selected>
                    {{ $productName }}
                </option>
            @else
                <option value="">{{__('common.please_select')}} </option>
            @endif
            
        </select>
        <span class="text-danger" >{{ $errors->first('product_id') }}</span>
        <input type="hidden"
            name="product_name"
            id="product_name"
            value="{{ $productName ?? '' }}">
    </div>
    <div class="reg-group col-12 col-md-6 col-xl-4 mb_20">
        <label class="general-lable" for="lead_source_id">{{label_case_custom(__('common.find_lifehuni'))}} *</label>
        <select data-validate="required" name="lead_source_id" id="lead_source_id" class="nice-select-regular w-100 ">
            <option value="">{{__('common.please_select')}}</option>
            @foreach ($leadSources as $key => $type)
            <option value="{{ $type->id }}" @if(old('lead_source_id') || $customerProfile?->lead_source_id==$type->id) selected @endif>
                {{ $type->name }}
            </option>
            @endforeach
        </select>
        <span class="text-danger" >{{ $errors->first('lead_source_id') }}</span>
    </div>
</div>

