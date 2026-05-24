<div class="main-title">
    <h3 class="mb-20">
        {{__('common.edit')}} {{__('common.country')}}</h3>
</div>



<form enctype="multipart/form-data" id="edit_form">
    <div class="white-box mb-5">
        <div class="row">
            <input type="hidden" name="id" value="{{$country->id}}">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label"
                        for="name">{{ __('common.name') }} <span class="text-danger">*</span></label>
                    <input name="name" class="primary_input_field name"
                        id="name" placeholder="{{ __('common.name') }}" value="{{$country->name}}"
                        type="text">
                    <span class="text-danger"  id="error_name"></span>
                </div>
            </div>
            @if(isModuleActive('intshipping'))
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label"
                        for="name">{{ __('common.continent') }} <span class="text-danger">*</span></label>
                        <select name="continent_id" id="continent" class="primary_select mb-15">
                            <option value="">{{__('common.select_one')}}</option>
                            @foreach ($continents as $key => $continent)
                                <option value="{{ $continent->id }}" {{ $continent->id == $country->continent_id ? 'selected' : ''}}>{{ $continent->name }}</option>
                            @endforeach
                        </select>
                    <span class="text-danger"  id="error_continent"></span>
                </div>
            </div>
            @endif

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label"
                        for="code">{{ __('setup.code') }} <span class="text-danger">*</span></label>
                    <input name="code" class="primary_input_field code"
                        id="code" placeholder="{{ __('setup.code') }}"
                        type="text" value="{{$country->code}}">
                    <span class="text-danger"  id="error_code"></span>
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label"
                        for="phonecode">{{ __('setup.phonecode') }} <span class="text-danger">*</span></label>
                    <input name="phonecode" class="primary_input_field phonecode"
                        id="phonecode" placeholder="{{ __('setup.phonecode') }}"
                        type="text" value="{{$country->phonecode}}">
                    <span class="text-danger"  id="error_phonecode"></span>
                </div>
            </div>

            <div id="countryFlagFileDiv" class="col-lg-8">
                <div class="primary_input mb-25">
                    <label class="primary_input_label" for="">{{ __('setup.flag') }} ({{getNumberTranslate(61)}}x{{getNumberTranslate(36)}}) {{__('common.px')}}</label>
                    <div class="primary_file_uploader">
                        <input class="primary-input" type="text" id="flag_file"
                            placeholder="{{__('common.browse_image')}}" readonly="">
                        <button class="" type="button">
                            <label class="primary-btn small fix-gr-bg"
                                for="flag">{{ __('common.browse') }} </label>
                            <input type="file" class="d-none" name="flag" id="flag" accept="image/jpeg,image/png,image/jpg,image/gif">
                        </button>
                    </div>
                </div>

                <span class="text-danger" id="error_flag"></span>

            </div>
            <div class="col-lg-4" id="createCountryFlagDiv">
                <div class="flag_img_div position-relative">
                    <img id="FlagPreview" class="flag-preview-img"
                        src="{{ showImage($country->flag?$country->flag:'flags/no_image.png') }}" alt="" style="object-fit: cover; width: 61px; height: 36px; border-radius: 4px; border: 1px solid #ddd;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute clear_flag_btn {{$country->flag ? '' : 'd-none'}}" style="top: -10px; right: -10px; border-radius: 50%; width: 25px; height: 25px; padding: 0; line-height: 1;">&times;</button>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="primary_input">
                    <label class="primary_input_label" for="">{{ __('common.status') }}</label>
                    <ul id="theme_nav" class="permission_list sms_list ">
                        <li>
                            <label data-id="bg_option"
                                   class="primary_checkbox d-flex mr-12">
                                <input name="status" id="status_active" value="1" {{$country->status == 1?'checked':''}} class="active" type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('common.active') }}</p>
                        </li>
                        <li>
                            <label data-id="color_option"
                                   class="primary_checkbox d-flex mr-12">
                                <input name="status" value="0" id="status_inactive" {{$country->status == 0?'checked':''}} class="de_active"
                                       type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('common.inactive') }}</p>
                        </li>
                    </ul>
                    <span class="text-danger" id="status_error"></span>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="primary_input">
                    <label class="primary_input_label" for="is_default">{{ __('setup.default') }}</label>
                    <label class="switch_toggle" for="is_default">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" id="is_default" value="1" {{ (int) $country->is_default === 1 ? 'checked' : '' }}>
                        <div class="slider round"></div>
                    </label>
                </div>
            </div>

            <div class="col-lg-12 text-center">
                <div class="d-flex justify-content-center pt_20">
                    <button type="submit" class="btn-toolkit btn-primary btn-icon">
                        <span class="ti-check"></span>
                        {{ __('common.update') }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>
