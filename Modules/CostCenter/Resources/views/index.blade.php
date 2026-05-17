@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">

    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <div class=" pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link action" href="#" id="btn_create_cost_center">
                                    <i class="ti-plus mr-2"></i>{{ __('cost_center.create_division') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link active show" href="#active_cost_centers" role="tab" data-toggle="tab" aria-selected="true">
                                    {{ __('cost_center.active_divisions') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="#deleted_cost_centers" role="tab" data-toggle="tab" aria-selected="false">
                                    {{ __('cost_center.deleted_divisions') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-12">
                <div class="">
                    <div class="tab-content">

                        {{-- Tab: Activos --}}
                        <div role="tabpanel" class="tab-pane fade active show" id="active_cost_centers">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('cost_center.active_divisions') }}</h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <div class="">
                                        @include('costcenter::components.active_list')
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab: Eliminados --}}
                        <div role="tabpanel" class="tab-pane fade" id="deleted_cost_centers">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('cost_center.deleted_divisions') }}</h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <div class="">
                                        @include('costcenter::components.deleted_list')
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('costcenter::components.form_modal')
    @include('backEnd.partials.delete_modal',['item_name' => __('cost_center.cost_center')])
    @include('backEnd.partials.restore_modal',['item_name' => __('cost_center.cost_center')])
</section>
@endsection

@push('scripts')
<script src="{{ asset('public/js/nice-ajax.js') }}" defer></script>

