<div class="modal fade admin-query" id="create_brand_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('product.create_brand') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            
            @if(isModuleActive('FrontendMultiLang'))
                @php
                    $LanguageList = getLanguageList();
                @endphp
            @endif

            <form action="" method="POST" enctype="multipart/form-data" id="create_brand_form">
                <div class="modal-body py-2 px-md-4 px-2">
                    <input type="hidden" name="form_type" value="modal_form">
                    
                    {{-- TARJETA 1: INFORMACIÓN GENERAL --}}
                    <div class="form-card">
                        <h3><i class="ti-info-alt mr-2"></i>{{ __('product.brand_info') ?? 'Información de la Marca' }}</h3>
                        <div class="row">
                            @if(isModuleActive('FrontendMultiLang'))
                                <div class="col-lg-12 mb-3">
                                    <ul class="nav nav-tabs justify-content-start grid_gap_5 border-0">
                                        @foreach ($LanguageList as $key => $language)
                                            <li class="nav-item">
                                                <a class="nav-link default_lang btn-sm border @if (auth()->user()->lang_code == $language->code) active @endif" href="#belement{{$language->code}}" role="tab" data-toggle="tab" aria-selected="{{ auth()->user()->lang_code == $language->code ? 'true' : 'false' }}">{{ $language->native }} </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-lg-12">
                                    <div class="tab-content">
                                        @foreach ($LanguageList as $key => $language)
                                            <div role="tabpanel" class="tab-pane fade @if (auth()->user()->lang_code == $language->code) show active @endif" id="belement{{$language->code}}">
                                                <div class="form-group mb-15">
                                                    <label class="primary_input_label" for="brand_name_{{$language->code}}"> {{__("common.name")}} <span class="text-danger">*</span></label>
                                                    <input class="primary_input_field" name="name[{{$language->code}}]" id="brand_name_{{$language->code}}" placeholder="{{__("common.name")}}" type="text" value="{{old('name')}}">
                                                    <span class="text-danger" id="error_brand_name"></span>
                                                </div>
                                                <div class="form-group mb-15" id="brand_des_div">
                                                    <label class="primary_input_label" for="brand_description_{{$language->code}}"> {{__("common.description")}} </label>
                                                    <textarea class="summernote" id="brand_description_{{$language->code}}" name="description[{{$language->code}}]"></textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="col-lg-12 form-group mb-15">
                                    <label class="primary_input_label" for="brand_name"> {{__("common.name")}} <span class="text-danger">*</span></label>
                                    <input class="primary_input_field" name="name" id="brand_name" placeholder="{{__("common.name")}}" type="text" value="{{old('name')}}">
                                    <span class="text-danger" id="error_brand_name"></span>
                                </div>
                                <div class="col-lg-12 form-group mb-15" id="brand_des_div">
                                    <label class="primary_input_label" for="brand_description"> {{__("common.description")}} </label>
                                    <textarea class="summernote" id="brand_description" name="description"></textarea>
                                </div>
                            @endif
                            
                            <div class="col-lg-12 form-group mb-15">
                                <label class="primary_input_label" for="brand_link"> {{__("product.website_link")}}</label>
                                <input class="primary_input_field" name="link" id="brand_link" placeholder="{{__("product.website_link")}}" type="text" value="{{old('link')}}">
                                <span class="text-danger" id="error_brand_link"></span>
                            </div>
                        </div>
                    </div>

                    {{-- TARJETA 2: SEO --}}
                    <div class="form-card">
                        <h3><i class="ti-world mr-2"></i>{{ __('common.seo_info') ?? 'Información SEO' }}</h3>
                        <div class="row">
                            @if(isModuleActive('FrontendMultiLang'))
                                <div class="col-lg-12 mb-3">
                                    <ul class="nav nav-tabs justify-content-start grid_gap_5 border-0">
                                        @foreach ($LanguageList as $key => $language)
                                            <li class="nav-item">
                                                <a class="nav-link default_lang btn-sm border @if (auth()->user()->lang_code == $language->code) active @endif" href="#bmelement{{$language->code}}" role="tab" data-toggle="tab" aria-selected="{{ auth()->user()->lang_code == $language->code ? 'true' : 'false' }}">{{ $language->native }} </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-lg-12">
                                    <div class="tab-content">
                                        @foreach ($LanguageList as $key => $language)
                                            <div role="tabpanel" class="tab-pane fade @if (auth()->user()->lang_code == $language->code) show active @endif" id="bmelement{{$language->code}}">
                                                <div class="form-group mb-15">
                                                    <label class="primary_input_label" for="brand_meta_title_{{$language->code}}"> {{__("common.meta_title")}}</label>
                                                    <input class="primary_input_field" name="meta_title[{{$language->code}}]" id="brand_meta_title_{{$language->code}}" placeholder="{{__("common.meta_title")}}" type="text" value="{{old('meta_title.'.$language->code)}}">
                                                    <span class="text-danger" id="error_brand_meta_title"></span>
                                                </div>
                                                <div class="form-group mb-15">
                                                    <label class="primary_input_label" for="brand_meta_description_{{$language->code}}"> {{__("common.meta_description")}}</label>
                                                    <textarea class="primary_textarea height_112 meta_description" id="brand_meta_description_{{$language->code}}" placeholder="{{ __('common.meta_description') }}" name="meta_description[{{$language->code}}]" spellcheck="false"> {{old('meta_description.'.$language->code)}}</textarea>
                                                    <span class="text-danger" id="error_brand_meta_description"></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="col-lg-12 form-group mb-15">
                                    <label class="primary_input_label" for="brand_meta_title"> {{__("common.meta_title")}}</label>
                                    <input class="primary_input_field" name="meta_title" id="brand_meta_title" placeholder="{{__("common.meta_title")}}" type="text" value="{{old('meta_title')}}">
                                    <span class="text-danger" id="error_brand_meta_title"></span>
                                </div>
                                <div class="col-lg-12 form-group mb-15">
                                    <label class="primary_input_label" for="brand_meta_description"> {{__("common.meta_description")}}</label>
                                    <textarea class="primary_textarea height_112 meta_description" id="brand_meta_description" placeholder="{{ __('common.meta_description') }}" name="meta_description" spellcheck="false"></textarea>
                                    <span class="text-danger" id="error_brand_meta_description"></span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- TARJETA 3: CONFIGURACIONES Y LOGO --}}
                    <div class="form-card mb-0">
                        <h3><i class="ti-settings mr-2"></i>{{ __('common.status_info') ?? 'Configuraciones y Multimedia' }}</h3>
                        <div class="row align-items-center">
                            
                            <div class="col-lg-6 form-group mb-25">
                                <label class="primary_input_label" for="brand_status">{{ __('common.status') }} <span class="text-danger">*</span></label>
                                <select class="primary_select" name="status" id="brand_status">
                                    <option value="1">{{ __('common.publish') }}</option>
                                    <option value="0">{{ __('common.pending') }}</option>
                                </select>
                                <span class="text-danger" id="error_brand_status"></span>
                            </div>

                            <div class="col-lg-6 form-group mb-25">
                                <label class="primary_input_label" for="active_checkbox1">{{ __('common.is_featured') }}</label>
                                <div>
                                    <label class="switch_toggle" for="active_checkbox1">
                                        <input type="checkbox" id="active_checkbox1" name="featured" checked>
                                        <div class="slider round"></div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-lg-8 form-group upload_photo_div" id="brand_logo_img_div">
                                <label class="primary_input_label" for="Brand_logo">{{__('common.logo')}} (150x150)PX</label>
                                <div class="primary_file_uploader">
                                    <input class="primary-input" type="text" id="logo_file" placeholder="{{__('common.browse_image_file')}}" readonly>
                                    <button class="" type="button">
                                        <label class="btn-toolkit btn-primary btn-sm mb-0" for="Brand_logo">{{__("common.browse")}} </label>
                                        <input type="file" class="d-none" name="logo" id="Brand_logo">
                                    </button>
                                </div>
                                <span class="text-danger" id="error_brand_logo"></span>
                            </div>

                            <div class="col-lg-4 form-group upload_photo_div text-center">
                                <div id="brand_logo_preview_div" class="p-2" style="border: 1px solid #e1e5eb; border-radius: 8px; background-color: #f8f9fa;">
                                    <img id="logoImg" src="{{ showImage('backend/img/default.png') }}" alt="Preview" style="max-height: 80px; max-width: 100%; border-radius: 4px;">
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- FOOTER MODAL --}}
                <div class="modal-footer" style="border-top: 1px solid #eef0f3; background-color: #f8f9fa;">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') ?? 'Cancelar' }}</button>
                    <button class="btn-toolkit btn-primary submit_btn" type="submit" data-toggle="tooltip">
                        <i class="ti-check mr-2"></i>{{__('common.save')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
