@extends('backEnd.master')
@section('styles')
    <link rel="stylesheet" href="{{asset(asset_path('modules/customer/css/style.css'))}}" />

@endsection
@section('mainContent')

    <section class="admin-visitor-area up_st_admin_visitor">

        <div class="container-fluid white_box_30px mb_30">
            <div class="row">
                <div class="col-md-12 mb-10">
                    <div class="box_header_right">
                        <div class="pos_tab_btn justify-content-end">
                            <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link active show" href="#all_user" role="tab" data-toggle="tab"
                                       id="1" aria-selected="true">{{__('affiliate.All User')}}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#active_user" role="tab" data-toggle="tab"
                                       id="1" aria-selected="true">{{__('affiliate.Active User')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#inactive_user" role="tab" data-toggle="tab"
                                       id="1" aria-selected="true">{{__('affiliate.Inactive User')}}</a>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="">
                        <div class="tab-content">

                            <div role="tabpanel" class="tab-pane fade active show" id="all_user">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.All User')}}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <!-- table-responsive -->
                                        <div class="">
                                            @include('affiliate::user.components.all_list')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" id="active_user">
                                <div class="box_header common_table_header ">
                                    <div class="main-title d-md-flex">
                                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.Active User')}}</h3>
                                    </div>
                                </div>
                                <div class="QA_section QA_section_heading_custom check_box_table">
                                    <div class="QA_table">
                                        <!-- table-responsive -->
                                        <div class="">
                                            @include('affiliate::user.components.active_list')
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" id="inactive_user">
                                    <div class="box_header common_table_header ">
                                        <div class="main-title d-md-flex">
                                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('affiliate.Inactive User')}}
                                            </h3>
                                        </div>
                                    </div>
                                    <div class="QA_section QA_section_heading_custom check_box_table">
                                        <div class="QA_table">
                                            <!-- table-responsive -->
                                            <div class="">
                                                @include('affiliate::user.components.inactive_list')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" value="{{route('affiliate.users.approved',':id')}}" id="user_active_url">

    </section>