<script type="text/javascript">
    (function($){
        "use strict";

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        let activeTable, deletedTable;
        const openCostCenterId = @json(request('open_cost_center'));

        $(document).ready(function(){

            loadPaymentMethods();

            function loadPaymentMethods() {
                $.get("{{ route('cost_centers.get-payment-methods') }}", function(data) {
                    $('#payment_form_id').empty().append(new Option("{{ __('cost_center.select_payment_form') }}", ""));
                    data.forEach(item => $('#payment_form_id').append(new Option(item.name, item.id)));
                    if ($.fn.niceSelect) {
                        $('.nice-select-regular').niceSelect('update'); // ← aquí dentro
                    }
                });
            }

            // DataTable columns config
            let dtColumns = [
                { data: 'code', name: 'code', className: 'text-nowrap', width: '90px' },
                { data: 'name', name: 'name', width: '170px' },
                { data: 'city_name', name: 'city_name', orderable: false, searchable: false, width: '120px' },
                { data: 'address', name: 'address', width: '240px' },
                { data: 'pin_code', name: 'pin_code', className: 'text-nowrap', width: '120px' },
                { data: 'phone', name: 'phone', className: 'text-nowrap', width: '120px' },
                { data: 'brand_name', name: 'brand_name', orderable: false, searchable: false, className: 'text-nowrap', width: '120px' },
                { data: 'payment_methods_list', name: 'payment_methods_list', orderable: false, searchable: false, className: 'text-nowrap', width: '150px' },
                { data: 'default_badge', name: 'is_default', searchable: false, className: 'text-center', width: '110px' },
                { data: 'status_badge', name: 'status', searchable: false, className: 'text-center', width: '90px' },
                { data: 'created_formatted', name: 'created_at', className: 'text-nowrap', width: '130px' },
                { data: 'updated_formatted', name: 'updated_at', className: 'text-nowrap', width: '130px' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-nowrap text-right', width: '170px' }
            ];

            let dtButtons = [
                {
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    title: "{{ __('cost_center.divisions') }}",
                    titleAttr: 'Copy',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    title: "{{ __('cost_center.divisions') }}",
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    title: "{{ __('cost_center.divisions') }}",
                    titleAttr: 'PDF',
                    exportOptions: { columns: ':not(:last-child)' },
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: "{{ __('cost_center.divisions') }}",
                    exportOptions: { columns: ':not(:last-child)' }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    postfixButtons: ['colvisRestore']
                }
            ];

            // Active DataTable
            activeTable = initGlobalDataTable("#activeCostCenterTable", "{{ route('cost_centers.get-data') }}" + '?table=active', dtColumns, {
                buttons: dtButtons,
                autoWidth: false,
                columnDefs: [
                    { targets: 12, responsivePriority: 1 },
                    { targets: 1, responsivePriority: 2 },
                    { targets: 0, responsivePriority: 3 },
                    { targets: 8, responsivePriority: 4 },
                    { targets: 9, responsivePriority: 5 },
                    { targets: 3, responsivePriority: 6 },
                    { targets: 2, responsivePriority: 7 },
                    { targets: 4, responsivePriority: 8 },
                    { targets: 5, responsivePriority: 9 },
                    { targets: 6, responsivePriority: 10 },
                    { targets: 7, responsivePriority: 11 },
                    { targets: 10, responsivePriority: 100 },
                    { targets: 11, responsivePriority: 101 }
                ]
            });

            // Deleted DataTable
            deletedTable = initGlobalDataTable("#deletedCostCenterTable", "{{ route('cost_centers.get-data') }}" + '?table=deleted', dtColumns, {
                buttons: dtButtons,
                autoWidth: false,
                columnDefs: [
                    { targets: 12, responsivePriority: 1 },
                    { targets: 1, responsivePriority: 2 },
                    { targets: 0, responsivePriority: 3 },
                    { targets: 8, responsivePriority: 4 },
                    { targets: 9, responsivePriority: 5 },
                    { targets: 3, responsivePriority: 6 },
                    { targets: 2, responsivePriority: 7 },
                    { targets: 4, responsivePriority: 8 },
                    { targets: 5, responsivePriority: 9 },
                    { targets: 6, responsivePriority: 10 },
                    { targets: 7, responsivePriority: 11 },
                    { targets: 10, responsivePriority: 100 },
                    { targets: 11, responsivePriority: 101 }
                ]
            });

            // Abrir Modal Crear
            $('#btn_create_cost_center').on('click', function(e) {
                e.preventDefault();
                resetForm();
                $('#costCenterFormModalLabel').text("{{ __('cost_center.create_division') }}");
                $('#costCenterFormModal').modal('show');
            });

            function resetForm() {
                $('#costCenterForm')[0].reset();
                $('#cost_center_id').val('');
                $('#pin_code').val('');
                $('#city_id').val(null);
                $('#city_name').val('');
                $('#brand_id').val(null);
                $('#brand_name').val('');
                $('#payment_form_id').val('');
                if ($.fn.niceSelect) {
                    $('.nice-select-ajax, .nice-select-regular').niceSelect('update');
                }
                $('#status').prop('checked', true);
                $('#is_default').prop('checked', false);
            }

            // Guardar o Actualizar
            $('#costCenterForm').on('submit', function(e) {
                e.preventDefault();
                $('#pre-loader').removeClass('d-none');

                let formData = $(this).serialize();
                let costCenterId = $('#cost_center_id').val();
                let url = costCenterId
                    ? "{{ route('cost_centers.update', 0) }}".replace('/0/', '/' + costCenterId + '/')
                    : "{{ route('cost_centers.store') }}";

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if(response.success) {
                            toastr.success(response.message, "{{ __('common.success') }}");
                            $('#costCenterFormModal').modal('hide');
                            activeTable.ajax.reload();
                            deletedTable.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.error || "{{ __('common.error_message') }}", "{{ __('common.error') }}");
                    },
                    complete: function() {
                        $('#pre-loader').addClass('d-none');
                    }
                });
            });

            function openCostCenterModal(id) {
                $('#pre-loader').removeClass('d-none');
                resetForm();

                let editUrl = "{{ route('cost_centers.edit', ':id') }}".replace(':id', id);
                $.get(editUrl, function(cc) {
                    $('#cost_center_id').val(cc.id);
                    $('#code').val(cc.code);
                    $('#cc_name').val(cc.name);
                    $('#address').val(cc.address);
                    $('#pin_code').val(cc.pin_code);
                    $('#phone').val(cc.phone);
                    $('#comment').val(cc.comment);
                    $('#status').prop('checked', cc.status == 1);
                    $('#is_default').prop('checked', cc.is_default == 1);

                    // Set city — use Option() to avoid XSS from untrusted DB values
                    if(cc.city_id && cc.city_text) {
                        let cityOption = new Option(cc.city_text, cc.city_id, true, true);
                        $('#city_id').empty().append(cityOption);
                        $('#city_name').val(cc.city_text);
                        if ($.fn.niceSelect) {
                            $('#city_id').niceSelect('update');
                        }
                    }

                    // Set brand — use Option() to avoid XSS from untrusted DB values
                    if(cc.brand_id && cc.brand_text) {
                        let brandOption = new Option(cc.brand_text, cc.brand_id, true, true);
                        $('#brand_id').empty().append(brandOption);
                        $('#brand_name').val(cc.brand_text);
                    }

                    // Set payment form
                    if(cc.payment_form_id) {
                        $('#payment_form_id').val(cc.payment_form_id);
                    }

                    if ($.fn.niceSelect) {
                        $('.nice-select-ajax, .nice-select-regular').niceSelect('update');
                    }

                    $('#costCenterFormModalLabel').text("{{ __('cost_center.edit_division') }}");
                    $('#costCenterFormModal').modal('show');
                    $('#pre-loader').addClass('d-none');
                }).fail(function() {
                    toastr.error("{{ __('common.error_message') }}", "{{ __('common.error') }}");
                    $('#pre-loader').addClass('d-none');
                });
            }

            // Editar
            $(document).on('click', '.edit_cost_center', function(e) {
                e.preventDefault();
                openCostCenterModal($(this).data('value'));
            });

            if (openCostCenterId) {
                openCostCenterModal(openCostCenterId);

                if (window.history && window.history.replaceState) {
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.delete('open_cost_center');
                    window.history.replaceState({}, document.title, currentUrl.toString());
                }
            }

            // Eliminar (softDelete)
            $(document).on('click', '.delete_cost_center', function(e) {
                e.preventDefault();
                let id = $(this).data('value');
                let destroyUrl = "{{ route('cost_centers.destroy', ':id') }}".replace(':id', id);
                confirm_modal(destroyUrl);
            });

            $(document).on('click', '#delete_link', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                $('#pre-loader').removeClass('d-none');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        toastr.success(response.message || "{{ __('common.deleted_successfully') }}", "{{ __('common.success') }}");
                        $('#confirm-delete').modal('hide');
                        activeTable.ajax.reload();
                        deletedTable.ajax.reload();
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "{{ __('common.error') }}";
                        toastr.error(msg, "{{ __('common.error') }}");
                        $('#confirm-delete').modal('hide');
                    },
                    complete: function() {
                        $('#pre-loader').addClass('d-none');
                    }
                });
            });

            // Restaurar
            $(document).on('click', '.restore_cost_center', function(e) {
                e.preventDefault();
                let id = $(this).data('value');
                let restoreUrl = "{{ route('cost_centers.restore', ':id') }}".replace(':id', id);
                confirm_restore_modal(restoreUrl);
            });

            $(document).on('click', '#restore_link', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                $('#pre-loader').removeClass('d-none');

                $.ajax({
                    url: url,
                    type: "POST",
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        toastr.success(response.message || "{{ __('common.restored_successfully') }}", "{{ __('common.success') }}");
                        $('#confirm-restore').modal('hide');
                        activeTable.ajax.reload();
                        deletedTable.ajax.reload();
                    },
                    error: function(xhr) {
                        let msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "{{ __('common.error') }}";
                        toastr.error(msg, "{{ __('common.error') }}");
                        $('#confirm-restore').modal('hide');
                    },
                    complete: function() {
                        $('#pre-loader').addClass('d-none');
                    }
                });
            });

            $(document).on('change', '.status_toggle', function() {
                $('#pre-loader').removeClass('d-none');
                let id = $(this).data('id');
                let status = $(this).is(':checked') ? 1 : 0;
                let toggleElement = this;

                $.ajax({
                    url: "{{ route('cost_centers.update-status') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        toastr.success(response.message, "{{ __('common.success') }}");
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.error || "{{ __('common.error_message') }}", "{{ __('common.error') }}");
                        $(toggleElement).prop('checked', !status);
                    },
                    complete: function() {
                        $('#pre-loader').addClass('d-none');
                    }
                });
            });

            $(document).on('change', '.default_toggle', function() {
                $('#pre-loader').removeClass('d-none');
                let id = $(this).data('id');
                let isDefault = $(this).is(':checked') ? 1 : 0;
                let toggleElement = this;

                $.ajax({
                    url: "{{ route('cost_centers.update-default') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        is_default: isDefault
                    },
                    success: function(response) {
                        toastr.success(response.message, "{{ __('common.success') }}");
                        activeTable.ajax.reload(null, false);
                        deletedTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.error || "{{ __('common.error_message') }}", "{{ __('common.error') }}");
                        $(toggleElement).prop('checked', !isDefault);
                    },
                    complete: function() {
                        $('#pre-loader').addClass('d-none');
                    }
                });
            });

        });
    })(jQuery);
</script>
@endpush
