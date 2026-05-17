@extends('backEnd.master')

@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('modules/inhouseorder/css/create.css'))}}" />

@endsection

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor ign-in-house-order-list">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row justify-content-center">
            <div class="col-md-12 mb-10">
                <div class="row">
                    <div class="col-12">
                        <div class="box_header_right">
                            <div class="pos_tab_btn justify-content-end">
                                <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2" role="tablist">
                                    @if (permissionCheck('admin.inhouse-order.create'))
                                        <li class="nav-item mt-10">
                                            <a class="nav-link action" href="{{ route('admin.inhouse-order.create') }}"><i class="ti-plus mr-2"></i>{{ __('order.create_new_order') }}</a>
                                        </li>
                                    @endif
                                    @if (permissionCheck('inhouse_order_confirmed'))
                                        <li class="nav-item mt-10">
                                            <a class="nav-link active show" href="#order_confirmed_data" role="tab" data-toggle="tab" id="1" aria-selected="true">{{__('order.confirmed_o')}}</a>
                                        </li>
                                    @endif

                                    @if (permissionCheck('inhouse_order_completed'))
                                        <li class="nav-item mt-10">
                                            <a class="nav-link" href="#order_complete_data" role="tab" data-toggle="tab" id="2" aria-selected="true">{{__('order.completed_o')}}</a>
                                        </li>
                                    @endif

                                    @if (permissionCheck('inhouse_order_pending'))
                                        <li class="nav-item mt-10">
                                            <a class="nav-link" href="#pending_payment_data" role="tab" data-toggle="tab" id="3" aria-selected="true">{{__('order.pending_payment_o')}}</a>
                                        </li>
                                    @endif

                                    @if (permissionCheck('inhouse_order_cancelled'))
                                        <li class="nav-item mt-10">
                                            <a class="nav-link" href="#cancelled_data" role="tab" data-toggle="tab" id="4" aria-selected="true">{{__('order.cancelled_o')}}</a>
                                        </li>
                                    @endif

                                    

                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="">

                    <div class="tab-content">
                    @if (permissionCheck('inhouse_order_confirmed'))
                        <div role="tabpanel" class="tab-pane fade active show" id="order_confirmed_data" data-type="confirmed" >
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
                                                    <th width="15%">{{__('common.date')}}</th>
                                                    <th>{{__('common.order_id')}}</th>
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
                    @if (permissionCheck('inhouse_order_completed'))
                        <div role="tabpanel" class="tab-pane fade" id="order_complete_data" data-type="completed">
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
                    @if (permissionCheck('inhouse_order_pending'))
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
                    @if (permissionCheck('inhouse_order_cancelled'))
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

            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                let target = $(e.target).attr("href");

                if (!$.fn.DataTable.isDataTable(target + " table")) {
                    let tableType = $(target).data('type');
                    initOrderTable(target + " table", tableType);
                }
            });

            $(document).ready(function () {

                initOrderTable('#confirmedTable', 'confirmed');
                // initOrderTable('#pendingPaymentTable', 'pending_payment');
                // initOrderTable('#completedTable', 'completed');
                // initOrderTable('#canceledTable', 'canceled');

            });

            function initOrderTable(selector, tableType) {
                $(selector).DataTable({
                    processing: true,
                    serverSide: true,
                    stateSave: true,
                    ajax: {
                        url: "{{ route('admin.inhouse-order.get-data') }}?table=" + tableType
                    },
                    columns: orderColumns(),
                    ...datatableOptions()
                });
            }

            function datatableOptions() {
                return {
                    bLengthChange: false,
                    bDestroy: true,
                    responsive: true,
                    language: {
                        search: "<i class='ti-search'></i>",
                        searchPlaceholder: trans('common.quick_search'),
                        info: trans('common.showing_start_to_end_of_total_records'),
                        infoEmpty: trans('common.showing_0_to_0_of_0_records'),
                        zeroRecords: trans('common.no_matching_records_found'),
                        lengthMenu: trans('common.show_menu_entries'),
                        emptyTable: trans('common.no_data_available_in_table'),
                        infoFiltered: trans('common.filtered_from_total_records'),
                        paginate: {
                            next: "<i class='ti-arrow-right'></i>",
                            previous: "<i class='ti-arrow-left'></i>"
                        },
                        buttons: {
                            copyTitle: trans('common.datatables.copy_title'),
                            copySuccess: {
                                1: trans('common.datatables.copy_success_one'),
                                _: trans('common.datatables.copy_success_multiple')
                            },
                            copyKeys: trans('common.datatables.copy_keys'),
                            copyInfo: trans('common.datatables.copy_info'),
                            excelTitle: trans('common.datatables.excel_title'),
                            csvTitle: trans('common.datatables.csv_title'),
                            pdfTitle: trans('common.datatables.pdf_title'),
                            pageLength: trans('common.datatables.page_length')
                        }
                    },
                    dom: 'Bfrtip',
                    buttons: getDataTableButtons(),
                    columnDefs: [{
                        targets: [-1, -2],
                        responsivePriority: 1
                    }]
                };
            }

            function getDataTableButtons() {
                return [
                    {
                        extend: 'copyHtml5',
                        text: '<i class="fa fa-files-o"></i>',
                        title: $("#header_title").text(),
                        titleAttr: trans('common.copy'),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o"></i>',
                        title: $("#header_title").text(),
                        titleAttr: trans('common.excel'),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa fa-file-text-o"></i>',
                        titleAttr: trans('common.csv'),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o"></i>',
                        title: $("#header_title").text(),
                        titleAttr: trans('common.pdf'),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                        pageSize: 'A4',
                        margin: [0, 0, 0, 0]
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i>',
                        title: $("#header_title").text(),
                        titleAttr: trans('common.print'),
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns"></i>',
                        titleAttr: trans('common.columns'),
                        postfixButtons: ['colvisRestore']
                    }
                ];
            }

            function orderColumns() {
                return [
                    { data: 'DT_RowIndex', name: 'id', render: d => numbertrans(d) },
                    { data: 'date', name: 'date' },
                    { data: 'order_number', name: 'order_number' },
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
                    { data: 'action', name: 'action' }
                ];
            }
            
        })(jQuery);
    </script>
@endpush
