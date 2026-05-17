<div class="modal fade admin-query" id="create_attribute_modal">
    <div class="modal-dialog modal_1000px modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('product.create_attribute') }}</h4>
                <button type="button" class="close " data-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            @if(isModuleActive('FrontendMultiLang'))
            @php
            $LanguageList = getLanguageList();
            @endphp
            @endif
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data" id="create_attribute_form">
                    <input type="hidden" name="form_type" value="modal_form">
                    <input type="hidden" class="edit_id">
                    <div class="row">
                        <div class="col-lg-8">
                            @if(isModuleActive('FrontendMultiLang'))
                                <ul class="nav nav-tabs justify-content-start mb-15 grid_gap_5" >
                                    @foreach ($LanguageList as $key => $language)
                                        <li class="nav-item">
                                            <a class="nav-link anchore_color @if (auth()->user()->lang_code == $language->code) active @endif" href="#aelement{{$language->code}}" role="tab" data-toggle="tab" aria-selected="@if (auth()->user()->lang_code == $language->code) true @else false @endif">{{ $language->native }} </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content">
                                    @foreach ($LanguageList as $key => $language)
                                        <div role="tabpanel" class="tab-pane fade @if (auth()->user()->lang_code == $language->code) show active @endif" id="aelement{{$language->code}}">
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for=""> {{__('common.name')}} <span class="text-danger">*</span></label>
                                                <input class="primary_input_field" name="name[{{$language->code}}]" id="name" placeholder="{{__('common.name')}}" type="text" value="{{old('name')}}">
                                                <span class="text-danger" id="name_error"></span>
                                            </div>
                                            <div class="primary_input mb-15">
                                                <label class="primary_input_label" for=""> {{__('common.description')}} </label>
                                                <textarea class="primary_textarea" style="height: 80px" placeholder="{{ __('common.description') }}" name="description[{{$language->code}}]" spellcheck="false"></textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="primary_input mb-15">
                                    <label class="primary_input_label" for=""> {{__('common.name')}} <span class="text-danger">*</span></label>
                                    <input class="primary_input_field" name="name" id="name" placeholder="{{__('common.name')}}" type="text" value="{{old('name')}}">
                                    <span class="text-danger" id="error_attribute_name"></span>
                                </div>
                                <div class="primary_input mb-15">
                                    <label class="primary_input_label" for=""> {{__("common.description")}} </label>
                                    <textarea class="primary_textarea" style="height: 80px" placeholder="{{ __('common.description') }}" name="description" spellcheck="false"></textarea>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-4">
                            <div class="primary_input mb-15">
                                <label class="primary_input_label" for="">{{ __('common.status') }} <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center flex-wrap gap-10">
                                    <label class="primary_checkbox d-flex mr-12">
                                        <input name="status" value="1" class="active" checked type="radio">
                                        <span class="checkmark"></span>
                                        <span class="ml-2 mt-1">{{__("common.active")}}</span>
                                    </label>
                                    <label class="primary_checkbox d-flex">
                                        <input name="status" value="0" class="de_active" type="radio">
                                        <span class="checkmark"></span>
                                        <span class="ml-2 mt-1">{{__("common.inactive")}}</span>
                                    </label>
                                </div>
                                <span class="text-danger" id="error_attribute_status"></span>
                            </div>

                            <div class="QA_section2 QA_section_heading_custom check_box_table">
                                <h5>{{__('product.attribute_value')}} <span class="text-danger">*</span></h5>
                                <div class="QA_table mb_10">
                                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                        <table class="table create_attribute_table">
                                            <tbody>
                                                <tr class="variant_row_lists">
                                                    <td class="pl-0 pb-1 border-0">
                                                        <input class="primary_input_field" name="variant_values[]" placeholder="-" type="text">
                                                    </td>
                                                    <td class="pl-0 pb-1 pr-0 border-0 text-right">
                                                        <button type="button" class="btn-toolkit btn-secondary icon-only add_single_variant_row">
                                                            <i class="ti-plus"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="variant_row_lists">
                                                    <td colspan="2" class="pl-0 pb-0 border-0">
                                                        <span class="text-danger" id="error_variant_values"></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 text-center mt-20">
                            <button class="btn-toolkit btn-primary"><i class="ti-check"></i>{{__("common.save")}} </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
