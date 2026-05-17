<div class="box_header common_table_header">
    <div class="main-title d-md-flex">
        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('order.edit_delivery_process') }}</h3>
    </div>
</div>
<form action="" method="POST" id="processEditForm">
    <div class="white_box_30px box_shadow_white mb-20">
        <div class="row">
            <input type="hidden" class="edit_id" name="id" value="0">
            <div class="col-lg-12 edit-empty-state">
                <div class="text-center py-4 text-muted">
                    <i class="ti-pencil-alt" style="font-size: 2rem; opacity: 0.4;"></i>
                    <p class="mt-2 mb-0" style="font-size: 1rem;">{{ __('order.select_delivery_process_to_edit') }}</p>
                </div>
            </div>
            @if(isModuleActive('FrontendMultiLang'))
                <div class="col-lg-12 edit-form-fields d-none">
                    <ul class="nav nav-tabs justify-content-start mt-sm-md-20 mb-30 grid_gap_5" role="tablist">
                        @foreach ($LanguageList as $key => $language)
                            <li class="nav-item">
                                <a class="nav-link anchore_color @if (auth()->user()->lang_code == $language->code) active @endif" href="#odpeelement{{$language->code}}" role="tab" data-toggle="tab" aria-selected="@if (auth()->user()->lang_code == $language->code) true @else false @endif">{{ $language->native }} </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="tab-content">
                        @foreach ($LanguageList as $key => $language)
                            <div role="tabpanel" class="tab-pane fade @if (auth()->user()->lang_code == $language->code) show active @endif" id="odpeelement{{$language->code}}">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="primary_input mb-15">
                                            <label class="primary_input_label" for=""> {{__("refund.process")}} <span class="text-danger" style="color: black !important;">*</span></label>
                                            <input class="primary_input_field" name="name[{{$language->code}}]" id="name{{$language->code}}" placeholder="{{__("refund.process")}}" type="text">
                                            <span class="text-danger" id="edit_name_error_{{$language->code}}"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="primary_input mb-15">
                                            <label class="primary_input_label" for=""> {{__("refund.description")}} <span class="text-danger" style="color: black !important;">*</span></label>
                                            <textarea class="primary_textarea height_112" id="description{{$language->code}}" name="description[{{$language->code}}]"></textarea>
                                            <span class="text-danger" id="edit_description_error_{{$language->code}}"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
            <div class="col-xl-12 edit-form-fields d-none">
                <div class="primary_input mb-25">
                    <label class="primary_input_label" for="">{{__("refund.process")}} <span class="text-danger" style="color: black !important;">*</span></label>
                    <input name="name" class="primary_input_field name" placeholder="{{__("refund.process")}}" type="text">
                    <span class="text-danger" id="edit_name_error"></span>
                </div>
            </div>
            <div class="col-lg-12 edit-form-fields d-none">
                <div class="primary_input mb-15">
                    <label class="primary_input_label" for=""> {{__("refund.description")}} <span class="text-danger" style="color: black !important;">*</span></label>
                    <textarea class="primary_textarea height_112 description" name="description"></textarea>
                    <span class="text-danger" id="edit_description_error"></span>
                </div>
            </div>
            @endif
            @if (permissionCheck('order_manage.process_update'))
                <div class="col-lg-12 text-center edit-form-actions d-none">
                    <button class=" btn-toolkit btn-primary btn-icon mt-2"><i class="ti-check"></i>{{__("common.update")}} </button>
                    <button type="button" class="btn-toolkit btn-secondary btn-icon mt-2 ml-2 cancel_edit_process">
                        <i class="ti-close"></i>{{ __('common.cancel') }}
                    </button>
                </div>
            @else
                <div class="col-lg-12 text-center mt-2">
                    <span class="alert alert-warning" role="alert">
                        <strong>{{ __('common.you_don_t_have_this_permission') }}</strong>
                    </span>
                </div>
            @endif
        </div>
    </div>
</form>