@endsection
@push('scripts')
    <script type="text/javascript">
        (function($){
            "use strict";

            $(document).ready(function(){
                allUserDataTable();
                activeUserDataTable();
                inactiveUserDataTable();

                $(document).on('click', '.user_confirm', function(event){
                    event.preventDefault();
                    let id = $(this).data('id');
                    let url =  $('#user_active_url').val();
                    url = url.replace(':id',id);
                    $("#pre-loader").removeClass('d-none');
                    $.get(url, function(response){
                        if(response){
                            toastr.success("Usuario afiliado activado exitosamente");
                            resetAfterChange();
                        }
                    });
                });
                $(document).on('click', '.user_disable', function(event){
                    event.preventDefault();
                    let id = $(this).data('id');
                    let url =  "{{route('affiliate.users.disable_enable',':id')}}";
                    url = url.replace(':id',id);
                    $("#pre-loader").removeClass('d-none');
                    $.get(url, function(response){
                        if(response){
                            toastr.success("Usuario afiliado activado exitosamente");
                            resetAfterChange();
                        }
                    });
                });

                function resetAfterChange(){
                    allUserDataTable();
                    activeUserDataTable();
                    inactiveUserDataTable();
                }

                function allUserDataTable(){
                    $('#allUserTable').DataTable({
                        processing: true,
                        serverSide: true,
                        "ajax": ( {
                            url: "{{ route('affiliate.users.datatable') }}" + '?table=all_users'
                        }),
                        "initComplete":function(json){
                            $("#pre-loader").addClass('d-none');
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'id' },
                            { data: 'avatar', name: 'avatar' },
                            { data: 'name', name: 'name' },
                            { data: 'email', name: 'email' },
                            { data: 'phone', name: 'phone' },
                            { data: 'status', name: 'status' },
                            { data: 'action', name: 'action' }

                        ],

                        bLengthChange: false,
                        "bDestroy": true,
                        language: {
                            search: "<i class='ti-search'></i>",
                            searchPlaceholder: trans('common.quick_search'),
                            paginate: {
                                next: "<i class='ti-arrow-right'></i>",
                                previous: "<i class='ti-arrow-left'></i>"
                            }
                        },
                        dom: 'Bfrtip',
                        buttons: [{
                            extend: 'copyHtml5',
                            text: '<i class="fa fa-files-o"></i>',
                            title: $("#header_title").text(),
                            titleAttr: 'Copy',
                            exportOptions: {
                                columns: ':visible',
                                columns: ':not(:last-child)',
                            }
                        },
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i>',
                                titleAttr: 'Excel',
                                title: $("#header_title").text(),
                                margin: [10, 10, 10, 0],
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },

                            },
                            {
                                extend: 'csvHtml5',
                                text: '<i class="fa fa-file-text-o"></i>',
                                titleAttr: 'CSV',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i>',
                                title: $("#header_title").text(),
                                titleAttr: 'PDF',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },
                                pageSize: 'A4',
                                margin: [0, 0, 0, 0],
                                alignment: 'center',
                                header: true,

                            },
                            {
                                extend: 'print',
                                text: '<i class="fa fa-print"></i>',
                                titleAttr: 'Print',
                                title: $("#header_title").text(),
                                exportOptions: {
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'colvis',
                                text: '<i class="fa fa-columns"></i>',
                                postfixButtons: ['colvisRestore']
                            }
                        ],
                        columnDefs: [{
                            visible: false
                        }],
                        responsive: true,
                    });
                }

                function activeUserDataTable(){
                    $('#activeUserTable').DataTable({
                        processing: true,
                        serverSide: true,
                        "ajax": ( {
                            url: "{{ route('affiliate.users.datatable') }}" + '?table=active_users'
                        }),
                        "initComplete":function(json){
                            $("#pre-loader").addClass('d-none');
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'id' },
                            { data: 'avatar', name: 'avatar' },
                            { data: 'name', name: 'name' },
                            { data: 'email', name: 'email' },
                            { data: 'phone', name: 'phone' },
                            { data: 'status', name: 'status' },
                            { data: 'action', name: 'action' }

                        ],

                        bLengthChange: false,
                        "bDestroy": true,
                        language: {
                            search: "<i class='ti-search'></i>",
                            searchPlaceholder: trans('common.quick_search'),
                            paginate: {
                                next: "<i class='ti-arrow-right'></i>",
                                previous: "<i class='ti-arrow-left'></i>"
                            }
                        },
                        dom: 'Bfrtip',
                        buttons: [{
                            extend: 'copyHtml5',
                            text: '<i class="fa fa-files-o"></i>',
                            title: $("#header_title").text(),
                            titleAttr: 'Copy',
                            exportOptions: {
                                columns: ':visible',
                                columns: ':not(:last-child)',
                            }
                        },
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i>',
                                titleAttr: 'Excel',
                                title: $("#header_title").text(),
                                margin: [10, 10, 10, 0],
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },

                            },
                            {
                                extend: 'csvHtml5',
                                text: '<i class="fa fa-file-text-o"></i>',
                                titleAttr: 'CSV',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i>',
                                title: $("#header_title").text(),
                                titleAttr: 'PDF',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },
                                pageSize: 'A4',
                                margin: [0, 0, 0, 0],
                                alignment: 'center',
                                header: true,

                            },
                            {
                                extend: 'print',
                                text: '<i class="fa fa-print"></i>',
                                titleAttr: 'Print',
                                title: $("#header_title").text(),
                                exportOptions: {
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'colvis',
                                text: '<i class="fa fa-columns"></i>',
                                postfixButtons: ['colvisRestore']
                            }
                        ],
                        columnDefs: [{
                            visible: false
                        }],
                        responsive: true,
                    });
                }

                function inactiveUserDataTable(){
                    $('#inactiveUserTable').DataTable({
                        processing: true,
                        serverSide: true,
                        "ajax": ( {
                            url: "{{ route('affiliate.users.datatable') }}" + '?table=inactive_users'
                        }),
                        "initComplete":function(json){
                            $("#pre-loader").addClass('d-none');
                        },
                        columns: [
                            { data: 'DT_RowIndex', name: 'id' },
                            { data: 'avatar', name: 'avatar' },
                            { data: 'name', name: 'name' },
                            { data: 'email', name: 'email' },
                            { data: 'phone', name: 'phone' },
                            { data: 'status', name: 'status' },
                            { data: 'action', name: 'action' }
                        ],

                        bLengthChange: false,
                        "bDestroy": true,
                        language: {
                            search: "<i class='ti-search'></i>",
                            searchPlaceholder: trans('common.quick_search'),
                            paginate: {
                                next: "<i class='ti-arrow-right'></i>",
                                previous: "<i class='ti-arrow-left'></i>"
                            }
                        },
                        dom: 'Bfrtip',
                        buttons: [{
                            extend: 'copyHtml5',
                            text: '<i class="fa fa-files-o"></i>',
                            title: $("#header_title").text(),
                            titleAttr: 'Copy',
                            exportOptions: {
                                columns: ':visible',
                                columns: ':not(:last-child)',
                            }
                        },
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i>',
                                titleAttr: 'Excel',
                                title: $("#header_title").text(),
                                margin: [10, 10, 10, 0],
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },

                            },
                            {
                                extend: 'csvHtml5',
                                text: '<i class="fa fa-file-text-o"></i>',
                                titleAttr: 'CSV',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i>',
                                title: $("#header_title").text(),
                                titleAttr: 'PDF',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },
                                pageSize: 'A4',
                                margin: [0, 0, 0, 0],
                                alignment: 'center',
                                header: true,

                            },
                            {
                                extend: 'print',
                                text: '<i class="fa fa-print"></i>',
                                titleAttr: 'Print',
                                title: $("#header_title").text(),
                                exportOptions: {
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'colvis',
                                text: '<i class="fa fa-columns"></i>',
                                postfixButtons: ['colvisRestore']
                            }
                        ],
                        columnDefs: [{
                            visible: false
                        }],
                        responsive: true,
                    });
                }

            });
        })(jQuery);

    </script>
@endpush
