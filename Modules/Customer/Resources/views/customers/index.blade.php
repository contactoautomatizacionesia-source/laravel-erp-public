@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('modules/customer/css/style.css'))}}" />

<style>
    #deleted_customer .switch_toggle{display:none;}
    .modal-content .modal-body {
        max-height: none;
    }
</style>
@endsection
@section('mainContent')

<section class="admin-visitor-area up_st_admin_visitor ign-customer-list">

    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <div class=" pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            @if (permissionCheck('admin.customer.create'))
                            <li class="nav-item">
                                <a class="nav-link action" href="{{route('admin.customer.create')}}"><i class="ti-plus mr-2"></i>{{ __('common.create_customer') }}</a>
                            </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link active show" href="#all_customer" role="tab" data-toggle="tab"
                                    aria-selected="true">{{ __('common.all_customer') }}</a>
                            </li>

                            @if (manualActivation())
                            <li class="nav-item">
                                <a class="nav-link" href="#pending_approval_customer" role="tab" data-toggle="tab"
                                    aria-selected="true">{{ __('auth.wait_for_approval') }}</a>
                            </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link" href="#active_customer" role="tab" data-toggle="tab"
                                    aria-selected="true">{{ __('common.active_customer') }}</a>
                            </li>

                            @if (permissionCheck('customer.list_inactive'))
                            <li class="nav-item">
                                <a class="nav-link" href="#in_active_customer" role="tab" data-toggle="tab"
                                    aria-selected="true">{{ __('common.in_active_customer') }}</a>
                            </li>
                            @endif

                            @if (manualActivation())
                            <li class="nav-item">
                                <a class="nav-link" href="#rejected_customer" role="tab" data-toggle="tab"
                                    aria-selected="true">{{ __('common.rejected_customers') }}</a>
                            </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link" href="#deleted_customer" role="tab" data-toggle="tab"
                                    aria-selected="true">{{ __('common.deleted_customers') }}</a>
                            </li>

                            {{-- [DEV] Preview contrato — eliminar cuando no se necesite --}}
                            <li class="nav-item ml-auto">
                                <button class="btn btn-sm btn-warning" id="btnContractPreview">
                                    <i class="ti-eye mr-1"></i> Preview Contrato
                                </button>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="">
                    <div class="tab-content">

                        <div role="tabpanel" class="tab-pane fade active show" id="all_customer"
                            data-table-id="#allCustomerTable" data-table-type="all_customer">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('common.all_customer')}}</h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <!-- table-responsive -->
                                    <div class="">
                                        @include('customer::customers.components.all_lists')
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (manualActivation())
                        <div role="tabpanel" class="tab-pane fade" id="pending_approval_customer"
                            data-table-id="#pendingApprovalCustomerTable" data-table-type="pending_approval_customer">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('auth.wait_for_approval') }}</h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <div class="">
                                        @include('customer::customers.components.pending_approval_lists')
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div role="tabpanel" class="tab-pane fade" id="active_customer"
                            data-table-id="#activeCustomerTable" data-table-type="active_customer">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('common.active_customer')}}</h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <!-- table-responsive -->
                                    <div class="">
                                        @include('customer::customers.components.active_lists')
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="deleted_customer"
                            data-table-id="#deletedCustomerTable" data-table-type="deleted_customer">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('common.deleted_customers')}}</h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <!-- table-responsive -->
                                    <div class="">
                                        @include('customer::customers.components.deleted_lists')
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (permissionCheck('customer.list_inactive'))
                        <div role="tabpanel" class="tab-pane fade" id="in_active_customer"
                            data-table-id="#inactiveCustomerTable" data-table-type="inactive_customer">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('common.in_active_customer') }}
                                    </h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <!-- table-responsive -->
                                    <div class="">
                                        @include('customer::customers.components.in_active_lists')
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if (manualActivation())
                        <div role="tabpanel" class="tab-pane fade" id="rejected_customer"
                            data-table-id="#rejectedApprovalCustomerTable" data-table-type="rejected_customer">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('common.rejected_customers') }}
                                    </h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <div class="">
                                        @include('customer::customers.components.rejected_approval_lists')
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
    @include('backEnd.partials.delete_modal',['item_name' => __('common.customer')])
    @include('backEnd.partials.restore_modal',['item_name' => __('common.customer')])
    <div class="wrap_modal_change_plan"></div>

    {{-- [DEV] Modal preview contrato — eliminar cuando no se necesite --}}
    <div class="modal fade" id="contractPreviewModal" tabindex="-1" aria-labelledby="contractPreviewModalLabel" aria-modal="true">
        <div class="modal-dialog modal-xl" style="max-width:92vw; margin:2vh auto;">
            <div class="modal-content" style="height:96vh; display:flex; flex-direction:column;">
                <div class="modal-header py-2" style="flex-shrink:0;">
                    <h5 class="modal-title" id="contractPreviewModalLabel">Preview — Contrato (último usuario registrado)</h5>
                    <div class="ml-auto d-flex align-items-center" style="gap:8px;">
                        <button type="button" id="btnReloadContract" class="btn btn-sm btn-outline-primary" title="Recargar">
                            <i class="ti-reload"></i> Recargar
                        </button>
                        <button type="button" class="close ml-2" data-dismiss="modal" aria-label="Cerrar" style="position:static;">&times;</button>
                    </div>
                </div>
                <div class="modal-body p-0" style="position:relative;">
                    <div id="contractPreviewLoader" style="display:none; position:absolute; inset:0; background:rgba(255,255,255,0.85); z-index:10; align-items:center; justify-content:center; flex-direction:column; gap:10px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <span style="font-size:13px; color:#555;">Generando contrato…</span>
                    </div>
                    <iframe id="contractPreviewIframe" src="" title="Vista previa del contrato" style="width:100%;height:100%;border:none;display:block;"></iframe>
                </div>
            </div>
        </div>
    </div>

