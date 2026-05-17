@extends('backEnd.master')
@section('mainContent')
@if(isModuleActive('FrontendMultiLang'))
@php
$LanguageList = getLanguageList();
@endphp
@endif
<x-admin.section class="ign-customer-list">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <x-backEnd.back-button :text="false" />
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('general_settings.Email Template')}}</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="white_box_30px box_shadow_white">
                    <form action="{{route('email_templates.update', $email_template->id)}}" method="post">
                        @csrf
                        <!-- content  -->
                        <div class="row">
                            @if(isModuleActive('FrontendMultiLang'))
                                <div class="col-lg-12">
                                    <ul class="nav nav-tabs justify-content-start mt-sm-md-20 mb-30 grid_gap_5" role="tablist">
                                        @foreach ($LanguageList as $key => $language)
                                            <li class="nav-item">
                                                <a class="nav-link show_value anchore_color @if (auth()->user()->lang_code == $language->code) active @endif" data-value='#value{{ $language->code }}' href="#element{{$language->code}}" role="tab" data-toggle="tab" aria-selected="@if (auth()->user()->lang_code == $language->code) true @else false @endif">{{ $language->native }} </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="tab-content">
                                        @foreach ($LanguageList as $key => $language)
                                            <div role="tabpanel" class="tab-pane fade @if (auth()->user()->lang_code == $language->code) show active @endif" id="element{{$language->code}}">
                                                <div class="col-xl-12">
                                                    <div class="primary_input mb-25">
                                                        <label class="primary_input_label" for="">{{__('general_settings.subject')}} <span class="text-danger">*</span></label>
                                                        <input type="text" name="subject[{{$language->code}}]" class="primary_input_field" placeholder="{{__('general_settings.subject')}}" value="{{isset($email_template)?$email_template->getTranslation('subject',$language->code):old('subject.'.$language->code)}}">
                                                        <span class="text-danger">{{$errors->first('subject')}}</span>
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
                                        <input type="text" name="subject" class="primary_input_field" placeholder="{{__('general_settings.subject')}}" value="{{ $email_template->subject }}">
                                        <span class="text-danger">{{$errors->first('subject')}}</span>
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-12">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="">{{__('common.type')}} <span class="text-danger">*</span></label>
                                    {{-- Eliminamos el name="subject" para evitar conflictos al guardar y usamos la traducción dinámica --}}
                                    <input type="text" class="primary_input_field"
                                        value="{{ __('template.' . $email_template->email_template_type->type) }} {{ ($email_template->relatable_type != null) ? '( '.$email_template->relatable->name.' )' : '' }}"
                                        disabled>

                                    <span class="text-danger">{{$errors->first('type_id')}}</span>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="">
                                        {{__('general_settings.reciepent')}}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="primary_select mb-25" name="reciepnt_type[]" id="reciepnt_type" multiple>
                                        <option value="customer" @if (in_array("customer", json_decode($email_template->reciepnt_type))) selected @endif>{{__('general_settings.customer')}}</option>
                                        <option value="admin" @if (in_array("admin", json_decode($email_template->reciepnt_type))) selected @endif>{{__('common.admin')}}</option>
                                        @if(isModuleActive('MultiVendor'))
                                          <option value="seller" @if (in_array("seller", json_decode($email_template->reciepnt_type))) selected @endif>{{__('general_settings.seller')}}</option>
                                        @endif
                                    </select>
                                    <span class="text-danger">{{$errors->first('reciepnt_type')}}</span>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="">{{__('general_settings.short_code')}} <small>({{__('general_settings.use_these_to_get_your_neccessary_info')}})</small> </label>
                                    <label class="primary_input_label red_text" for="">{{ $email_template->short_codes }}</label>
                                </div>
                            </div>

                        @if(isModuleActive('FrontendMultiLang'))
                            @foreach ($LanguageList as $key => $language)
                                <div class="col-xl-12 value_place @if (auth()->user()->lang_code != $language->code) d-none @endif" id="value{{ $language->code }}">
                                    <div class="primary_input mb-25">
                                        <label class="primary_input_label" for="">{{__('general_settings.template')}} ({{ $language->code }})</label>
                                        <textarea name="template[{{ $language->code }}]" class="summernote" placeholder="" >{{ $email_template->getTranslation('value',$language->code) }}</textarea>
                                        <span class="text-danger">{{$errors->first('template')}}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                        <div class="col-xl-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label" for="">{{__('general_settings.template')}}</label>
                                <textarea name="template" class="summernote" placeholder="" >{{ $email_template->value }}</textarea>
                                <span class="text-danger">{{$errors->first('template')}}</span>
                            </div>
                        </div>
                        @endif

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
</x-admin.section>
@endsection
@push('scripts')
    <script type="text/javascript">
        (function($){
            "use strict";
            $(document).ready(function() {
                $('.summernote').summernote({
                    placeholder: '',
                    tabsize: 5,
                    minHeight: 600,
                    maxHeight: 800,
                    codeviewFilter: true,
                    codeviewIframeFilter: true,
                    callbacks: {
                        onImageUpload: function (files) {
                            sendFile(files, '.summernote')
                        }
                    }
                });

                $(document).on('click','.show_value',function(){
                     let value_show = $(this).attr('data-value');
                     $(".value_place").addClass('d-none');
                     $(value_show).removeClass('d-none');
                })
            });
        })(jQuery);
    </script>
@endpush
