@extends('backEnd.master')
@section('styles')

<link rel="stylesheet" href="{{asset(asset_path('modules/ordermanage/css/style.css'))}}" />

@endsection
@section('mainContent')

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row justify-content-center">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <div class=" pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2" role="tablist">
                            @if (permissionCheck('pending_orders'))
                                <li class="nav-item">
                                    <a class="nav-link active show" href="#order_pending_data" role="tab" data-toggle="tab" id="1" aria-selected="true">{{__('order.pending_o')}}</a>
                                </li>
                            @endif

                            @if (permissionCheck('confirmed_orders'))
                                <li class="nav-item">
                                    <a class="nav-link" href="#order_confirmed_data" role="tab" data-toggle="tab" id="2" aria-selected="true">{{__('order.confirmed_o')}}</a>
                                </li>
                            @endif

                            @if (permissionCheck('completed_orders'))
                                <li class="nav-item">
                                    <a class="nav-link" href="#order_complete_data" role="tab" data-toggle="tab" id="3" aria-selected="true">{{__('order.completed_o')}}</a>
                                </li>
                            @endif

                            @if (permissionCheck('pending_payment_orders'))
                                <li class="nav-item">
                                    <a class="nav-link" href="#pending_payment_data" role="tab" data-toggle="tab" id="4" aria-selected="true">{{__('order.pending_payment_o')}}</a>
                                </li>
                            @endif

                            @if (permissionCheck('cancelled_orders'))
                                <li class="nav-item">
                                    <a class="nav-link" href="#cancelled_data" role="tab" data-toggle="tab" id="5" aria-selected="true">{{__('order.cancelled_o')}}</a>
                                </li>
                            @endif

                            @if (permissionCheck('inhouse_orders'))
                                <li class="nav-item">
                                    <a class="nav-link" href="#inhouse_order_data" role="tab" data-toggle="tab" id="6" aria-selected="true">{{__('order.inhouse_orders')}}</a>
                                </li>
                            @endif

                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="">
                    <div class="tab-content">
                        @if (permissionCheck('pending_orders'))
                            <div role="tabpanel" class="tab-pane fade active show" id="order_pending_data" data-type="pending">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('order.pending_orders')}}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">

                                        <div class="" id="latest_order_div">
                                            <table class="table" id="orderPendingTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('common.sl')}}</th>
                                                        <th width="10%">{{__('common.date')}}</th>
                                                        <th>{{__('common.order_id')}}</th>
                                                        <th>{{__('common.payment_reference')}}</th>
                                                        <th>{{__('common.customer')}}</th>
                                                        <th>{{__('common.email')}}</th>
                                                        <th>{{__('common.products')}}</th>
                                                        <th>{{__('common.total_amount')}}</th>
                                                        <th>{{__('common.totals_points')}}</th>
                                                        <th>{{__('common.origin')}}</th>
                                                        <th>{{__('common.payment_method')}}</th>
                                                        <th>{{__('common.cost_center')}}</th>
                                                        <th>{{__('order.order_status')}}</th>
                                                        <th>{{__('order.is_paid')}}</th>
                                                        <th>{{__('common.action')}}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (permissionCheck('confirmed_orders'))
                            <div role="tabpanel" class="tab-pane fade" id="order_confirmed_data" data-type="confirmed">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('order.confirmed_orders')}}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">

                                        <div class="" id="latest_order_div">
                                            <table class="table" id="confirmedTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('common.sl')}}</th>
                                                        <th width="10%">{{__('common.date')}}</th>
                                                        <th>{{__('common.order_id')}}</th>
                                                        <th>{{__('common.payment_reference')}}</th>
                                                        <th>{{__('common.customer')}}</th>
                                                        <th>{{__('common.email')}}</th>
                                                        <th>{{__('common.products')}}</th>
                                                        <th>{{__('common.total_amount')}}</th>
                                                        <th>{{__('common.totals_points')}}</th>
                                                        <th>{{__('common.origin')}}</th>
                                                        <th>{{__('common.payment_method')}}</th>
                                                        <th>{{__('common.cost_center')}}</th>
                                                        <th>{{__('order.order_status')}}</th>
                                                        <th>{{__('order.is_paid')}}</th>
                                                        <th>{{__('common.action')}}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (permissionCheck('completed_orders'))
                            <div role="tabpanel" class="tab-pane fade" id="order_complete_data" data-type="completed" >
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('order.completed_orders')}}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">

                                        <div class="" id="latest_order_div">
                                            <table class="table" id="completedTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('common.sl')}}</th>
                                                        <th width="10%">{{__('common.date')}}</th>
                                                        <th>{{__('common.order_id')}}</th>
                                                        <th>{{__('common.payment_reference')}}</th>
                                                        <th>{{__('common.customer')}}</th>
                                                        <th>{{__('common.email')}}</th>
                                                        <th>{{__('common.products')}}</th>
                                                        <th>{{__('common.total_amount')}}</th>
                                                        <th>{{__('common.totals_points')}}</th>
                                                        <th>{{__('common.origin')}}</th>
                                                        <th>{{__('common.payment_method')}}</th>
                                                        <th>{{__('common.cost_center')}}</th>
                                                        <th>{{__('order.order_status')}}</th>
                                                        <th>{{__('order.is_paid')}}</th>
                                                        <th>{{__('common.action')}}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (permissionCheck('pending_payment_orders'))
                            <div role="tabpanel" class="tab-pane fade" id="pending_payment_data" data-type="pending_payment">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('order.pending_payment_orders')}}</h3>
                                    </div>
                                </div>

                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">

                                        <div class="" id="latest_order_div">
                                            <table class="table" id="pendingPaymentTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('common.sl')}}</th>
                                                        <th width="10%">{{__('common.date')}}</th>
                                                        <th>{{__('common.order_id')}}</th>
                                                        <th>{{__('common.payment_reference')}}</th>
                                                        <th>{{__('common.customer')}}</th>
                                                        <th>{{__('common.email')}}</th>
                                                        <th>{{__('common.products')}}</th>
                                                        <th>{{__('common.total_amount')}}</th>
                                                        <th>{{__('common.totals_points')}}</th>
                                                        <th>{{__('common.origin')}}</th>
                                                        <th>{{__('common.payment_method')}}</th>
                                                        <th>{{__('common.cost_center')}}</th>
                                                        <th>{{__('order.order_status')}}</th>
                                                        <th>{{__('order.is_paid')}}</th>
                                                        <th>{{__('common.action')}}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (permissionCheck('cancelled_orders'))
                            <div role="tabpanel" class="tab-pane fade" id="cancelled_data" data-type="canceled">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('order.cancelled_orders')}}</h3>
                                    </div>
                                </div>

                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">

                                        <div class="" id="latest_order_div">
                                            <table class="table" id="canceledTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('common.sl')}}</th>
                                                        <th width="10%">{{__('common.date')}}</th>
                                                        <th>{{__('common.order_id')}}</th>
                                                        <th>{{__('common.payment_reference')}}</th>
                                                        <th>{{__('common.customer')}}</th>
                                                        <th>{{__('common.email')}}</th>
                                                        <th>{{__('common.products')}}</th>
                                                        <th>{{__('common.total_amount')}}</th>
                                                        <th>{{__('common.totals_points')}}</th>
                                                        <th>{{__('common.origin')}}</th>
                                                        <th>{{__('common.payment_method')}}</th>
                                                        <th>{{__('common.cost_center')}}</th>
                                                        <th>{{__('order.order_status')}}</th>
                                                        <th>{{__('order.is_paid')}}</th>
                                                        <th>{{__('common.action')}}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (permissionCheck('inhouse_orders'))
                            <div role="tabpanel" class="tab-pane fade" id="inhouse_order_data"  data-type="inhouse">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('order.inhouse_orders')}}</h3>
                                    </div>
                                </div>

                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">

                                        <div class="" id="latest_order_div">
                                            <table class="table" id="inhouseOrderTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('common.sl')}}</th>
                                                        <th width="10%">{{__('common.date')}}</th>
                                                        <th>{{__('common.order_id')}}</th>
                                                        <th>{{__('common.payment_reference')}}</th>
                                                        <th>{{__('common.customer')}}</th>
                                                        <th>{{__('common.email')}}</th>
                                                        <th>{{__('common.products')}}</th>
                                                        <th>{{__('common.total_amount')}}</th>
                                                        <th>{{__('common.totals_points')}}</th>
                                                        <th>{{__('common.origin')}}</th>
                                                        <th>{{__('common.payment_method')}}</th>
                                                        <th>{{__('common.cost_center')}}</th>
                                                        <th>{{__('order.order_status')}}</th>
                                                        <th>{{__('order.is_paid')}}</th>
                                                        <th>{{__('common.action')}}</th>
                                                    </tr>
                                                </thead>

                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>

        </div>
    </div>

