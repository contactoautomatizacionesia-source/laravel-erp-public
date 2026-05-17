<div class="modal fade admin-query" id="create_category_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('common.create_category') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            
            @if(isModuleActive('FrontendMultiLang'))
                @php $LanguageList = getLanguageList(); @endphp
            @endif
            
            <form action="" method="POST" enctype="multipart/form-data" id="add_category_form">
                <div class="modal-body py-2 px-md-4 px-2">
                    <input type="hidden" name="form_type" value="modal_form">

                    {{-- TARJETA 1: INFORMACIÓN GENERAL --}}
                    <div class="form-card">
                        <h3><i class="ti-info-alt mr-2"></i>{{ __('product.general_information') ?? 'Información General' }}</h3>
                        <div class="row">
                            
                            @if(isModuleActive('FrontendMultiLang'))
                                <div class="col-lg-12 mb-3">
                                    <ul class="nav nav-tabs justify-content-start grid_gap_5 border-0">
                                        @foreach ($LanguageList as $key => $language)
                                            <li class="nav-item">
                                                <a class="nav-link default_lang btn-sm border @if (auth()->user()->lang_code == $language->code) active @endif" href="#element{{$language->code}}" role="tab" data-toggle="tab" aria-selected="{{ auth()->user()->lang_code == $language->code ? 'true' : 'false' }}">{{ $language->native }} </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-lg-6">
                                    <div class="tab-content">
                                        @foreach ($LanguageList as $key => $language)
                                            <div role="tabpanel" class="tab-pane fade @if (auth()->user()->lang_code == $language->code) show active @endif" id="element{{$language->code}}">
                                                <div class="form-group">
                                                    <label class="primary_input_label" for="category_name_{{$language->code}}">
                                                        {{__('common.name')}} <span class="text-danger">*</span>
                                                    </label>
                                                    <input class="primary_input_field name" type="text" id="category_name_{{$language->code}}" name="name[{{$language->code}}]" autocomplete="off" placeholder="{{__('common.name')}}">
                                                    <span class="text-danger" id="error_category_name"></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="col-lg-6 form-group">
                                    <label class="primary_input_label" for="category_name">
                                        {{__('common.name')}} <span class="text-danger">*</span>
                                    </label>
                                    <input class="primary_input_field name" type="text" id="category_name" name="name" autocomplete="off" placeholder="{{__('common.name')}}">
                                    <span class="text-danger" id="error_category_name"></span>
                                </div>
                            @endif

                            <div class="col-lg-6 form-group">
                                <label class="primary_input_label" for="category_slug">
                                    {{__('common.slug')}} <span class="text-danger">*</span>
                                </label>
                                <input class="primary_input_field slug" type="text" id="category_slug" name="slug" autocomplete="off" placeholder="{{__('common.slug')}}">
                                <span class="text-danger" id="error_category_slug"></span>
                            </div>

                            @if(isModuleActive('MultiVendor'))
                                <div class="col-lg-6 form-group">
                                    <label class="primary_input_label" for="category_commission_rate">
                                        {{__('common.commission_rate')}}
                                    </label>
                                    <input class="primary_input_field commission_rate" type="number" min="0" step="{{step_decimal()}}" value="0" id="category_commission_rate" name="commission_rate" autocomplete="off" placeholder="{{__('common.commission_rate')}}">
                                    <span class="text-danger" id="error_category_commission_rate"></span>
                                </div>
                            @endif

                            <div class="col-lg-6 form-group">
                                <label class="primary_input_label" for="icon">{{__('common.icon')}}</label>
                                <input class="primary_input_field" type="text" id="icon" name="icon" autocomplete="off" placeholder="{{__('common.icon')}}">
                                <span class="text-danger" id="error_category_icon"></span>
                            </div>
                        </div>
                    </div>

                    {{-- TARJETA 2: CONFIGURACIONES --}}
                    <div class="form-card">
                        <h3><i class="ti-settings mr-2"></i>{{ __('product.others_info') ?? 'Configuraciones' }}</h3>
                        <div class="row">
                            <div class="col-lg-6 form-group">
                                <span class="primary_input_label">{{ __('product.searchable') }}</span>
                                <ul class="permission_list sms_list d-flex flex-wrap gap-3">
                                    <li class="mr-3">
                                        <label data-id="bg_option" class="primary_checkbox d-flex mr-2">
                                            <input name="searchable" id="searchable_active" value="1" checked="true" class="active" type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.active') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.active') }}</p>
                                    </li>
                                    <li>
                                        <label data-id="color_option" class="primary_checkbox d-flex mr-2">
                                            <input name="searchable" id="searchable_inactive" value="0" class="de_active" type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.inactive') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.inactive') }}</p>
                                    </li>
                                </ul>
                                <span class="text-danger" id="error_category_searchable"></span>
                            </div>

                            <div class="col-lg-6 form-group">
                                <span class="primary_input_label">{{ __('common.status') }}</span>
                                <ul class="permission_list sms_list d-flex flex-wrap gap-3">
                                    <li class="mr-3">
                                        <label data-id="bg_option" class="primary_checkbox d-flex mr-2">
                                            <input name="status" id="category_status_active" value="1" checked="true" class="active" type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.active') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.active') }}</p>
                                    </li>
                                    <li>
                                        <label data-id="color_option" class="primary_checkbox d-flex mr-2">
                                            <input name="status" value="0" id="category_status_inactive" class="de_active" type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.inactive') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.inactive') }}</p>
                                    </li>
                                </ul>
                                <span class="text-danger" id="error_category_status"></span>
                            </div>

                            <div class="col-lg-6 form-group mt-3">
                                <ul class="permission_list sms_list">
                                    <li>
                                        <label data-id="bg_option" class="primary_checkbox d-flex mr-12">
                                            <input class="in_sub_cat" name="category_type" id="sub_cat" value="subCategory" type="checkbox">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('product.add_as_sub_category') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('product.add_as_sub_category') }}</p>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-lg-6 form-group d-none in_parent_div mt-3" id="sub_cat_div">
                                <label class="primary_input_label" for="parent_id">{{ __('product.parent_category') }} <span class="text-danger">*</span></label>
                                <select class="primary_select" name="parent_id" id="parent_id">
                                    @if(isset($first_category) && $first_category != null)
                                        <option value="{{$first_category->id}}" selected>{{$first_category->name}}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- TARJETA 3: MULTIMEDIA --}}
                    <div class="form-card mb-0">
                        <h3><i class="ti-gallery mr-2"></i>Multimedia</h3>
                        <div class="row align-items-center">
                            <div class="col-lg-8 form-group upload_photo_div" id="category_image_div">
                                <label class="primary_input_label" for="image">{{__('common.upload_photo')}} ({{__('common.file_less_than_1MB')}})</label>
                                <div class="primary_file_uploader">
                                    <input class="primary-input" type="text" id="image_file" placeholder="{{__('common.browse_image_file')}}" readonly>
                                    <button class="" type="button">
                                        <label class="btn-toolkit btn-primary btn-sm mb-0" for="image">{{__("common.browse")}} </label>
                                        <input type="file" class="d-none" name="image" id="image">
                                    </button>
                                </div>
                                <span class="text-danger" id="error_category_image"></span>
                            </div>

                            <div class="col-lg-4 form-group upload_photo_div text-center">
                                <div id="category_image_preview_div" class="p-2" style="border: 1px solid #e1e5eb; border-radius: 8px; background-color: #f8f9fa;">
                                    <img id="catImgShow" src="{{ showImage('backend/img/default.png') }}" alt="Preview" style="max-height: 80px; max-width: 100%; border-radius: 4px;">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER MODAL --}}
                <div class="modal-footer" style="border-top: 1px solid #eef0f3; background-color: #f8f9fa;">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') ?? 'Cancelar' }}</button>
                    <button id="create_btn" type="submit" class="btn-toolkit btn-primary submit_btn" data-toggle="tooltip">
                        <i class="ti-check mr-2"></i>{{__('common.save')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
