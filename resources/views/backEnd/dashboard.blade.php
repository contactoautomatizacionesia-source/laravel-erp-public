@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('backend/css/backend_page_css/dashboard.css'))}}" />
<link rel="stylesheet" href="{{asset(asset_path('backend/css/backend_global.css'))}}" />
@endsection
@section('mainContent')
<section class="mb-40 ign-control-panel py-3 py-lg-3 px-2 px-lg-2 px-xl-3">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-12 mb_10">
                <div class= "d-flex">
                    <h3 class="mb-0 mr-3 text-nowrap section-main-title text-color-stats">{{ __('common.summary') }} </h3>
                    @if(isModuleActive('MultiVendor'))
                    @if(auth()->user()->role->type == 'superadmin' || auth()->user()->role->type == 'seller' || auth()->user()->role->type == 'admin')
                    <ul class="d-flex">
                        <li><a href="{{route('seller.dashboard')}}" target="_blank"
                                class="primary-btn radius_30px mr-10 text-white bg-color-base">{{ __('dashboard.seller_dashboard')
                                }}</a>
                        </li>
                    </ul>
                    @endif
                    @endif
                </div>
            </div>
            @if (permissionCheck('widget_card'))
            <div class="col-lg-8 col-md-8 col-sm-12">
                <div class="float-md-right float-none pos_tab_btn justify-content-end">
                    <ul class="nav">
                        <li class="nav-item mb_5">
                            <a class="nav-link filtering active" data-type="today" href="javascript:void(0)">{{
                                __('dashboard.today') }}</a>
                        </li>
                        <li class="nav-item mb_5">
                            <a class="nav-link filtering" data-type="week" href="javascript:void(0)">{{
                                __('dashboard.this_week') }}</a>
                        </li>
                        <li class="nav-item mb_5">
                            <a class="nav-link filtering" data-type="month" href="javascript:void(0)">{{
                                __('dashboard.this_month') }}</a>
                        </li>
                        <li class="nav-item mb_5">
                            <a class="nav-link filtering" data-type="year" href="javascript:void(0)">{{
                                __('dashboard.this_year') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
        @if (permissionCheck('widget_card'))
        <div class="row mb_30 ign-dash-cards mt-3 mx-0"> <!--Section cards-->

            <x-backEnd.dashboard-card
                type="total_products"
                permission="widget_total_product"
                route="product.index"
                title="dashboard.total_product"
                icon="fas fa-cubes"
                :value="$totalProducts"
            />

            <x-backEnd.dashboard-card
                type="total_seller_card"
                permission="widget_total_seller"
                module="MultiVendor"
                route="admin.merchants_list"
                title="dashboard.total_seller"
                icon="fa fa-sitemap"
                :value="$totalSellers"
            />
           
            <x-backEnd.dashboard-card
                type="total_customer_card"
                permission="widget_total_customer"
                route="cusotmer.list_active"
                title="dashboard.total_customer"
                icon="fa fa-users"
                :value="$totalCustomers"
            />

            <x-backEnd.dashboard-card
                type="visitor_card"
                permission="widget_visitor"
                route="#"
                title="dashboard.visitor"
                icon="fa fa-user"
                :value="$totalvisitors"
            />

            <x-backEnd.dashboard-card
                type="total_order_card"
                permission="widget_total_order"
                route="order_manage.total_sales_index"
                title="dashboard.total_order"
                icon="fa fa-file-text-o"
                :value="$total_order"
            />
            
            <x-backEnd.dashboard-card
                type="total_pending_order_card"
                permission="widget_total_pending_order"
                route="order_manage.total_sales_index"
                title="dashboard.total_pending_order"
                icon="fa fa-clock-o"
                :value="$total_pending_order"
            />
            
            <x-backEnd.dashboard-card
                type="total_completed_order_card"
                permission="widget_total_completed_order"
                route="order_manage.total_sales_index"
                title="dashboard.total_completed_order"
                icon="fa fa-check"
                :value="$total_completed_order"
            />

            <x-backEnd.dashboard-card
                type="total_sale_amount_card"
                permission="widget_total_sale"
                route="#"
                title="dashboard.total_sale"
                icon="fa fa-line-chart"
                :value="$total_sale"
                :isCurrency="true"
            />

            <x-backEnd.dashboard-card
                type="total_review_card"
                permission="widget_total_review"
                route="#"
                title="dashboard.total_review"
                icon="fa fa-thumbs-o-up"
                :value="$total_review"
            />

            <x-backEnd.dashboard-card
                type="total_revenue_card"
                permission="widget_total_revenue"
                route="#"
                title="dashboard.today_sale_revenue"
                icon="fa fa-usd"
                :value="$total_revenue"
                :isCurrency="true"
            />
            
           
            <x-backEnd.dashboard-card
                type="total_active_customer_card"
                permission="widget_active_customer"
                route="cusotmer.list_active"
                title="dashboard.active_customer"
                icon="fa fa-user-plus"
                :value="$total_active_customers"
            />
           
            <x-backEnd.dashboard-card
                type="total_subscriber_card"
                permission="widget_total_subcriber"
                route="#"
                title="dashboard.total_subscriber"
                icon="fa fa-user-plus"
                :value="$total_subscriber"
            />
            
        </div>
        @endif
        <!-- Graficas -->
        @if(permissionCheck('dashboard_graph'))
        <div class="row mb_30 @if (permissionCheck('widget_card')) @else mt-30 @endif">
            @if (app('dashboard_setup')->where('type', 'product_graph')->first()->is_active &&
            permissionCheck('dashboard_graph_products'))
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 d-flex">
                <div class=" mb_30 shadown-md graph_dashboard w-100">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px grap-title">{{ __('dashboard.products') }}</h3>
                        </div>
                    </div>
                    <div class="chart_pie_box">
                        <canvas height="150" id="traffic-chartt"></canvas>
                    </div>
                    <div class="sales_value_legend" id="traffic-chart-legendd"></div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'order_summary_graph')->first()->is_active &&
            permissionCheck('dashboard_graph_orders_summary'))
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 d-flex">
                <div class=" mb_30 shadown-md graph_dashboard w-100">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px grap-title">{{ __('dashboard.orders_summary') }}</h3>
                        </div>
                    </div>
                    <div class="chart_pie_box">
                        <canvas height="150" id="traffic-chart2"></canvas>
                    </div>
                    <div class="sales_value_legend" id="traffic-chart-legend2"></div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'seller_graph')->first()->is_active &&
            permissionCheck('dashboard_graph_sellers') && isModuleActive('MultiVendor'))
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 d-flex">
                <div class=" mb_30 shadown-md graph_dashboard w-100">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px grap-title">{{ __('dashboard.sellers') }}</h3>
                        </div>
                    </div>
                    <div class="chart_pie_box">
                        <canvas height="150" id="traffic-chart3"></canvas>
                    </div>
                    <div class="sales_value_legend" id="traffic-chart-legend3"></div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'guest_vs_registered_order_graph')->first()->is_active &&
            permissionCheck('dashboard_graph_gueast_authorize_order_today'))
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 d-flex">
                <div class=" mb_30 shadown-md graph_dashboard w-100">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px grap-title">
                                {{ __('dashboard.guest_authorized_order_today') }}</h3>
                        </div>
                    </div>
                    <div class="chart_pie_box">
                        <canvas height="150" id="traffic-chart4"></canvas>
                    </div>
                    <div class="sales_value_legend" id="traffic-chart-legend4"></div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'today_order_summary_graph')->first()->is_active &&
            permissionCheck('dashboard_graph_today_order_summary'))
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 d-flex">
                <div class=" mb_30 shadown-md graph_dashboard w-100">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px grap-title">{{ __('dashboard.today_order_summary') }}</h3>
                        </div>
                    </div>
                    <div class="chart_pie_box">
                        <canvas height="150" id="traffic-chart5"></canvas>
                    </div>
                    <div class="sales_value_legend" id="traffic-chart-legend5"></div>
                </div>
            </div>
            @endif
        </div>

        @if (app('business_settings')->where('type', 'google_analytics')->first()->status == 1 &&
        permissionCheck('dashboard_graph_site_analytics'))
        <div class="row mb_30">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('dashboard.site_analytics') }}</h3>
                </div>
            </div>
            @if (isset($total_page_visitor))
            <div class="col-md-3 col-lg-3 col-sm-3">
                <div class="white-box single-summery active">
                    <div class="d-block mt-10">
                        <h3>{{ __('dashboard.total_visitor') }}</h3>
                        <h1 class="gradient-color2">{{ getNumberTranslate($total_page_visitor) }}</h1>
                    </div>
                </div>
            </div>
            @endif
            @if (isset($total_page_views))
            <div class="col-md-3 col-lg-3 col-sm-3">
                <div class="white-box single-summery active">
                    <div class="d-block mt-10">
                        <h3>{{ __('dashboard.total_page_views') }}</h3>
                        <h1 class="gradient-color2">{{ getNumberTranslate($total_page_views) }}</h1>
                    </div>
                </div>
            </div>
            @endif
            @if (isset($total_new_visitor))
            <div class="col-md-3 col-lg-3 col-sm-3">
                <div class="white-box single-summery active">
                    <div class="d-block mt-10">
                        <h3>{{ __('dashboard.new_visitors') }}</h3>
                        <h1 class="gradient-color2">{{ getNumberTranslate($total_new_visitor) }}</h1>
                    </div>
                </div>
            </div>
            @endif
            @if (isset($total_old_views))
            <div class="col-md-3 col-lg-3 col-sm-3">
                <div class="white-box single-summery active">
                    <div class="d-block mt-10">
                        <h3>{{ __('dashboard.old_visitors') }}</h3>
                        <h1 class="gradient-color2">{{ getNumberTranslate($total_old_views) }}</h1>
                    </div>
                </div>
            </div>
            @endif
            @if (isset($total_in_session))
            <div class="col-md-3 col-lg-3 col-sm-3">
                <div class="white-box single-summery active">
                    <div class="d-block mt-10">
                        <h3>{{ __('dashboard.total_in_sessions') }}</h3>
                        <h1 class="gradient-color2">{{getNumberTranslate( $total_in_session) }}</h1>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        @endif

        <div class="row mb_30">
            @if (app('dashboard_setup')->where('type', 'top_ten_product')->first()->is_active &&
            permissionCheck('dashboard_top_ten_product'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.top_10_product') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th>{{__('common.sl')}}</th>
                                            <th scope="col">{{__('common.name')}}</th>
                                            <th scope="col">{{ __('common.brand') }} </th>
                                            <th scope="col">{{ __('common.total_sale') }} </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($top_ten_products as $key => $product)
                                        <tr>
                                            <td>{{getNumberTranslate($key + 1)}}</td>
                                            <td><a href="{{singleProductURL($product->seller->slug, $product->slug)}}"
                                                    target="_blank">{{$product->product->product_name}}</a></td>

                                            <td>{{$product->product->brand->name}}</td>
                                            <td>{{getNumberTranslate($product->total_sale)}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if (app('dashboard_setup')->where('type', 'top_ten_seller')->first()->is_active &&
            permissionCheck('dashboard_top_ten_seller') && isModuleActive('MultiVendor'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.top_10_seller') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{__('common.sl')}}</th>
                                            <th scope="col">{{__('common.name')}}</th>
                                            <th scope="col">{{ __('common.shop') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($top_ten_sellers as $key => $top_ten_seller)
                                        <tr>
                                            <td>{{getNumberTranslate($key+1)}}</td>
                                            <td>
                                                @if ($top_ten_seller->user->slug)

                                                <a href="{{route('frontend.seller',$top_ten_seller->user->slug)}}"
                                                    target="_blank">{{$top_ten_seller->user->first_name}}
                                                    {{$top_ten_seller->user->last_name}}</a>
                                                @else
                                                <a href="{{route('frontend.seller',base64_encode($top_ten_seller->user_id))}}"
                                                    target="_blank">{{$top_ten_seller->user->first_name}}
                                                    {{$top_ten_seller->user->last_name}}</a>
                                                @endif

                                            </td>
                                            <td>
                                                @if ($top_ten_seller->user->slug)
                                                <a href="{{route('frontend.seller',$top_ten_seller->user->slug)}}"
                                                    target="_blank">{{$top_ten_seller->seller_shop_display_name}}</a>
                                                @else

                                                <a href="{{route('frontend.seller',base64_encode($top_ten_seller->user_id))}}"
                                                    target="_blank">{{$top_ten_seller->seller_shop_display_name}}</a>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'category_wise_product_qty')->first()->is_active &&
            permissionCheck('dashboard_category_wise_product_qty'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.category_wise_product_qty') }}
                            </h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{__('common.sl')}}</th>
                                            <th scope="col">{{ __('common.category_name') }}</th>
                                            <th scope="col">{{ __('common.product_qty') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categories as $key => $category)
                                        <tr>
                                            <td>{{getNumberTranslate($key+1)}}</td>
                                            <td>{{$category->name}}</td>
                                            <td>{{getNumberTranslate(count($category->products))}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if ($categoriesTotal > 10)
                    <div class="row justify-content-center mt-10">
                        <a href="{{ route('product.categories.index_info') }}" class="primary_btn_2" target="_blank">{{
                            __('common.see_all') }}</a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'category_wise_sale')->first()->is_active &&
            permissionCheck('dashboard_category_wise_product_sale'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">
                                {{ __('dashboard.category_wise_product_sale') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{__('common.sl')}}</th>
                                            <th scope="col">{{ __('common.category_name') }}</th>
                                            <th scope="col">{{ __('common.no_of_sale') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topSaleCategories as $key => $topSaleCategory)
                                        <tr>
                                            <td>{{getNumberTranslate($key+1)}}</td>
                                            <td>{{$topSaleCategory->name}}</td>
                                            <td>{{getNumberTranslate($topSaleCategory->total_sale)}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if ($categoriesTotal > 10)
                    <div class="row justify-content-center mt-10">
                        <a href="{{ route('product.categories.index_info') }}" class="primary_btn_2" target="_blank">{{
                            __('common.see_all') }}</a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'coupon_wise_sale')->first()->is_active &&
            permissionCheck('dashboard_coupon_wise_sale'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.coupon_wise_sale') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{__('common.sl')}}</th>
                                            <th scope="col">{{ __('common.type') }}</th>
                                            <th scope="col">{{ __('common.coupon') }}</th>
                                            <th scope="col">{{ __('common.total_discount') }}</th>
                                            <th scope="col">{{ __('common.num_of_uses') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($coupon_wise_sales as $key => $coupon)
                                        <tr>
                                            <td>{{getNumberTranslate($key+1)}}</td>
                                            <td>
                                                @if ($coupon->coupon_type == 1)
                                                {{__('marketing.product_base')}}
                                                @elseif ($coupon->coupon_type == 2)
                                                {{__('marketing.order_base')}}
                                                @else
                                                {{__('marketing.free_shipping')}}
                                                @endif
                                            </td>
                                            <td>{{$coupon->coupon_code}}</td>
                                            <td>{{getNumberTranslate($coupon->coupon_uses->sum('discount_amount'))}}</td>
                                            <td>{{@getNumberTranslate($coupon->coupon_uses->count())}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @if (count($total_coupon) > 10)
                    <div class="row justify-content-center mt-10">
                        <a href="{{ route('marketing.coupon.coupon_info') }}" target="_blank" class="primary_btn_2">{{
                            __('common.see_all') }}</a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'new_customers')->first()->is_active &&
            permissionCheck('dashboard_new_customers'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.new_customers') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{__('common.sl')}}</th>
                                            <th scope="col">{{ __('common.name') }}</th>
                                            <th scope="col">{{ __('common.email') }}</th>
                                            <th scope="col">{{ __('common.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($new_customers as $key => $customer)
                                        <tr>
                                            <td>{{getNumberTranslate($key+1)}}</td>
                                            <td>{{$customer->first_name}} {{ $customer->last_name }}</td>
                                            <td>{{getNumberTranslate($customer->email)}}</td>
                                            <td>
                                                <a href="{{route('customer.show_details',$customer->id)}}"
                                                    class="primary-btn radius_30px mr-10 fix-gr-bg" type="button"
                                                    target="_blank"><i class="ti-eye"></i>{{__('common.details')}}</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'recently_added_products')->first()->is_active &&
            permissionCheck('dashboard_recently_added_products'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.recently_added_products') }}
                            </h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th>{{__('common.sl')}}</th>
                                            <th scope="col">{{__('common.name')}}</th>
                                            <th scope="col">{{ __('common.brand') }}</th>
                                            <th scope="col">{{ __('common.total_sale') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recently_added_products as $key => $product)
                                        <tr>
                                            <td>{{getNumberTranslate($key + 1)}}</td>
                                            <td><a href="{{singleProductURL($product->seller->slug, $product->slug)}}"
                                                    target="_blank">{{$product->product->product_name}}</a>
                                            </td>

                                            <td>{{$product->product->brand->name}}</td>
                                            <td>{{getNumberTranslate($product->total_sale)}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'top_refferer')->first()->is_active &&
            permissionCheck('dashboard_top_refferers'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.top_refferers') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th>{{__('common.sl')}}</th>
                                            <th scope="col">{{__('common.name')}}</th>
                                            <th scope="col" width="20%">{{ __('common.count') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($top_refferers as $key => $top_refferer)
                                        <tr>
                                            <td>{{getNumberTranslate($key + 1)}}</td>
                                            <td><a href="{{route('customer.show_details',$top_refferer->user_id)}}"
                                                    target="_blank">{{$top_refferer->user->first_name}}</a></td>
                                            <td>{{getNumberTranslate($top_refferer->total_used)}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'latest_order')->first()->is_active &&
            permissionCheck('dashboard_latest_order'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.latest_order') }}</h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th>{{__('common.order_id')}}</th>
                                            <th>{{__('common.total_amount')}}</th>
                                            <th>{{__('order.order_status')}}</th>
                                            <th>{{__('common.action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latest_orders as $key => $order)
                                        <tr>
                                            <td><a href="{{route('order_manage.show_details',$order->id)}}"
                                                    target="_blank">{{getNumberTranslate($order->order_number)}}</a></td>
                                            <td>{{single_price($order->grand_total)}}</td>
                                            <td>
                                                @if ($order->is_confirmed == 1 && $order->is_completed == 1 )
                                                    <h6><span class="badge_1">{{__('common.completed')}}</span></h6>
                                                @elseif ($order->is_confirmed == 1 && $order->is_cancelled == 0)
                                                    <h6><span class="badge_1">{{__('common.confirmed')}}</span></h6>
                                                @elseif ($order->is_confirmed == 2 && $order->is_cancelled == 0)
                                                    <h6><span class="badge_4">{{__('common.declined')}}</span></h6>
                                                @elseif($order->is_cancelled == 1)
                                                <h6><span class="badge_4">{{__('common.cancelled')}}</span></h6>
                                                @else
                                                    <h6><span class="badge_4">{{__('common.pending')}}</span></h6>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{route('order_manage.show_details',$order->id)}}"
                                                    class="primary-btn radius_30px mr-10 fix-gr-bg" type="button"
                                                    target="_blank"><i class="ti-eye"></i>{{__('common.details')}}</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'latest_search_keyword')->first()->is_active &&
            permissionCheck('dashboard_latest_search_keyword'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.latest_search_keyword') }}
                            </h3>
                        </div>
                    </div>
                    <div class="QA_section">
                        <div class="QA_table ">
                            <!-- table-responsive -->
                            <div class="">
                                <table class="table Crm_table_active4 ">
                                    <thead>
                                        <tr>
                                            <th>{{__('common.sl')}}</th>
                                            <th>{{__('common.keyword')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latest_search_keywords as $key => $keyword)
                                        <tr>
                                            <td>{{getNumberTranslate( $key+1 )}}</td>
                                            <td>{{ $keyword->keyword }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if (app('dashboard_setup')->where('type', 'appealed_dispute')->first()->is_active &&
            permissionCheck('dashboard_appealed_disputed'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.appealed_disputed') }}</h3>
                        </div>
                    </div>
                    <div class="box_header_right">
                        <div class="pos_tab_btn justify-content-end">
                            <ul class="nav mb-4" role="tablist">
                                @if(isModuleActive('MultiVendor'))
                                <li class="nav-item">
                                    <a class="nav-link active show" href="#top_customer_disputed" role="tab"
                                        data-toggle="tab" id="customer_dispute" aria-selected="true">{{
                                        __('dashboard.top_customers') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#top_seller_disputed" role="tab" data-toggle="tab"
                                        id="seller_dispute" aria-selected="true">{{ __('dashboard.top_sellers') }}</a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade active show" id="top_customer_disputed">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.top_customers') }}
                                    </h3>
                                </div>
                            </div>
                            <div class="QA_section">
                                <div class="QA_table">
                                    <table class="table Crm_table_active4 ">
                                        <thead>
                                            <tr>
                                                <th>{{__('common.sl')}}</th>
                                                <th>{{__('common.name')}}</th>
                                                <th>{{__('common.amount')}}</th>
                                            </tr>
                                        </thead>
                                        @php
                                        $user_list = \App\Models\User::whereIn('id',
                                        $top_disputed_customer->pluck('customer_id'))->get(['id','first_name']);
                                        @endphp
                                        <tbody>
                                            @foreach ($top_disputed_customer as $key => $d_customer)
                                            <tr>
                                                <td>{{ getNumberTranslate($key + 1) }}</td>
                                                <td>
                                                    {{ @$user_list->where('id', @$d_customer->customer_id)->first()->first_name }}
                                                </td>
                                                <td>{{ single_price($d_customer->total) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @if(isModuleActive('MultiVendor'))
                        <div role="tabpanel" class="tab-pane fade" id="top_seller_disputed">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.top_sellers') }}</h3>
                                </div>
                            </div>
                            <div class="QA_section">
                                <div class="QA_table">
                                    <table class="table Crm_table_active4 ">
                                        <thead>
                                            <tr>
                                                <th>{{__('common.sl')}}</th>
                                                <th>{{__('common.name')}}</th>
                                                <th>{{__('common.amount')}}</th>
                                            </tr>
                                        </thead>
                                        @php
                                        $user_list = \App\Models\User::whereIn('id',
                                        $top_disputed_sellers->pluck('seller_id'))->get(['id','first_name']);
                                        @endphp
                                        <tbody>
                                            @foreach ($top_disputed_sellers as $key => $d_seller)
                                            @php
                                            $seller_info = $user_list->where('id', $d_seller->seller_id)->first();
                                            @endphp
                                            <tr>
                                                <td>{{ getNumberTranslate($key + 1) }}</td>
                                                <td>{{ $seller_info->first_name }}</td>
                                                <td>{{ single_price($seller_info->SellerRefundedAmounts) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if (app('dashboard_setup')->where('type', 'reviews')->first()->is_active &&
            permissionCheck('dashboard_recent_reviews'))
            <div class="col-lg-6 col-md-6 col-sm-6 ">
                <div class="white_box_30px mb_30 shadown-md">
                    <div class="box_header common_table_header ">
                        <div class="main-title d-md-flex">
                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.recent_reviews') }}</h3>
                        </div>
                    </div>
                    <div class="box_header_right">
                        <div class="pos_tab_btn justify-content-end mb-3">
                            <ul class="nav" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active show" href="#product_reviews" role="tab" data-toggle="tab"
                                        id="customer_dispute" aria-selected="true">{{ __('dashboard.product_reviews')
                                        }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#seller_reviews" role="tab" data-toggle="tab"
                                        id="seller_dispute" aria-selected="true">@if(isModuleActive('MultiVendor')){{
                                        __('dashboard.seller_reviews') }} @else {{__('review.company_reviews')}} @endif</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade active show" id="product_reviews">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.product_reviews') }}
                                    </h3>
                                </div>
                            </div>
                            <div class="QA_section">
                                <div class="QA_table">
                                    <div class="table-responsive">
                                        <table class="table Crm_table_active4 ">
                                            <thead>
                                                <tr>
                                                    <th>{{__('common.sl')}}</th>
                                                    <th>{{__('product.product_name')}}</th>
                                                    <th>{{__('review.rating')}}</th>
                                                    <th>{{__('review.customer_feedback')}}</th>
                                                </tr>
                                            </thead>
                                            @php
                                            $product_reviews =
                                            \Modules\Review\Entities\ProductReview::orderBy('id','desc')->get()->take(10);

                                            @endphp
                                            <tbody>
                                                @foreach ($product_reviews as $key => $review)

                                                <tr>
                                                    <td>{{ getNumberTranslate($key + 1) }}</td>
                                                    <td>
                                                        @if($review->product)
                                                        <p>{{($review->type ==
                                                            'product')?$review->product->product_name:$review->giftcard->name}}
                                                        </p>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="review_star_icon text-nowrap">
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 1?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 2?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 3?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 4?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating == 5?'':'non_rated'}}"></i>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p>{{ \Str::substr($review->review, 0, 100) }}</p>
                                                    </td>

                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="seller_reviews">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('dashboard.seller_reviews') }}
                                    </h3>
                                </div>
                            </div>
                            <div class="QA_section">
                                <div class="QA_table">
                                    <div class="table-responsive">
                                        <table class="table Crm_table_active4 ">
                                            <thead>
                                                <tr>
                                                    <th>{{__('common.sl')}}</th>
                                                    @if(isModuleActive('MultiVendor'))
                                                    <th>{{__('common.seller')}}</th>
                                                    @endif
                                                    <th>{{__('review.rating')}}</th>
                                                    <th>{{__('review.customer_feedback')}}</th>
                                                </tr>
                                            </thead>
                                            @php
                                            $seller_reviews =
                                            \Modules\Review\Entities\SellerReview::orderBy('id','desc')->get()->take(10);
                                            @endphp
                                            <tbody>
                                                @foreach ($seller_reviews as $key => $review)

                                                <tr>
                                                    <td>{{ getNumberTranslate($key + 1) }}</td>
                                                    @if(isModuleActive('MultiVendor'))
                                                    <td>{{ $review->seller->first_name }}</td>
                                                    @endif
                                                    <td>
                                                        <div class="review_star_icon text-nowrap">
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 1?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 2?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 3?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating >= 4?'':'non_rated'}}"></i>
                                                            <i
                                                                class="fas fa-star review_star {{$review->rating == 5?'':'non_rated'}}"></i>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p>{{ \Str::substr($review->review, 0, 100) }}</p>
                                                    </td>

                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <input type="hidden" id="vendorCheck" value="{{isModuleActive('MultiVendor')?'true':'false'}}">
</section>
<input type="hidden" id="graph_total_product" value="{{ $graph_total_product }}">
<input type="hidden" id="graph_admin_product" value="{{ $graph_admin_product }}">
<input type="hidden" id="graph_inactive_product" value="{{ $graph_inactive_product }}">
<input type="hidden" id="graph_seller_product" value="{{ $graph_seller_product }}">
<input type="hidden" id="graph_total_sales" value="{{ $graph_total_sales }}">
<input type="hidden" id="graph_completed_sales" value="{{ $graph_completed_sales }}">
<input type="hidden" id="graph_pending_sales" value="{{ $graph_pending_sales }}">
<input type="hidden" id="graph_cancelled_sales" value="{{ $graph_cancelled_sales }}">
<input type="hidden" id="graph_total_sellers" value="{{ $graph_total_sellers }}">
<input type="hidden" id="graph_normal_sellers" value="{{ $graph_normal_sellers }}">
<input type="hidden" id="graph_trusted_sellers" value="{{ $graph_trusted_sellers }}">
<input type="hidden" id="graph_total_carts" value="{{ $total_product_in_cart }}">
<input type="hidden" id="graph_total_authorized_order" value="{{ $graph_total_authorized_order }}">
<input type="hidden" id="graph_total_guest_order" value="{{ $graph_total_guest_order }}">
<input type="hidden" id="graph_pending_sales_today" value="{{ $graph_pending_sales_today }}">
<input type="hidden" id="graph_sales_today" value="{{ $graph_sales_today }}">
<input type="hidden" id="graph_processing_sales_today" value="{{ $graph_processing_sales_today }}">
<input type="hidden" id="graph_completed_sales_today" value="{{ $graph_completed_sales_today }}">
@endsection
@push('scripts')
<script>
    (function($) {
          "use strict";
          $(document).on('click', '.filtering', function () {
              $('.filtering').removeClass('active');
              $(this).addClass('active');
              let type = $(this).data('type');
              $('.gradient-color2').hide();
              $('.demo_wait').removeClass('d-none');
              $('.stat-icon').addClass('d-none');
              $.ajax({
                  method: "get",
                  url: "{{url('dashboard-cards-info')}}" + "/" + type,
                  success: function (data) {

                      $('.visitor_card .stat-number').text(numbertrans(data.total_visitors));
                      $('.total_sale_amount_card .stat-number').text(numbertrans(data.total_sale));
                      $('.total_order_card .stat-number').text(numbertrans(data.total_order));
                      $('.total_pending_order_card .stat-number').text(numbertrans(data.total_pending_order));
                      $('.total_completed_order_card .stat-number').text(numbertrans(data.total_completed_order));
                      $('.total_review_card .stat-number').text(numbertrans(data.total_review));
                      $('.total_revenue_card .stat-number').text(numbertrans(data.total_revenue));

                      $('.gradient-color2').show();
                      $('.demo_wait').addClass('d-none');
                      $('.stat-icon').removeClass('d-none');
                  }
              })
          });
          $(function() {

            Chart.defaults.global.legend.labels.usePointStyle = true;
            if (document.getElementById('traffic-chartt') != null) {
                if($('#vendorCheck').val() == 'true'){
                    var dataType = [$('#graph_total_product').val(), $('#graph_admin_product').val(), $('#graph_seller_product').val()];
                }else{
                    var dataType = [$('#graph_total_product').val(), $('#graph_inactive_product').val(), $('#graph_admin_product').val()];
                }
                var ctx = document.getElementById('traffic-chartt').getContext("2d");
                if ($("#traffic-chartt").length) {

                  var trafficChartData = {
                    datasets: [{
                      data: dataType,
                      backgroundColor: [
                        "#cfff98",
                        "#ff5c26",
                        "#97ba6f",
                      ],
                      hoverBackgroundColor: [
                          "#cfff98",
                          "#ff5c26",
                          "#97ba6f",
                      ],
                      borderColor: [
                        "transparent",
                        "transparent",
                        "transparent"
                      ],
                      legendColor: [
                        "#cfff98",
                        "#ff5c26",
                        "#97ba6f",
                      ]
                    }],

                    // These labels appear in the legend and in the tooltips when hovering different arcs
                    labels: [
                      '{{__("common.active")}}',
                      '{{__("common.inactive")}}',
                      '{{__("common.total")}}',
                    ]
                  };

                  var trafficChartOptions = {
                    responsive: true,
                    cutoutPercentage: 65,
                    animation: {
                      animateScale: true,
                      animateRotate: true
                    },
                    legend: false,
                    legendCallback: function(chart) {
                      var text = [];
                      text.push('<ul>');
                      for (var i = 0; i < trafficChartData.datasets[0].data.length; i++) {
                          text.push('<li><span class="legend-dots" style="background:' +
                          trafficChartData.datasets[0].legendColor[i] +
                                      '"></span><div class="legend_name"><span>');
                          if (trafficChartData.labels[i]) {
                              text.push(trafficChartData.labels[i]);
                          }
                          text.push('</span><span class="value_legend">'+numbertrans(trafficChartData.datasets[0].data[i])+'</span>')
                          text.push('</div></li>');
                      }
                      text.push('</ul>');
                      return text.join('');
                    }
                  };
                  var trafficChartCanvas = $("#traffic-chartt").get(0).getContext("2d");
                  var trafficChart = new Chart(trafficChartCanvas, {
                    type: 'doughnut',
                    data: trafficChartData,
                    options: trafficChartOptions
                  });
                  $("#traffic-chart-legendd").html(trafficChart.generateLegend());
                }
            }
            if (document.getElementById('traffic-chart2') != null) {
                Chart.defaults.global.legend.labels.usePointStyle = true;
                var ctx = document.getElementById('traffic-chart2').getContext("2d");
                if ($("#traffic-chart2").length) {

                  var trafficChartData = {
                    datasets: [{
                      data: [$('#graph_total_sales').val(), $('#graph_completed_sales').val(), $('#graph_pending_sales').val(), $('#graph_cancelled_sales').val()],
                      backgroundColor: [
                        "#ff5c26",
                        "#000080",
                        "#b7965e",
                        "#b044cf",
                      ],
                      hoverBackgroundColor: [
                        "#ff5c26",
                        "#000080",
                        "#b7965e",
                        "#b044cf",
                      ],
                      borderColor: [
                        "transparent",
                        "transparent",
                        "transparent",
                        "transparent",
                      ],
                      legendColor: [
                        "#ff5c26",
                        "#000080",
                        "#b7965e",
                        "#b044cf",
                      ]
                    }],

                    // These labels appear in the legend and in the tooltips when hovering different arcs
                    labels: [
                      '{{__("common.total")}}',
                      '{{__("common.completed")}}',
                      '{{__("common.pending")}}',
                      '{{__("common.cancel")}}',
                    ]
                  };

                  var trafficChartOptions = {
                    responsive: true,
                    cutoutPercentage: 65,
                    animation: {
                      animateScale: true,
                      animateRotate: true
                    },
                    legend: false,
                    legendCallback: function(chart) {
                      var text = [];
                      text.push('<ul>');
                      for (var i = 0; i < trafficChartData.datasets[0].data.length; i++) {
                          text.push('<li><span class="legend-dots" style="background:' +
                          trafficChartData.datasets[0].legendColor[i] +
                                      '"></span><div class="legend_name"><span>');
                          if (trafficChartData.labels[i]) {
                              text.push(trafficChartData.labels[i]);
                          }
                          text.push('</span><span class="value_legend">'+numbertrans(trafficChartData.datasets[0].data[i])+'</span>')
                          text.push('</div></li>');
                      }
                      text.push('</ul>');
                      return text.join('');
                    }
                  };
                  var trafficChartCanvas = $("#traffic-chart2").get(0).getContext("2d");
                  var trafficChart = new Chart(trafficChartCanvas, {
                    type: 'doughnut',
                    data: trafficChartData,
                    options: trafficChartOptions
                  });
                  $("#traffic-chart-legend2").html(trafficChart.generateLegend());
                }
            }
            if (document.getElementById('traffic-chart3') != null) {
                Chart.defaults.global.legend.labels.usePointStyle = true;
                var ctx = document.getElementById('traffic-chart3').getContext("2d");
                if ($("#traffic-chart3").length) {

                  var trafficChartData = {
                    datasets: [{
                      data: [$('#graph_total_sellers').val(), $('#graph_trusted_sellers').val(), $('#graph_normal_sellers').val()],
                      backgroundColor: [
                        "#d5d1fc",
                        "#b742d3",
                        "#c7eaee",
                      ],
                      hoverBackgroundColor: [
                        "#d5d1fc",
                        "#b742d3",
                        "#c7eaee",
                      ],
                      borderColor: [
                        "transparent",
                        "transparent",
                        "transparent",
                      ],
                      legendColor: [
                        "#d5d1fc",
                        "#b742d3",
                        "#c7eaee",
                      ]
                    }],

                    // These labels appear in the legend and in the tooltips when hovering different arcs
                    labels: [
                      '{{__("dashboard.total_seller")}}',
                      '{{__("common.trusted_seller")}}',
                      '{{__("seller.regular_seller")}}',
                    ]
                  };

                  var trafficChartOptions = {
                    responsive: true,
                    cutoutPercentage: 65,
                    animation: {
                      animateScale: true,
                      animateRotate: true
                    },
                    legend: false,
                    legendCallback: function(chart) {
                      var text = [];
                      text.push('<ul>');
                      for (var i = 0; i < trafficChartData.datasets[0].data.length; i++) {
                          text.push('<li><span class="legend-dots" style="background:' +
                          trafficChartData.datasets[0].legendColor[i] +
                                      '"></span><div class="legend_name"><span>');
                          if (trafficChartData.labels[i]) {
                              text.push(trafficChartData.labels[i]);
                          }
                          text.push('</span><span class="value_legend">'+numbertrans(trafficChartData.datasets[0].data[i])+'</span>')
                          text.push('</div></li>');
                      }
                      text.push('</ul>');
                      return text.join('');
                    }
                  };
                  var trafficChartCanvas = $("#traffic-chart3").get(0).getContext("2d");
                  var trafficChart = new Chart(trafficChartCanvas, {
                    type: 'doughnut',
                    data: trafficChartData,
                    options: trafficChartOptions
                  });
                  $("#traffic-chart-legend3").html(trafficChart.generateLegend());
                }
            }
            if (document.getElementById('traffic-chart4') != null) {
                Chart.defaults.global.legend.labels.usePointStyle = true;
                var ctx = document.getElementById('traffic-chart4').getContext("2d");
                if ($("#traffic-chart4").length) {

                  var trafficChartData = {
                    datasets: [{
                      data: [$('#graph_total_carts').val(), $('#graph_total_authorized_order').val(), $('#graph_total_guest_order').val()],
                      backgroundColor: [
                        "#d5d1fc",
                        "#b742d3",
                        "#c7eaee",
                      ],
                      hoverBackgroundColor: [
                        "#d5d1fc",
                        "#b742d3",
                        "#c7eaee",
                      ],
                      borderColor: [
                        "transparent",
                        "transparent",
                        "transparent",
                      ],
                      legendColor: [
                        "#d5d1fc",
                        "#b742d3",
                        "#c7eaee",
                      ]
                    }],

                    // These labels appear in the legend and in the tooltips when hovering different arcs
                    labels: [
                      '{{__("dashboard.in_carts")}}',
                      '{{__("common.registered")}}',
                      '{{__("common.guest")}}',
                    ]
                  };

                  var trafficChartOptions = {
                    responsive: true,
                    cutoutPercentage: 65,
                    animation: {
                      animateScale: true,
                      animateRotate: true
                    },
                    legend: false,
                    legendCallback: function(chart) {
                      var text = [];
                      text.push('<ul>');
                      for (var i = 0; i < trafficChartData.datasets[0].data.length; i++) {
                          text.push('<li><span class="legend-dots" style="background:' +
                          trafficChartData.datasets[0].legendColor[i] +
                                      '"></span><div class="legend_name"><span>');
                          if (trafficChartData.labels[i]) {
                              text.push(trafficChartData.labels[i]);
                          }
                          text.push('</span><span class="value_legend">'+numbertrans(trafficChartData.datasets[0].data[i])+'</span>')
                          text.push('</div></li>');
                      }
                      text.push('</ul>');
                      return text.join('');
                    }
                  };
                  var trafficChartCanvas = $("#traffic-chart4").get(0).getContext("2d");
                  var trafficChart = new Chart(trafficChartCanvas, {
                    type: 'doughnut',
                    data: trafficChartData,
                    options: trafficChartOptions
                  });
                  $("#traffic-chart-legend4").html(trafficChart.generateLegend());
                }
            }
            if (document.getElementById('traffic-chart5') != null) {
                Chart.defaults.global.legend.labels.usePointStyle = true;
                var ctx = document.getElementById('traffic-chart5').getContext("2d");
                if ($("#traffic-chart5").length) {

                  var trafficChartData = {
                    datasets: [{
                      data: [$('#graph_sales_today').val(), $('#graph_pending_sales_today').val(), $('#graph_processing_sales_today').val(), $('#graph_completed_sales_today').val()],
                      backgroundColor: [
                        "#d5d1fc",
                        "#000080",
                        "#c7eaee",
                        "#b044cf",
                      ],
                      hoverBackgroundColor: [
                        "#d5d1fc",
                        "#000080",
                        "#c7eaee",
                        "#b044cf",
                      ],
                      borderColor: [
                        "transparent",
                        "transparent",
                        "transparent",
                        "transparent",
                      ],
                      legendColor: [
                        "#d5d1fc",
                        "#000080",
                        "#c7eaee",
                        "#b044cf",
                      ]
                    }],

                    // These labels appear in the legend and in the tooltips when hovering different arcs
                    labels: [
                      '{{__("common.total")}}',
                      '{{__("common.pending")}}',
                      '{{__("defaultTheme.processing")}}',
                      '{{__("common.completed")}}',
                    ]
                  };

                  var trafficChartOptions = {
                    responsive: true,
                    cutoutPercentage: 65,
                    animation: {
                      animateScale: true,
                      animateRotate: true
                    },
                    legend: false,
                    legendCallback: function(chart) {
                      var text = [];
                      text.push('<ul>');
                      for (var i = 0; i < trafficChartData.datasets[0].data.length; i++) {
                          text.push('<li><span class="legend-dots" style="background:' +
                          trafficChartData.datasets[0].legendColor[i] +
                                      '"></span><div class="legend_name"><span>');
                          if (trafficChartData.labels[i]) {
                              text.push(trafficChartData.labels[i]);
                          }
                          text.push('</span><span class="value_legend">'+numbertrans(trafficChartData.datasets[0].data[i])+'</span>')
                          text.push('</div></li>');
                      }
                      text.push('</ul>');
                      return text.join('');
                    }
                  };
                  var trafficChartCanvas = $("#traffic-chart5").get(0).getContext("2d");
                  var trafficChart = new Chart(trafficChartCanvas, {
                    type: 'doughnut',
                    data: trafficChartData,
                    options: trafficChartOptions
                  });
                  $("#traffic-chart-legend5").html(trafficChart.generateLegend());
                }
            }
          });
        })(jQuery);

</script>
@endpush
