@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{ asset(asset_path('backend/vendors/css/icon-picker.css')) }}" />
<link rel="stylesheet" href="{{asset(asset_path('modules/product/css/product_create.css'))}}" />
<style>
    .plus-btn-center-padding{ padding: 38px 0px; }
    .redius-50{ border-radius: 50%; }
    .nav-tabs .nav-link { font-weight: 500; color: var(--text_black); border-radius: 8px 8px 0 0; background: transparent; }
    .nav-tabs .nav-link.active { color: var(--toolkit_corporative-orange-color); border-bottom: 2px solid var(--toolkit_corporative-orange-color); }
</style>
@endsection

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-20 white_box">
        <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data" id="choice_form">
            @csrf
            <div class="row justify-content-center mb-4">
                <div class="col-12">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px"><i class="ti-plus mr-2 text-color-base"></i>{{ __('product.add_new_product') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            @if(isModuleActive('FrontendMultiLang'))
                @php $LanguageList = getLanguageList(); @endphp
            @endif

            <ul class="nav nav-tabs justify-content-start mb-30 grid_gap_5">
                <li class="nav-item">
                    <a class="nav-link active show" href="#GenaralInfo" role="tab" data-toggle="tab" aria-selected="true"><i class="ti-info-alt mr-1"></i>{{__('product.general_information')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#RelatedProduct" role="tab" data-toggle="tab" aria-selected="false"><i class="ti-layers mr-1"></i>{{__('product.related_product')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#UpSale" role="tab" data-toggle="tab" aria-selected="false"><i class="ti-arrow-up mr-1"></i>{{__('common.up_sale')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#CrossSale" role="tab" data-toggle="tab" aria-selected="false"><i class="ti-control-shuffle mr-1"></i>{{__('common.cross_sale')}}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade active show" id="GenaralInfo">

                    {{-- TARJETA 1: INFORMACIÓN PRINCIPAL --}}
                    <div class="row justify-content-between">
                        <div class="col-lg-8">
                            <div class="form-card">
                                <h3><i class="ti-tag mr-2"></i>{{ __('product.product_information') }}</h3>

                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        <input type="hidden" value="1" id="product_type">
                                        <span class="primary_input_label" id="product_type_label">{{ __('common.type') }} <span class="text-danger">*</span></span>
                                        <ul id="theme_nav" class="permission_list sms_list d-flex gap-3">
                                            <li class="mr-4">
                                                <label data-id="bg_option" class="primary_checkbox d-flex mr-2">
                                                    <input name="product_type" id="single_prod" value="1" @if(!old('product_type') || old('product_type') == 1) checked @endif class="active prod_type" type="radio">
                                                    <span class="checkmark"></span>
                                                    <span class="sr-only">{{ __('product.single') }}</span>
                                                </label>
                                                <p class="mb-0">{{ __('product.single') }}</p>
                                            </li>
                                            <li>
                                                <label data-id="color_option" class="primary_checkbox d-flex mr-2">
                                                    <input name="product_type" value="2" id="variant_prod" @if(old('product_type') && old('product_type') == 2) checked @endif class="de_active prod_type" type="radio">
                                                    <span class="checkmark"></span>
                                                    <span class="sr-only">{{ __('product.variant') }}</span>
                                                </label>
                                                <p class="mb-0">{{ __('product.variant') }}</p>
                                            </li>
                                        </ul>
                                    </div>

                                    @if(isModuleActive('FrontendMultiLang'))
                                        <div class="col-12 mb-3">
                                            <ul class="nav nav-tabs justify-content-start grid_gap_5 border-0">
                                                @foreach ($LanguageList as $language)
                                                    <li class="nav-item">
                                                        <a class="nav-link default_lang btn-sm border @if (auth()->user()->lang_code == $language->code) active @endif" data-id="{{$language->code}}" href="#pnelement{{$language->code}}" role="tab" data-toggle="tab">{{ $language->native }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-12 tab-content">
                                            @foreach ($LanguageList as $language)
                                                <div role="tabpanel" class="tab-pane fade @if(auth()->user()->lang_code == $language->code) show active @endif" id="pnelement{{$language->code}}">
                                                    <div class="row">
                                                        <div class="col-lg-6 form-group">
                                                            <label class="primary_input_label" for="product_name_{{$language->code}}"> {{ __('common.name') }} <span class="text-danger">*</span></label>
                                                            <input class="primary_input_field" name="product_name[{{$language->code}}]" id="product_name_{{$language->code}}" placeholder="{{ __('common.name') }}" type="text" value="{{old('product_name.'.$language->code)}}">
                                                            <span class="text-danger" id="error_product_name_{{$language->code}}">{{ $errors->first('product_name') }}</span>
                                                        </div>
                                                        <div class="col-lg-6 form-group sku_single_div d-none" id="default_lang_{{$language->code}}">
                                                            <label class="primary_input_label" for="sku_single_{{$language->code}}"> {{ __('product.product_sku') }}</label>
                                                            <input class="primary_input_field" name="product_sku[{{$language->code}}]" id="sku_single_{{$language->code}}" placeholder="{{ __('product.product_sku') }}" type="text" value="{{old('product_sku.'.$language->code)}}">
                                                            <span id="error_single_sku" class="text-danger">{{ $errors->first('product_sku') }}</span>
                                                        </div>
                                                        @if(app('general_setting')->product_subtitle_show)
                                                            <div class="col-lg-6 form-group">
                                                                <label class="primary_input_label" for="subtitle_1_{{$language->code}}"> {{ __('product.subtitle_1') }}</label>
                                                                <input class="primary_input_field" name="subtitle_1[{{$language->code}}]" id="subtitle_1_{{$language->code}}" type="text" value="{{old('subtitle_1.'.$language->code)}}">
                                                            </div>
                                                            <div class="col-lg-6 form-group">
                                                                <label class="primary_input_label" for="subtitle_2_{{$language->code}}"> {{ __('product.subtitle_2') }}</label>
                                                                <input class="primary_input_field" name="subtitle_2[{{$language->code}}]" id="subtitle_2_{{$language->code}}" type="text" value="{{old('subtitle_2.'.$language->code)}}">
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="col-lg-6 form-group">
                                            <label class="primary_input_label" for="product_name"> {{ __('common.name') }} <span class="text-danger">*</span></label>
                                            <input class="primary_input_field" name="product_name" id="product_name" type="text" value="{{ old('product_name') }}">
                                            <span class="text-danger" id="error_product_name">{{ $errors->first('product_name') }}</span>
                                        </div>
                                        <div class="col-lg-6 form-group sku_single_div">
                                            <label class="primary_input_label" for="sku_single"> {{ __('product.product_sku') }} <span class="text-danger">*</span></label>
                                            <input class="primary_input_field" name="product_sku" id="sku_single" type="text" value="{{old('product_sku')}}">
                                            <span class="text-danger" id="error_single_sku">{{ $errors->first('product_sku') }}</span>
                                        </div>
                                        @if(app('general_setting')->product_subtitle_show)
                                            <div class="col-lg-6 form-group">
                                                <label class="primary_input_label" for="subtitle_1"> {{ __('product.subtitle_1') }}</label>
                                                <input class="primary_input_field" name="subtitle_1" id="subtitle_1" type="text" value="{{old('subtitle_1')}}">
                                            </div>
                                            <div class="col-lg-6 form-group">
                                                <label class="primary_input_label" for="subtitle_2"> {{ __('product.subtitle_2') }}</label>
                                                <input class="primary_input_field" name="subtitle_2" id="subtitle_2" type="text" value="{{old('subtitle_2')}}">
                                            </div>
                                        @endif
                                    @endif

                                    <div class="col-lg-6 form-group d-none variant_sku_prefix">
                                        <label class="primary_input_label" for="variant_sku_prefix"> {{ __('product.variant_sku_prefix') }} <span class="text-danger">*</span></label>
                                        <input class="primary_input_field" name="variant_sku_prefix" id="variant_sku_prefix" type="text" value="{{old('variant_sku_prefix')}}">
                                    </div>

                                    <div class="col-lg-6 form-group">
                                        <label class="primary_input_label" for="model_number"> {{ __('common.model_number') }} / {{ __('product.lot') }}</label>
                                        <input class="primary_input_field" id="model_number" name="model_number" type="text" value="{{ old('model_number') }}">
                                    </div>

                                    <div class="col-lg-6 form-group" id="category_select_div">@include('product::products.components._category_list_select')</div>
                                    <div class="col-lg-6 form-group" id="brand_select_div">@include('product::products.components._brand_list_select')</div>
                                    <div class="col-lg-6 form-group" id="unit_select_div">@include('product::products.components._unit_list_select')</div>

                                    <div class="col-lg-6 form-group">
                                        <label class="primary_input_label" for="barcode_type">{{ __('product.barcode_type')}}</label>
                                        <select name="barcode_type" id="barcode_type" class="primary_select">
                                            <option disabled>{{ __('product.select_barcode') }}</option>
                                            @foreach (barcodeList() as $key => $barcode)
                                                <option value="{{ $barcode }}" @if(old('barcode_type') == $barcode || $key==0) selected @endif>{{ $barcode }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-6 form-group">
                                        <label class="primary_input_label" for="expiry_date">{{ __('product.expiry_date') }}</label>
                                        <input class="primary_input_field" id="expiry_date" name="expiry_date" min="{{ date('Y-m-d') }}" type="date" value="{{ old('expiry_date') }}">
                                    </div>

                                    @if(isModuleActive('GoogleMerchantCenter'))
                                        <div class="col-lg-4 form-group">
                                            <label class="primary_input_label" for="condition">{{ __('product.product_condition')}}</label>
                                            <select class="primary_select" name="condition" id="condition">
                                                <option value="new" @if(old('condition') == 'new') selected @endif>{{ __('product.new') }}</option>
                                                <option value="used" @if(old('condition') == 'used') selected @endif>{{ __('product.used') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-4 form-group">
                                            <label class="primary_input_label" for="gtin"> {{ __('common.gtin') }}</label>
                                            <input class="primary_input_field" name="gtin" id="gtin" type="text" value="{{ old('gtin') }}">
                                        </div>
                                        <div class="col-lg-4 form-group">
                                            <label class="primary_input_label" for="mpn"> {{ __('common.mpn') }}</label>
                                            <input class="primary_input_field" name="mpn" id="mpn" type="text" value="{{ old('mpn') }}">
                                        </div>
                                    @endif

                                    <div class="col-xl-12 mt-3">
                                        <ul class="permission_list sms_list">
                                            <li>
                                                <label class="primary_checkbox d-flex mr-12">
                                                    <input name="is_physical" id="is_physical" checked value="1" type="checkbox">
                                                    <span class="checkmark"></span>
                                                    <span class="sr-only">{{ __('product.is_physical_product') }}</span>
                                                </label>
                                                <p class="mb-0 font-weight-bold">{{ __('product.is_physical_product') }}</p>
                                                <input type="hidden" name="is_physical" value="1" id="is_physical_prod">
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            {{-- TARJETA 3: DIMENSIONES Y PESO --}}
                            <div class="form-card weight_height_div position-relative">
                                <div class="physical_blocked_overlay" style="display: none;">
                                    <div class="overlay-content text-center mx-4">
                                        <i class="ti-lock mb-2 d-block" style="font-size: 20px;"></i>
                                        <p class="mb-0">{{ __('product.only_for_physical_products') }}</p>
                                    </div>
                                </div>
                                <h3><i class="ti-ruler-pencil mr-2"></i>{{ __('product.weight_height_info') }}</h3>
                                <div class="row">
                                    @php
                                        $dimensions = [
                                            ['name' => 'weight', 'label' => __('product.weight'), 'unit' => __('product.gm')],
                                            ['name' => 'length', 'label' => __('product.length'), 'unit' => __('product.cm')],
                                            ['name' => 'breadth', 'label' => __('product.breadth'), 'unit' => __('product.cm')],
                                            ['name' => 'height', 'label' => __('product.height'), 'unit' => __('product.cm')],
                                        ];
                                    @endphp
                                    @foreach($dimensions as $dim)
                                        <div class="col-lg-12 form-group">
                                            <label class="primary_input_label" for="{{ $dim['name'] }}">{{ $dim['label'] }} [{{ $dim['unit'] }}]</label>
                                            <input class="primary_input_field" name="{{ $dim['name'] }}" id="{{ $dim['name'] }}" type="number" min="0" step="{{step_decimal()}}" value="{{ old($dim['name']) }}">
                                        </div>
                                    @endforeach

                                    <div id="phisical_shipping_div" class="col-lg-12 form-group mt-2">
                                        <label class="primary_input_label" for="additional_shipping">{{ __('product.additional_shipping_charge') }}</label>
                                        <input class="primary_input_field currency-mask" name="additional_shipping" id="additional_shipping" type="text" value="{{old('additional_shipping')?old('additional_shipping'):0}}">
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="row justify-content-between">
                        {{-- TARJETA 2: PRECIOS Y STOCK --}}
                        <div class="col-lg-8">
                            <div class="form-card">
                                <h3><i class="ti-money mr-2"></i>{{ __('product.price_info_and_stock') }}</h3>
                                <div class="row">
                                    <div class="col-lg-6 form-group selling_price_div">
                                        <label class="primary_input_label" for="selling_price"> {{ __('product.selling_price') }} <span class="text-danger">*</span></label>
                                        <input class="primary_input_field selling_price currency-mask" name="selling_price" id="selling_price" type="text" value="{{old('selling_price')}}">
                                        <span class="text-danger" id="error_selling_price">{{ $errors->first('selling_price') }}</span>
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <label class="primary_input_label" for="discount"> {{ __('product.discount') }}</label>
                                        <input class="primary_input_field" name="discount" id="discount" type="number" min="0" step="{{step_decimal()}}" value="{{old('discount')?old('discount'):0}}">
                                        <span class="text-danger" id="error_discunt"></span>
                                    </div>
                                    <div class="col-lg-6 form-group">
                                        <label class="primary_input_label" for="discount_type">{{ __('product.discount_type')}}</label>
                                        <select class="primary_select" name="discount_type" id="discount_type">
                                            <option value="1" @if(old('discount_type') == 1) selected @endif>{{ __('common.amount') }}</option>
                                            <option value="0" @if(old('discount_type') == 0) selected @endif>{{ __('common.percentage') }}</option>
                                        </select>
                                    </div>

                                    @if (app('gst_config')['enable_gst'] == "only_tax")
                                        <div class="col-lg-12 form-group">
                                            <label class="primary_input_label" for="tax_id">{{ __('common.tax')}}</label>
                                            <select class="primary_select" name="tax_id" id="tax_id">
                                                <option value="" selected disabled>{{__('common.select_one')}}</option>
                                                @foreach($gst_lists as $gst)
                                                    <option value="{{$gst->id}}" @if(old('tax_id') == $gst->id) selected @endif>{{ $gst->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="col-lg-6 form-group">
                                            <label class="primary_input_label" for="tax_type">{{ __('gst.GST_group')}}</label>
                                            <select class="primary_select" name="gst_group" id="tax_type">
                                                <option value="" selected disabled>{{__('common.select_one')}}</option>
                                                @foreach($gst_groups as $group)
                                                    <option value="{{$group->id}}" @if(old('gst_group') == $group->id) selected @endif>{{ $group->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-12 form-group" id="gst_list_div"></div>
                                    @endif

                                    @php
                                        $stockFields = [
                                            ['name' => 'minimum_order_qty', 'label' => __('product.minimum_order_qty'), 'req' => true, 'def' => 1],
                                            ['name' => 'max_order_qty', 'label' => __('product.max_order_qty'), 'req' => false, 'def' => ''],
                                            ['name' => 'min_stock', 'label' => __('product.min_stock'), 'req' => true, 'def' => 1],
                                            ['name' => 'max_stock', 'label' => __('product.max_stock'), 'req' => true, 'def' => ''],
                                        ];
                                    @endphp
                                    @foreach($stockFields as $field)
                                        <div class="col-lg-6 form-group">
                                            <label class="primary_input_label" for="{{ $field['name'] }}">{{ $field['label'] }} @if($field['req'])<span class="text-danger">*</span>@endif</label>
                                            <input class="primary_input_field" name="{{ $field['name'] }}" id="{{ $field['name'] }}" type="number" min="0" value="{{ old($field['name']) ?? $field['def'] }}">
                                            @if($field['req'])
                                                <span class="text-danger" id="error_{{ $field['name'] === 'minimum_order_qty' ? 'minumum_qty' : $field['name'] }}">{{ $errors->first($field['name']) }}</span>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if(auth()->user()->role->type == 'seller' || (isset($_GET['add_type']) && $_GET['add_type'] == 'in-house'))
                                        <div class="col-lg-6 form-group" id="stock_manage_div">
                                            <label class="primary_input_label" for="stock_manage">{{__('product.stock_manage') }}</label>
                                            <select class="primary_select" name="stock_manage" id="stock_manage">
                                                <option value="1" @if(old('stock_manage') == '1') selected @endif>{{ __('common.yes') }}</option>
                                                <option value="0" @if(old('stock_manage') == '0') selected @endif>{{ __('common.no') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-6 form-group d-none" id="single_stock_div">
                                            <label class="primary_input_label" for="single_stock">{{__('product.product_stock') }}</label>
                                            <input class="primary_input_field" name="single_stock" id="single_stock" type="number" min="0" value="{{old('single_stock')?old('single_stock'):0}}">
                                        </div>
                                    @endif
                                </div>

                                @if (isModuleActive('WholeSale'))
                                    <div class="whole_sale_info_add mt-3" id="whole_sale_info_add">
                                        <h4 class="mb-3" style="font-size: 14px; color:var(--base_color_2);">{{ __('wholesale.Wholesale Price') }}</h4>
                                        <div class="table-responsive mb-3">
                                            <table class="table mb-0">
                                                <thead class="sr-only">
                                                    <tr>
                                                        <th scope="col">{{ __('wholesale.Min QTY') }}</th>
                                                        <th scope="col">{{ __('wholesale.Max QTY') }}</th>
                                                        <th scope="col">{{ __('wholesale.Price per piece') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="whole_sale_price_list">
                                                        <td class="pl-0 pb-0"><input type="text" class="form-control primary_input_field" placeholder="{{__('wholesale.Min QTY')}}" name="wholesale_min_qty_0[]"></td>
                                                        <td class="pl-0 pb-0"><input type="text" class="form-control primary_input_field" placeholder="{{__('wholesale.Max QTY')}}" name="wholesale_max_qty_0[]"></td>
                                                        <td class="pl-0 pb-0"><input type="text" class="form-control primary_input_field" placeholder="{{__('wholesale.Price per piece')}}" name="wholesale_price_0[]"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="button" class="btn-toolkit btn-secondary-outline add_single_whole_sale_price"><i class="ti-plus"></i> {{__('wholesale.Add More')}} </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TARJETA 4: IMÁGENES Y MULTIMEDIA --}}
                        <div class="col-lg-4">
                            <div class="form-card">
                                <h3><i class="ti-gallery mr-2"></i>Multimedia y Archivos</h3>
                                <div class="row">
                                    <div class="col-lg-12 form-group">
                                        <span class="primary_input_label">{{ __('product.product_image_info') }}</span>
                                        <div class="primary_file_uploader" data-toggle="amazuploader" data-multiple="true" data-type="image" data-name="images[]">
                                            <input class="primary-input file_amount" type="text" id="thumbnail_image_file" placeholder="{{ __('common.choose_images') }}" readonly>
                                            <button class="" type="button">
                                                <label class="btn-toolkit btn-primary btn-sm mb-0" for="thumbnail_image">{{__('product.Browse') }} </label>
                                                <input type="hidden" class="selected_files image_selected_files" value="{{old('images')?implode(',',old('images')):''}}">
                                            </button>
                                        </div>
                                        <div class="product_image_all_div mt-2"></div>
                                    </div>

                                    <div class="col-lg-12 form-group">
                                        <span class="primary_input_label">{{ __('product.meta_image') }} (300x300 px)</span>
                                        <div class="primary_file_uploader" data-toggle="amazuploader" data-multiple="false" data-type="image" data-name="meta_image">
                                            <input class="primary-input file_amount" type="text" id="meta_image_file" placeholder="{{__('common.browse_image_file')}}" readonly>
                                            <button class="" type="button">
                                                <label class="btn-toolkit btn-primary btn-sm mb-0" for="meta_image">{{__('product.Browse') }} </label>
                                                <input type="hidden" class="selected_files" value="{{old('meta_image')}}">
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 form-group">
                                        <label class="primary_input_label" for="pdf">{{__('product.pdf_specifications') }}</label>
                                        <div class="primary_file_uploader">
                                            <input class="primary-input" type="text" id="pdf_place1" placeholder="{{__('product.upload_pdf')}}" readonly>
                                            <button class="" type="button">
                                                <label class="btn-toolkit btn-primary btn-sm mb-0" for="pdf">{{__('product.Browse') }} </label>
                                                <input type="file" class="d-none" name="pdf_file" id="pdf">
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 digital_file_upload_div form-group">
                                        <label class="primary_input_label" for="digital_file">{{__('product.program_file_upload') }}</label>
                                        <div class="primary_file_uploader">
                                            <input class="primary-input" type="text" id="pdf_place" placeholder="{{__('product.upload_file')}}" readonly>
                                            <button class="" type="button">
                                                <label class="btn-toolkit btn-primary btn-sm mb-0" for="digital_file">{{__('product.Browse') }} </label>
                                                <input type="file" class="d-none" name="single_digital_file" id="digital_file">
                                            </button>
                                        </div>
                                        <div class="mt-3">
                                            <label class="primary_input_label" for="in_app_purchase_code">{{__('product.in_app_purchase_code') }}</label>
                                            <input type="text" name="in_app_purchase_code" id="in_app_purchase_code" class="primary_input_field">
                                        </div>
                                    </div>

                                    <div class="col-lg-12 form-group mt-3">
                                        <label class="primary_input_label" for="video_provider">{{ __('product.video_provider')}}</label>
                                        <select class="primary_select" name="video_provider" id="video_provider">
                                            <option value="youtube" @if(!old('video_provider') || old('video_provider') == 'youtube') selected @endif>{{ __('product.youtube') }}</option>
                                            <option value="daily_motion" @if(old('video_provider') == 'daily_motion') selected @endif>{{ __('product.daily_motion') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 form-group mt-3">
                                        <label class="primary_input_label" for="video_link">{{ __('product.video_link')}}</label>
                                        <input class="primary_input_field" name="video_link" id="video_link" type="text" value="{{ old('video_link') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- VARIACIONES / ATRIBUTOS --}}
                    <div class="form-card attribute_div" id="attribute_select_div">
                        <h3><i class="ti-split-v mr-2"></i>{{ __('product.sku_variants') }}</h3>
                        @include('product::products.components._attribute_list_select')

                        <div class="col-lg-12 px-0 mt-3" id="customer_choice_options">
                            @if(old('choice_no'))
                                @foreach (old('choice_no') as $key => $id)
                                    @php $attribute = \Modules\Product\Entities\Attribute::find($id); @endphp
                                    <div class="row align-items-center mb-2">
                                        <div class="col-lg-4">
                                            <input type="hidden" name="choice_no[]" id="attribute_id_{{$attribute->id}}" value="{{ $attribute->id }}">
                                            <input class="primary_input_field" name="choice[]" type="text" value="{{ $attribute->name }}" readonly>
                                        </div>
                                        <div class="col-lg-6">
                                            <select name="choice_options_{{ $attribute->id }}[]" class="primary_select choice_attribute" multiple>
                                                @foreach ($attribute->values as $value)
                                                    <option value="{{$value->id}}" @if(in_array($value->id, old('choice_options_'.$attribute->id))) selected @endif> {{ $value->color ? $value->color->name : $value->value }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-1 text-center">
                                            <a class="btn-toolkit btn-secondary attribute_remove text-white"><i class="ti-trash"></i></a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-lg-12 px-0 sku_combination overflow-auto"></div>
                    </div>



                    {{-- TARJETA 5: DESCRIPCIONES Y SEO --}}
                    <div class="form-card">
                        <h3><i class="ti-write mr-2"></i>Descripciones y SEO</h3>
                        <div class="row">
                            <div class="col-lg-12 form-group">
                                <label class="primary_input_label" for="tags">@lang('blog.tags') (@lang('product.comma_separated')) <span class="text-danger">*</span></label>
                                <div class="tagInput_field mb_26">
                                    <input name="tags" id="tags" class="tag-input" type="text" value="{{old('tags')}}" data-role="tagsinput" />
                                </div>
                                <span class="text-danger" id="error_tags">{{ $errors->first('tags') }}</span>
                                <div class="suggeted_tags mt-2">
                                    <span class="text-muted small">@lang('blog.suggested_tags')</span>
                                    <div id="tag_show" class="suggested_tag_show"></div>
                                </div>
                            </div>

                            @if(isModuleActive('FrontendMultiLang'))
                                <div class="col-12 tab-content mt-3">
                                    @foreach ($LanguageList as $language)
                                            <div role="tabpanel" class="tab-pane fade pelement @if(auth()->user()->lang_code == $language->code) show active @endif" id="pelement{{$language->code}}">
                                                <div class="form-group">
                                                    <label class="primary_input_label" for="description_{{$language->code}}">{{ __('common.description') }} ({{ $language->native }})</label>
                                                    <textarea class="summernote" id="description_{{$language->code}}" name="description[{{$language->code}}]">{{old('description.'.$language->code)}}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label class="primary_input_label" for="specification_{{$language->code}}">{{ __('product.specifications') }} ({{ $language->native }})</label>
                                                    <textarea class="summernote2" id="specification_{{$language->code}}" name="specification[{{$language->code}}]">{{old('specification.'.$language->code)}}</textarea>
                                                </div>
                                                <div class="form-group mt-4">
                                                    <h4 class="mb-3 text-muted border-bottom pb-2">{{ __('common.seo_info') }}</h4>
                                                    <label class="primary_input_label" for="meta_title_{{$language->code}}">{{ __('common.meta_title')}}</label>
                                                    <input class="primary_input_field" name="meta_title[{{$language->code}}]" id="meta_title_{{$language->code}}" type="text" value="{{old('meta_title.'.$language->code)}}">
                                                </div>
                                                <div class="form-group">
                                                    <label class="primary_input_label" for="meta_description_{{$language->code}}">{{ __('common.meta_description') }}</label>
                                                    <textarea class="primary_textarea height_112 meta_description" id="meta_description_{{$language->code}}" name="meta_description[{{$language->code}}]">{{old('meta_description.'.$language->code)}}</textarea>
                                                </div>
                                            </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="col-lg-12 form-group">
                                    <label class="primary_input_label" for="description">{{ __('common.description') }}</label>
                                    <textarea class="summernote" id="description" name="description">{{old('description')}}</textarea>
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="primary_input_label" for="specification">{{ __('product.specifications') }}</label>
                                    <textarea class="summernote2" id="specification" name="specification">{{old('specification')}}</textarea>
                                </div>
                                <div class="col-lg-12 form-group mt-4">
                                    <h4 class="mb-3 text-muted border-bottom pb-2">{{ __('common.seo_info') }}</h4>
                                    <label class="primary_input_label" for="meta_title">{{ __('common.meta_title')}}</label>
                                    <input class="primary_input_field" name="meta_title" id="meta_title" type="text" value="{{ old('meta_title') }}">
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="primary_input_label" for="meta_description">{{ __('common.meta_description') }}</label>
                                    <textarea class="primary_textarea height_112 meta_description" id="meta_description" name="meta_description">{{old('meta_description')}}</textarea>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- TARJETA 6: CONFIGURACIONES ADICIONALES --}}
                    <div class="form-card mb-0">
                        <h3><i class="ti-settings mr-2"></i>{{ __('product.others_info') }}</h3>
                        <div class="row">
                            <div class="col-lg-6 form-group">
                                <span class="primary_input_label">{{ __('common.status') }} <span class="text-danger">*</span></span>
                                <ul class="permission_list sms_list d-flex flex-wrap gap-3">
                                    <li class="mr-3">
                                        <label class="primary_checkbox d-flex mr-2">
                                            <input name="status" id="status_active" value="1" @if(old('status') == null || old('status') == 1) checked @endif class="active" type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.publish') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.publish') }}</p>
                                    </li>
                                    <li class="mr-3">
                                        <label class="primary_checkbox d-flex mr-2">
                                            <input name="status" value="0" id="status_inactive" @if(old('status') != null && old('status') == 0) checked @endif class="de_active" type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.pending') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.pending') }}</p>
                                    </li>
                                    <li>
                                        <label class="primary_checkbox d-flex mr-2">
                                            <input name="status" value="3" id="status_draft" @if(old('status') != null && old('status') == 3) checked @endif class="de_active" type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.draft') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.draft') }}</p>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-lg-6 form-group">
                                <span class="primary_input_label">{{ __('common.make_Display_in_details_page') }} <span class="text-danger">*</span></span>
                                <ul class="permission_list sms_list d-flex flex-wrap gap-3">
                                    <li class="mr-3">
                                        <label class="primary_checkbox d-flex mr-2">
                                            <input name="display_in_details" value="1" @if(!old('display_in_details') || old('display_in_details') == 1) checked @endif type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.up_sale') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.up_sale') }}</p>
                                    </li>
                                    <li>
                                        <label class="primary_checkbox d-flex mr-2">
                                            <input name="display_in_details" value="2" @if(old('display_in_details') == 2) checked @endif type="radio">
                                            <span class="checkmark"></span>
                                            <span class="sr-only">{{ __('common.cross_sale') }}</span>
                                        </label>
                                        <p class="mb-0">{{ __('common.cross_sale') }}</p>
                                    </li>
                                </ul>
                            </div>

                            @if(isModuleActive('GoldPrice'))
                                <div class="col-lg-4 form-group mt-3">
                                    <span class="primary_input_label">{{__('product.auto_update_required') }} <span class="text-danger">*</span></span>
                                    <ul class="permission_list sms_list d-flex gap-3">
                                        <li class="mr-3">
                                            <label class="primary_checkbox d-flex mr-2">
                                                <input name="auto_update_required" value="1" @if(old('auto_update_required') == null || old('auto_update_required') == 1) checked @endif type="radio">
                                                <span class="checkmark"></span>
                                                <span class="sr-only">{{ __('common.on') }}</span>
                                            </label>
                                            <p class="mb-0">{{ __('common.on') }}</p>
                                        </li>
                                        <li>
                                            <label class="primary_checkbox d-flex mr-2">
                                                <input name="auto_update_required" value="0" @if(old('auto_update_required') != null && old('auto_update_required') == 0) checked @endif type="radio">
                                                <span class="checkmark"></span>
                                                <span class="sr-only">{{ __('common.off') }}</span>
                                            </label>
                                            <p class="mb-0">{{ __('common.off') }}</p>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-lg-4 form-group mt-3">
                                    <label class="primary_input_label" for="gold_price_id">{{__('product.gold_price')}}</label>
                                    <select class="primary_select" name="gold_price_id" id="gold_price_id">
                                        @foreach($gold_prices as $gold_price)
                                            <option data-price="{{$gold_price->price}}" value="{{$gold_price->id}}" @if(old('gold_price_id') == $gold_price->id) selected @endif>{{$gold_price->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 form-group mt-3">
                                    <label class="primary_input_label" for="making_charge">{{__('product.making_charge') }}</label>
                                    <input class="primary_input_field" name="making_charge" id="making_charge" type="number" min="0" step="{{step_decimal()}}" value="{{old('making_charge')?old('making_charge'):0}}">
                                </div>
                            @endif

                            @if(isModuleActive('ClubPoint'))
                                <div class="col-lg-4 form-group mt-3">
                                    <label class="primary_input_label" for="club_point">{{ __('clubpoint.point') }}</label>
                                    <input class="primary_input_field" name="club_point" id="club_point" type="number" min="0" value="{{old('club_point')?old('club_point'):0}}">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane fade" id="RelatedProduct">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex"><h3 class="mb-0">{{ __('product.related_product') }}</h3></div>
                    </div>
                    <input class="primary_input_field mb-3" placeholder="Quick Search" type="text" id="rsearch_products">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table position-relative" id="related_product">
                            <div class="table-responsive dataTables_wrapper" style="max-height: 600px;" id="product_list_div">
                                <table class="table dataTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 10%;"><label class="primary_checkbox d-flex"><input type="checkbox" id="relatedProductAll"><span class="checkmark"></span><span class="sr-only">{{ __('common.select_all') }}</span></label></th>
                                            <th scope="col" style="width: 20%;">{{ __('common.name') }}</th>
                                            <th scope="col" style="width: 15%;">{{ __('product.brand') }}</th>
                                            <th scope="col" style="width: 10%;">{{ __('product.thumbnail') }}</th>
                                            <th scope="col" style="width: 10%;">{{ __('product.created_at') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablecontentsrelatedProduct">
                                        @foreach ($products as $key => $item)
                                        <tr>
                                            <th scope="row"><label class="primary_checkbox d-flex"><input name="related_product[]" id="related_product_{{$key}}" @if(isset($product) && @$product->relatedProducts->where('related_sale_product_id',$item->id)->first()) checked @endif value="{{$item->id}}" type="checkbox" class="related_product_checked"><span class="checkmark"></span><span class="sr-only">{{ __('common.select') }}</span></label></th>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ @$item->brand->name }}</td>
                                            <td><div class="product_img_div"><img class="product_list_img" src="{{ showImage($item->thumbnail_image_source) }}" alt="thumb"></div></td>
                                            <td>{{ date(app('general_setting')->dateFormat->format, strtotime($item->created_at)) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="pagination-container">{!! $products->links() !!}</div>
                            </div>
                        </div>
                        <input type="hidden" name="related_product_hidden_name" id="related_product_hidden_id">
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane fade" id="UpSale">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex"><h3 class="mb-0">{{ __('common.up_sale') }}</h3></div>
                    </div>
                    <input class="primary_input_field mb-3" placeholder="Quick Search" type="text" id="upsale_search_products">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table" id="upsale_products">
                            <div class="table-responsive dataTables_wrapper" style="max-height: 600px;" id="product_list_div">
                                <table class="table dataTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 10%;"><label class="primary_checkbox d-flex"><input type="checkbox" id="upSaleAll"><span class="checkmark"></span><span class="sr-only">{{ __('common.select_all') }}</span></label></th>
                                            <th scope="col" style="width: 20%;">{{ __('common.name') }}</th>
                                            <th scope="col" style="width: 15%;">{{ __('product.brand') }}</th>
                                            <th scope="col" style="width: 10%;">{{ __('product.thumbnail') }}</th>
                                            <th scope="col" style="width: 10%;">{{ __('product.created_at') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablecontentsupSaleAll">
                                        @foreach ($products as $key => $item)
                                        <tr>
                                            <th scope="row"><label class="primary_checkbox d-flex"><input name="up_sale[]" id="up_sale_{{$key}}" @if(isset($product) && @$product->upSales->where('up_sale_product_id',$item->id)->first()) checked @endif value="{{$item->id}}" type="checkbox" class="upsale_product_checked"><span class="checkmark"></span><span class="sr-only">{{ __('common.select') }}</span></label></th>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ @$item->brand->name }}</td>
                                            <td><div class="product_img_div"><img class="product_list_img" src="{{ showImage($item->thumbnail_image_source) }}" alt="thumb"></div></td>
                                            <td>{{ date(app('general_setting')->dateFormat->format, strtotime($item->created_at)) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="pagination-container">{!! $products->links() !!}</div>
                            </div>
                        </div>
                        <input type="hidden" name="upsale_product_hidden_name" id="upsale_product_hidden_id">
                    </div>
                </div>

                <div role="tabpanel" class="tab-pane fade" id="CrossSale">
                    <div class="box_header common_table_header">
                        <div class="main-title d-md-flex"><h3 class="mb-0">{{ __('common.cross_sale') }}</h3></div>
                    </div>
                    <input class="primary_input_field mb-3" placeholder="Quick Search" type="text" id="crosssale_search_products">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table" id="crosssale_products">
                            <div class="table-responsive dataTables_wrapper" style="max-height: 600px;" id="product_list_div">
                                <table class="table dataTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" style="width: 10%;"><label class="primary_checkbox d-flex"><input type="checkbox" id="crossSaleAll"><span class="checkmark"></span><span class="sr-only">{{ __('common.select_all') }}</span></label></th>
                                            <th scope="col" style="width: 20%;">{{ __('common.name') }}</th>
                                            <th scope="col" style="width: 15%;">{{ __('product.brand') }}</th>
                                            <th scope="col" style="width: 10%;">{{ __('product.thumbnail') }}</th>
                                            <th scope="col" style="width: 10%;">{{ __('product.created_at') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablecontentscrossSaleAll">
                                        @foreach ($products as $key => $item)
                                            <tr>
                                                <th scope="row"><label class="primary_checkbox d-flex"><input name="cross_sale[]" id="cross_sale_{{$key}}" @if(isset($product) &&  @$product->crossSales->where('cross_sale_product_id',$item->id)->first()) checked @endif value="{{$item->id}}" type="checkbox" class="crosssale_product_checked"><span class="checkmark"></span><span class="sr-only">{{ __('common.select') }}</span></label></th>
                                                <td>{{ $item->product_name }}</td>
                                                <td>{{ @$item->brand->name }}</td>
                                                <td><div class="product_img_div"><img class="product_list_img" src="{{ showImage($item->thumbnail_image_source) }}" alt="thumb"></div></td>
                                                <td>{{ date(app('general_setting')->dateFormat->format, strtotime($item->created_at)) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="pagination-container">{!! $products->links() !!}</div>
                            </div>
                        </div>
                        <input type="hidden" name="crosssale_product_hidden_name" id="crosssale_product_hidden_id">
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <input type="hidden" name="request_from" value="main_product_form">
                <div class="col-12 text-center p-3" style="background: #f8f9fa; border-radius: 15px; border: 1px dashed #cecece75;">
                    <p class="text-muted small mb-3"><i class="ti-info-alt mr-1"></i> {{__('product.save_information')}}</p>

                    @if(auth()->user()->role->type != 'seller')
                        <input type="hidden" name="save_type" id="save_type">
                        <div class="btn-group-toolkit justify-content-center flex-wrap">
                            <button class="btn-toolkit btn-secondary-outline saveBtn" data-value="only_save">
                                <i class="ti-check mr-2"></i>{{ __('common.save') }}
                            </button>

                            @if(!isModuleActive('MultiVendor') || (isset($_GET['add_type']) && $_GET['add_type'] == 'in-house'))
                                <button class="btn-toolkit btn-primary saveBtn" data-value="save_publish">
                                    <i class="ti-upload mr-2"></i>{{ __('common.save') }} & {{ __('common.publish') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </form>
    </div>
</section>

@include('product::products.components._create_category_modal')
@include('product::products.components._create_brand_modal')
@include('product::products.components._create_unit_modal')
@include('product::products.components._create_attribute_modal')
@include('product::products.components._create_shipping_modal')
@endsection
@include('product::products.create_script')