</section>
@endsection
@push('scripts')
    <script type="text/javascript">
        (function($){
                "use strict";

                $(document).ready(function(){
                    const approvalStatusApproved = "{{ \App\Models\User::APPROVAL_STATUS_APPROVED }}";
                    const approvalStatusRejected = "{{ \App\Models\User::APPROVAL_STATUS_REJECTED }}";

                    const customerTableConfigs = {
                        all_customer: {
                            tableId: '#allCustomerTable',
                            columns: getCustomerColumns(),
                            exportTitle: 'Todos los empresarios',
                            exportFilename: 'empresarios_todos'
                        },
                        active_customer: {
                            tableId: '#activeCustomerTable',
                            columns: getCustomerColumns(),
                            exportTitle: 'Empresarios activos',
                            exportFilename: 'empresarios_activos'
                        },
                        deleted_customer: {
                            tableId: '#deletedCustomerTable',
                            columns: getCustomerColumns(),
                            exportTitle: 'Empresarios eliminados',
                            exportFilename: 'empresarios_eliminados'
                        },
                        inactive_customer: {
                            tableId: '#inactiveCustomerTable',
                            columns: getCustomerColumns(),
                            exportTitle: 'Empresarios inactivos',
                            exportFilename: 'empresarios_inactivos'
                        },
                        @if (manualActivation())
                        pending_approval_customer: {
                            tableId: '#pendingApprovalCustomerTable',
                            columns: getApprovalColumns(),
                            exportTitle: 'Empresarios pendientes de aprobación',
                            exportFilename: 'empresarios_pendientes_aprobacion'
                        },
                        rejected_customer: {
                            tableId: '#rejectedApprovalCustomerTable',
                            columns: getApprovalColumns(),
                            exportTitle: 'Empresarios rechazados',
                            exportFilename: 'empresarios_rechazados'
                        },
                        @endif
                    };

                    initializeActiveTabTable();

                    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                        initializeTabTable($(e.target).attr('href'));
                    });

                    $(document).on('click', '.delete_customer', function(event){
                        event.preventDefault();
                        let value = $(this).data('value');
                        confirm_modal(value);
                    });

                    $(document).on('click', '.change_plan_customer', function() {
                        let customerId = $(this).data('id');
                        $('#pre-loader').removeClass('d-none');
                        $.post('{{ route('customer.show_customer_change_plan') }}', {
                            _token: '{{ csrf_token() }}',
                            id: customerId,
                        }, function(data) {
                            $('.wrap_modal_change_plan').html(data);
                            $('#change_plan_modal').modal('show');
                            $('#pre-loader').addClass('d-none');
                            initTextareaCounters();
                        });
                    });

                    $(document).on('click', '.restore_customer', function(event){
                        event.preventDefault();
                        let value = $(this).data('value');
                        confirm_restore_modal(value);
                    });

                    function getCustomerColumns(){
                        return [
                            { data: 'DT_RowIndex', name: 'id' ,render:function(data){
                                return numbertrans(data)
                            }},
                            { data: 'avatar', name: 'avatar' },
                            { data: 'unique_code', name: 'unique_code' },
                            { data: 'name', name: 'first_name' },
                            { data: 'email', name: 'email' },
                            { data: 'phone', name: 'username' },
                            { data: 'status', name: 'status' },
                            { data: 'wallet_balance', name: 'wallet_balance' },
                            { data: 'current_plan', name: 'current_plan' },
                            { data: 'orders', name: 'orders' },
                            { data: 'action', name: 'action' }
                        ];
                    }

                    function getApprovalColumns(){
                        return [
                            { data: 'DT_RowIndex', name: 'id' ,render:function(data){
                                return numbertrans(data)
                            }},
                            { data: 'avatar', name: 'avatar' },
                            { data: 'unique_code', name: 'unique_code' },
                            { data: 'name', name: 'first_name' },
                            { data: 'email', name: 'email' },
                            { data: 'email_verified', name: 'is_verified' },
                            { data: 'phone', name: 'username' },
                            { data: 'action', name: 'action' }
                        ];
                    }

                    function initializeActiveTabTable(){
                        let activeTabSelector = $('.tab-pane.active.show').first().attr('id');

                        if (activeTabSelector) {
                            initializeTabTable('#' + activeTabSelector);
                        }
                    }

                    function initializeTabTable(tabSelector){
                        let $tabPane = $(tabSelector);

                        if ($tabPane.length === 0) {
                            return;
                        }

                        let tableType = $tabPane.data('table-type');
                        let tableConfig = customerTableConfigs[tableType];
                        let tableId = $tabPane.data('table-id') || (tableConfig ? tableConfig.tableId : null);

                        if (!tableConfig || !tableId || !$(tableId).length || $.fn.DataTable.isDataTable(tableId)) {
                            return;
                        }

                        let ajaxUrl = "{{ route('cusotmer.list.get-data') }}" + '?table=' + tableType;
                        initGlobalDataTable(tableId, ajaxUrl, tableConfig.columns, {
                            exportTitle: tableConfig.exportTitle,
                            exportFilename: tableConfig.exportFilename
                        });
                    }

                    function reloadTableById(tableId, tableType){
                        if ($(tableId).length === 0) {
                            return;
                        }

                        if ($.fn.DataTable.isDataTable(tableId)) {
                            $(tableId).DataTable().ajax.reload(null, false);
                            return;
                        }

                        initializeDataTableByType(tableType);
                    }

                    function initializeDataTableByType(tableType){
                        let tableConfig = customerTableConfigs[tableType];

                        if (!tableConfig) {
                            return;
                        }

                        let ajaxUrl = "{{ route('cusotmer.list.get-data') }}" + '?table=' + tableType;
                        initGlobalDataTable(tableConfig.tableId, ajaxUrl, tableConfig.columns, {
                            exportTitle: tableConfig.exportTitle,
                            exportFilename: tableConfig.exportFilename
                        });
                    }

                    function reloadCustomerTables(){
                        Object.keys(customerTableConfigs).forEach(function(tableType){
                            reloadTableById(customerTableConfigs[tableType].tableId, tableType);
                        });
                    }

                    function submitApprovalStatus(id, status, reason = null){
                        $("#pre-loader").removeClass('d-none');

                        $.post('{{ route('customer.update_approval_status') }}', {
                            _token: '{{ csrf_token() }}',
                            id: id,
                            status: status,
                            reason: reason
                        }, function(response){
                            if(response.status == 1){
                                toastr.success(response.message, "{{__('common.success')}}");
                                reloadCustomerTables();
                            }else{
                                toastr.error(response.message || "{{__('common.error_message')}}", "{{__('common.error')}}");
                            }
                        }).fail(function(response){
                            let message = "{{__('common.error_message')}}";

                            if (response.responseJSON && response.responseJSON.message) {
                                message = response.responseJSON.message;
                            } else if (response.responseJSON && response.responseJSON.errors) {
                                const firstErrorKey = Object.keys(response.responseJSON.errors)[0];
                                if (firstErrorKey && response.responseJSON.errors[firstErrorKey].length) {
                                    message = response.responseJSON.errors[firstErrorKey][0];
                                }
                            }

                            toastr.error(message, "{{__('common.error')}}");
                        }).always(function(){
                            $("#pre-loader").addClass('d-none');
                        });
                    }

                    $(document).on('click', '.update_approval_status', function(event){
                        event.preventDefault();

                        let id = $(this).data('id');
                        let status = $(this).data('status');
                        let currentStatus = ($(this).data('current-status') || approvalStatusApproved).toString();
                        let reasonRequired = status === approvalStatusRejected || (status === approvalStatusApproved && currentStatus === approvalStatusRejected);

                        if (!reasonRequired) {
                            submitApprovalStatus(id, status);
                            return;
                        }

                        let reasonTitle = status === approvalStatusRejected
                            ? "{{ __('common.rejection_reason') }}"
                            : "{{ __('common.approval_reason') }}";
                        let reasonPlaceholder = status === approvalStatusRejected
                            ? "{{ __('common.write_rejection_reason') }}"
                            : "{{ __('common.write_approval_reason') }}";
                        let reasonRequiredMessage = status === approvalStatusRejected
                            ? "{{ __('common.provide_cancellation_reason') }}"
                            : "{{ __('common.provide_approval_reason') }}";

                        swal({
                            title: reasonTitle,
                            content: {
                                element: "textarea",
                                attributes: {
                                    placeholder: reasonPlaceholder,
                                    rows: 4,
                                    class: "swal-textarea-custom"
                                }
                            },
                            buttons: {
                                cancel: {
                                    text: "{{ __('common.cancel') }}",
                                    visible: true,
                                    className: "btn-cancel-custom"
                                },
                                confirm: {
                                    text: "{{ __('common.confirm') }}",
                                    className: "btn-confirm-custom"
                                }
                            },
                            closeOnClickOutside: false,
                        }).then(function(value){
                            if (value === null) {
                                return;
                            }

                            let reason = (value || '').toString().trim();
                            if (reason === '') {
                                toastr.error(reasonRequiredMessage, "{{ __('common.error') }}");
                                return;
                            }

                            submitApprovalStatus(id, status, reason);
                        });
                    });

                    $(document).on('change', '.update_active_status', function(event){
                        let id = $(this).data('id');
                        let status = 0;

                        if($(this).prop('checked')){
                            status = 1;
                        }
                        else{
                            status = 0;
                        }
                        $("#pre-loader").removeClass('d-none');

                        $.post('{{ route('customer.update_active_status') }}', {_token:'{{ csrf_token() }}', id:id, status:status}, function(data){
                            if(data == 1){
                                toastr.success("{{__('common.updated_successfully')}}","{{__('common.success')}}");
                                reloadCustomerTables();
                            }
                            else{
                                toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                            }
                            $("#pre-loader").addClass('d-none');
                        })

                        .fail(function(response) {
                                if(response.responseJSON.error){
                                        toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                                        $('#pre-loader').addClass('d-none');
                                        return false;
                                    }

                                });
                    });

                });
            })(jQuery);

    </script>

    {{-- [DEV] Modal preview contrato --}}
    <script>
        var contractPreviewUrl = '{{ route("admin.customer.contract_preview") }}';

        $('#btnContractPreview').on('click', function () {
            $('#contractPreviewModal').modal('show');
            document.getElementById('contractPreviewIframe').src = contractPreviewUrl;
        });

        function reloadContractPreview() {
            var iframe = document.getElementById('contractPreviewIframe');
            var loader = document.getElementById('contractPreviewLoader');
            loader.style.display = 'flex';
            iframe.src = contractPreviewUrl;
            iframe.onload = function () { loader.style.display = 'none'; };
        }

        $('#btnContractPreview').on('click', function () {
            $('#contractPreviewModal').modal('show');
            reloadContractPreview();
        });

        $('#btnReloadContract').on('click', reloadContractPreview);
    </script>
@endpush

