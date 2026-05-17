@extends('backEnd.master')
@section('mainContent')
@if(isModuleActive('FrontendMultiLang'))
@php
$LanguageList = getLanguageList();
@endphp
@endif
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('general_settings.sms_template')}}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="white_box_30px box_shadow_white">
                    <form action="{{route('sms_templates.update', $sms_template->id)}}" method="post">
                        @csrf
                        <!-- content  -->
                        <div class="row">
                            @if(isModuleActive('FrontendMultiLang'))
                                <div class="col-lg-12">
                                    <ul class="nav nav-tabs justify-content-start mt-sm-md-20 mb-30 grid_gap_5" role="tablist">
                                        @foreach ($LanguageList as $key => $language)
                                            <li class="nav-item">
                                                <a class="nav-link anchore_color @if (auth()->user()->lang_code == $language->code) active @endif" href="#element{{$language->code}}" role="tab" data-toggle="tab" aria-selected="@if (auth()->user()->lang_code == $language->code) true @else false @endif">{{ $language->native }} </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="tab-content">
                                        @foreach ($LanguageList as $key => $language)
                                            <div role="tabpanel" class="tab-pane fade @if (auth()->user()->lang_code == $language->code) show active @endif" id="element{{$language->code}}">
                                                <div class="col-xl-12">
                                                    <div class="primary_input mb-25">
                                                        <label class="primary_input_label" for="">{{__('general_settings.subject')}} <span class="text-danger">*</span> ({{$language->code}})</label>
                                                        <input type="text" name="subject[{{$language->code}}]" class="primary_input_field" placeholder="{{__('general_settings.subject')}}" value="{{isset($sms_template)?$sms_template->getTranslation('subject',$language->code):old('subject.'.$language->code)}}">
                                                        <span class="text-danger">{{$errors->first('subject')}}</span>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12">
                                                    <div class="primary_input mb-25">
                                                        <label class="primary_input_label" for="">{{__('general_settings.short_code')}} <small>({{__('general_settings.use_these_to_get_your_neccessary_info')}})</small> </label>
                                                        <label class="primary_input_label red_text" for="">{GIFT_CARD_NAME}, {SECRET_CODE}, {USER_FIRST_NAME}, {USER_EMAIL}, {ORDER_TRACKING_NUMBER}, {WEBSITE_NAME}</label>
                                                    </div>
                                                </div>
                                                <div class="col-xl-12">
                                                    <div class="primary_input mb-25">
                                                        <label class="primary_input_label" for="">{{__('general_settings.template')}} ({{$language->code}})</label>
                                                        <textarea name="template[{{$language->code}}]" class="form-control primary_input_field" rows="10" placeholder="" >{{isset($sms_template)?$sms_template->getTranslation('value',$language->code):old('template.'.$language->code)}}</textarea>
                                                        <span class="text-danger">{{$errors->first('template')}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="col-xl-12">
                                    <div class="primary_input mb-25">
                                        <label class="primary_input_label" for="">{{__('general_settings.subject')}}</label>
                                        <input type="text" name="subject[en]" class="primary_input_field" placeholder="{{__('general_settings.subject')}}" value="{{isset($sms_template)?$sms_template->getTranslation('subject','en'):old('subject.en')}}">
                                        <span class="text-danger">{{$errors->first('subject')}}</span>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="primary_input mb-25">
                                        <label class="primary_input_label" for="">{{__('general_settings.short_code')}} <small>({{__('general_settings.use_these_to_get_your_neccessary_info')}})</small> </label>
                                        <label class="primary_input_label red_text" for="">{GIFT_CARD_NAME}, {SECRET_CODE}, {USER_FIRST_NAME}, {USER_EMAIL}, {ORDER_TRACKING_NUMBER}, {WEBSITE_NAME}</label>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="primary_input mb-25">
                                        <label class="primary_input_label" for="">{{__('general_settings.template')}}</label>
                                        <textarea name="template[en]" class="form-control primary_input_field" rows="10" placeholder="" >{{isset($sms_template)?$sms_template->getTranslation('subject','en'):old('subject.en')}}</textarea>
                                        <span class="text-danger">{{$errors->first('template')}}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-6">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="">{{__('common.type')}}</label>
                                    <input
                                        type="text"
                                        name="type_id"
                                        class="primary_input_field"
                                        placeholder="{{__('general_settings.subject')}}"
                                        value="{{ __('template.' . $sms_template->templateType->type) }} {{ ($sms_template->relatable_type != null) ? '( '.$sms_template->relatable->name.' )' : '' }}"
                                        disabled
                                    >
                                    <span class="text-danger">{{$errors->first('type_id')}}</span>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="">{{__('general_settings.reciepent')}}</label>
                                    <select class="primary_select mb-25" name="reciepnt_type[]" id="reciepnt_type" multiple>
                                        <option value="customer" @if (in_array("customer", json_decode($sms_template->reciepnt_type))) selected @endif>{{__('general_settings.customer')}}</option>
                                        @if(isModuleActive('MultiVendor'))
                                            <option value="seller" @if (in_array("seller", json_decode($sms_template->reciepnt_type))) selected @endif>{{__('general_settings.seller')}}</option>
                                        @endif
                                    </select>
                                    <span class="text-danger">{{$errors->first('reciepnt_type')}}</span>
                                </div>
                            </div>



                        </div>
                        <div class="submit_btn text-center mb-100 pt_15">
                            <button class="primary_btn_large" type="submit"> <i class="ti-check"></i> {{ __('common.save') }}</button>
                        </div>
                        <!-- content  -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')

@endpush
