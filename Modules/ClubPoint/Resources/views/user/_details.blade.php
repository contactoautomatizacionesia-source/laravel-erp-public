@extends('backEnd.master')
@section('styles')

<link rel="stylesheet" href="{{asset(asset_path('modules/ordermanage/css/sale_details.css'))}}" />
@endsection
@section('mainContent')
    <div id="add_product">
        <section class="admin-visitor-area up_st_admin_visitor">
            <div class="container-fluid p-0">
                <div class="row justify-content-center">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="box_header common_table_header">
                            <div class=" d-flex justify-content-between w-100">
                                <div class=" main-title d-md-flex align-items-baseline">
                                    <x-backEnd.back-button :text="false" />
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ getNumberTranslate($order->order_number) }} </h3>
                                </div>
                                <ul class="d-flex">
                                    <li>
                                        <a href="{{ route('order_manage.print_order_details', $order->id) }}"
                                            target="_blank"
                                            class="primary-btn fix-gr-bg radius_30px mr-10">{{ __('order.print') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 student-details">
                        <div class="white_box_30px box_shadow_white" id="printableArea">
                            <div class="row pb-30 border-bottom">
                                <div class="col-md-6 col-lg-6">
                                    <div class="logo_div">
                                        <img src="{{ showImage(app('general_setting')->logo) }}" alt="">
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6 text-right">
                                    <h4>{{ getNumberTranslate($order->order_number) }}</h4>
                                </div>
                            </div>
                            <div class="row mt-30">
                                @foreach ($order->packages as $key => $order_package)
                                    <div class="col-12 mt-30">
                                        @if ($order_package->is_cancelled == 1)
                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label red" for="">
                                                    {{__('defaultTheme.order_cancelled')}} - ({{ $order_package->package_code }})
                                                </label>
                                            </div>

                                            <div class="primary_input mb-25">
                                                <label class="primary_input_label sub-title" for="">
                                                    {{ @$order_package->cancel_reason->name }}
                                                </label>
                                            </div>
                                        @endif
                                        <div class="box_header common_table_header">
                                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('common.package')}}:
                                                {{ getNumberTranslate($order_package->package_code) }} @if ($order_package->delivery_process)
                                                    <small>({{ @$order_package->delivery_process->name }})</small>
                                                @endif
                                            </h3>
                                            @if(isModuleActive('MultiVendor'))
                                            <ul class="d-flex float-right">
                                                <li>
                                                    <strong>
                                                        @if($order_package->seller->role->type == 'seller')
                                                            {{ @$order_package->seller->SellerAccount->seller_shop_display_name ? @$order_package->seller->SellerAccount->seller_shop_display_name : @$order_package->seller->first_name }}
                                                        @else
                                                            {{ app('general_setting')->company_name }}
                                                        @endif
                                                    </strong>
                                                </li>
                                            </ul>
                                            @endif
                                        </div>
                                        <div class="box_header common_table_header justify-content-lg-end"> 
                                            <ul class="d-flex float-right">
                                                <li> <strong>{{__('defaultTheme.shipping_method')}} : {{ $order_package->shipping->method_name }}</strong></li>
                                            </ul>
                                        </div>
                                        <div class="QA_section QA_section_heading_custom check_box_table">
                                            <div class=" ">
                                                <!-- table-responsive -->
                                                <div class="table-responsive">
                                                    <table class="table products-table">
                                                        <tr>
                                                            <th scope="col">{{__('common.sl')}}</th>
                                                            <th scope="col">{{__('common.image')}}</th>
                                                            <th scope="col">{{__('common.name')}}</th>
                                                            <th scope="col">{{__('common.details')}}</th>
                                                            <th scope="col">{{__('common.price')}}</th>
                                                            <th scope="col">{{__('common.point')}}</th>
                                                            <th scope="col">{{__('common.tax')}}/{{__('gst.gst')}}</th>
                                                            <th scope="col">{{__('common.total')}}</th>
                                                        </tr>
                                                        @foreach ($order_package->products as $key => $package_product)
                                                            <tr>
                                                                <td>{{ getNumberTranslate($key + 1)}}</td>
                                                                <td>
                                                                    <div class="product_img_div">
                                                                        @if ($package_product->type == "gift_card")
                                                                            <img src="{{showImage(@$package_product->giftCard->thumbnail_image)}}">
                                                                        @else
                                                                            @if (@$package_product->seller_product_sku->sku->product->product_type == 1)
                                                                                <img src="{{showImage(@$package_product->seller_product_sku->product->thum_img??@$package_product->seller_product_sku->sku->product->thumbnail_image_source)}}">
                                                                            @else
                                                                                <img src="{{showImage(@$package_product->seller_product_sku->sku->variant_image?@$package_product->seller_product_sku->sku->variant_image:@$package_product->seller_product_sku->product->thum_img??@$package_product->seller_product_sku->product->product->thumbnail_image_source)}}">
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @if ($package_product->type == "gift_card")
                                                                        <span class="text-nowrap">{{substr(@$package_product->giftCard->name,0,22)}} @if(strlen(@$package_product->giftCard->name) > 22)... @endif</span>
                                                                    @else
                                                                        <span class="text-nowrap">{{substr(@$package_product->seller_product_sku->sku->product->product_name,0,22)}} @if(strlen(@$package_product->seller_product_sku->sku->product->product_name) > 22)... @endif</span>
                                                                    @endif
                                                                </td>
                                                                @if ($package_product->type == "gift_card")
                                                                    <td class="text-nowrap">{{__('common.qty')}}: {{ getNumberTranslate($package_product->qty) }}</td>
                                                                @else
                                                                    @if (@$package_product->seller_product_sku->sku->product->product_type == 2)
                                                                        <td class="text-nowrap">
                                                                            {{__('common.qty')}}: {{ getNumberTranslate($package_product->qty) }}
                                                                            <br>
                                                                            @php
                                                                                $countCombinatiion = count(@$package_product->seller_product_sku->product_variations);
                                                                            @endphp
                                                                            @foreach (@$package_product->seller_product_sku->product_variations as $key => $combination)
                                                                                @if ($combination->attribute->id == 1)
                                                                                    <div class="box_grid ">
                                                                                        <span>{{ $combination->attribute->name }}:</span><span class='box variant_color' style="background-color:{{ $combination->attribute_value->value }}"></span>
                                                                                    </div>
                                                                                @else
                                                                                    {{ $combination->attribute->name }}:
                                                                                    {{ $combination->attribute_value->value }}
                                                                                @endif
                                                                                @if ($countCombinatiion > $key + 1)
                                                                                    <br>
                                                                                @endif
                                                                            @endforeach
                                                                        </td>
                                                                    @else
                                                                        <td class="text-nowrap">{{__('common.qty')}}: {{ getNumberTranslate($package_product->qty) }}</td>
                                                                    @endif
                                                                @endif

                                                                <td class="text-nowrap">{{ single_price($package_product->price) }}</td>
                                                                <td class="text-nowrap">{{ getNumberTranslate($package_product->total_club_point) }}</td>
                                                                <td class="text-nowrap">{{ single_price($package_product->tax_amount) }}</td>
                                                                <td class="text-nowrap">{{ single_price($package_product->price * $package_product->qty + $package_product->tax_amount) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row mt-30">
                                <div class="col-6 col-md-4 ml-auto">
                                    <h4 class="table-heading">{{__('order.order_info')}}</h4>
                                    <table class="invoice-table table-borderless clone_line_table">
                                        
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('order.is_paid')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> {{ $order->is_paid == 1 ? __('common.yes') : __('common.no') }}</td>
                                        </tr>
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('order.subtotal')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> {{ single_price($order->sub_total) }}</td>
                                        </tr>
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('common.discount')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> - {{ single_price($order->discount_total) }}</td>
                                        </tr>
                                        @if($order->coupon)
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('common.coupon')}} {{__('common.discount')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> - {{single_price($order->coupon->discount_amount)}}</td>
                                        </tr>
                                        @endif
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('common.shipping_charge')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> {{ single_price($order->shipping_total) }}</td>
                                        </tr>
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('common.totals_points')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> {{ getNumberTranslate(@$order->club_point) }} </td>
                                        </tr>
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('common.tax')}}/{{__('gst.gst')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> {{ single_price($order->tax_amount) }}</td>
                                        </tr>
                                        <tr class="d-flex justify-content-between aling-items-center">
                                            <td>{{__('order.grand_total')}}</td>
                                            <td class="pl-25 text-nowrap text-end"> {{ single_price($order->grand_total) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