</section>
@endsection

@push('scripts')
    <script>
        (function($){
            "use strict";

            // Definimos las columnas específicas para ESTA vista
            const orderColumns = [
                { data: 'DT_RowIndex', name: 'id', render: d => numbertrans(d) },
                { data: 'date', name: 'date' },
                { data: 'order_number', name: 'order_number' },
                { data: 'payment_reference', name: 'payment_reference' },
                { data: 'customer', name: 'customer' },
                { data: 'email', name: 'customer.email' },
                { data: 'total_qty', name: 'total_qty' },
                { data: 'total_amount', name: 'grand_total' },
                { data: 'total_points', name: 'total_points' },
                { data: 'origin', name: 'origin' },
                { data: 'payment_method', name: 'payment_method' },
                { data: 'cost_center', name: 'cost_center' },
                { data: 'order_status', name: 'order_status' },
                { data: 'is_paid', name: 'is_paid' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ];

            // Inicialización diferida por Tabs (Bootstrap)
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                let target = $(e.target).attr("href");

                if (!$.fn.DataTable.isDataTable(target + " table")) {
                    let tableType = $(target).data('type');
                    let ajaxUrl = "{{ route('order_manage.total_sales_get_data') }}?table=" + tableType;
                    
                    // LLAMADA A LA FUNCIÓN GLOBAL
                    initGlobalDataTable(target + " table", ajaxUrl, orderColumns, {
                        columnDefs: [
                            { targets: [-1, -2], responsivePriority: 1 }
                        ]
                    });
                }
            });

            // Inicialización de la tabla principal (por defecto)
            $(document).ready(function () {
                let defaultUrl = "{{ route('order_manage.total_sales_get_data') }}?table=pending";
                initGlobalDataTable('#orderPendingTable', defaultUrl, orderColumns, {
                    columnDefs: [
                        { targets: [-1, -2], responsivePriority: 1 }
                    ]
                });
            });

        })(jQuery);
    </script>
@endpush