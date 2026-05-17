@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-inventory-exits">

        {{-- Botones de acción --}}
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link action" href="#" id="btnNewExit">
                                    <i class="ti-plus mr-2"></i> {{ __('inventoryexit::messages.new_request') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Título --}}
        <div class="row">
            <div class="col-md-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('inventoryexit::messages.title') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="form-card">
            <h3>{{__('common.filters')}}</h3>
            <div class="row">
                <div class="col-md-6 col-lg-3 mt-sm-2">
                    <label class="primary_input_label" for="filter_status">
                        {{ __('inventoryexit::messages.filter_status') }}
                    </label>
                    <select id="filter_status" class="primary_input_select">
                        <option value="">{{ __('inventoryexit::messages.all_statuses') }}</option>
                        <option value="pending">{{ __('inventoryexit::messages.status_pending') }}</option>
                        <option value="approved">{{ __('inventoryexit::messages.status_approved') }}</option>
                        <option value="rejected">{{ __('inventoryexit::messages.status_rejected') }}</option>
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 mt-sm-2">
                    <label class="primary_input_label" for="filter_cost_center">
                        {{ __('inventoryexit::messages.filter_cost_center') }}
                    </label>
                    <select id="filter_cost_center" class="primary_input_select">
                        <option value="">{{ __('inventoryexit::messages.all_cost_centers') }}</option>
                        @foreach($costCenters as $cc)
                            <option value="center-{{ $cc->id }}">{{ $cc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-lg-3 mt-sm-2">
                    <label class="primary_input_label" for="filter_date_from">
                        {{ __('inventoryexit::messages.filter_date_from') }}
                    </label>
                    <input type="date" id="filter_date_from" class="primary_input_field">
                </div>
                <div class="col-md-6 col-lg-3 mt-sm-2">
                    <label class="primary_input_label" for="filter_date_to">
                        {{ __('inventoryexit::messages.filter_date_to') }}
                    </label>
                    <input type="date" id="filter_date_to" class="primary_input_field">
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="row">
            <div class="col-lg-12">
                <div class="QA_section QA_section_heading_custom">
                    <div class="QA_table">
                        <table id="inventoryExitTable" class="table">
                            <thead>
                                <tr>
                                    <th class="text-center">{{ __('common.sl') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_date') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_type') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_products') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_cost_center') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_requested_by') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_approved_by') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_status') }}</th>
                                    <th class="text-center">{{ __('inventoryexit::messages.col_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    {{-- Contenedor para modal de detalle (cargado por AJAX) --}}
    <div id="exitDetailContainer"></div>

    {{-- Modales --}}
    @include('inventoryexit::partials._create_modal')
    @include('inventoryexit::partials._approve_modal')
    @include('inventoryexit::partials._confirm_modal')

</x-admin.section>
@endsection

@push('scripts')
<script>
(function ($) {
    'use strict';

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const ajaxUrl  = "{{ route('inventory_exit.get-data') }}";
    const storeUrl = "{{ route('inventory_exit.store') }}";

    // ---------------------------------------------------------------
    // DataTable
    // ---------------------------------------------------------------
    const dtColumns = [
        { data: 'DT_RowIndex',      name: 'id',             render: d => numbertrans(d),  className: 'text-center' },
        { data: 'exit_date_col',    name: 'exit_date',      searchable: false,             className: 'text-center' },
        { data: 'exit_reason_col',  name: 'exit_reason_id', searchable: false, orderable: false, className: 'text-center' },
        { data: 'products_col',     name: 'products',       searchable: false, orderable: false, className: 'text-center' },
        { data: 'cost_center_col',  name: 'location_type',  searchable: false, orderable: false, className: 'text-center' },
        { data: 'requested_by_col', name: 'requested_by',   searchable: false, orderable: false, className: 'text-center' },
        { data: 'approved_by_col',  name: 'approved_by',    searchable: false, orderable: false, className: 'text-center' },
        { data: 'status_col',       name: 'status',         searchable: false, orderable: false, className: 'text-center' },
        { data: 'actions',          name: 'actions',        searchable: false, orderable: false, className: 'text-center' },
    ];

    const dtButtons = [
        { extend: 'copyHtml5',  text: '<i class="fa fa-files-o"></i>',     titleAttr: 'Copy',  exportOptions: { columns: ':not(:last-child)' } },
        { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i>', titleAttr: 'Excel', exportOptions: { columns: ':not(:last-child)' }, title: "{{ __('inventoryexit::messages.title') }}" },
        { extend: 'csvHtml5',   text: '<i class="fa fa-file-text-o"></i>',  titleAttr: 'CSV',   exportOptions: { columns: ':not(:last-child)' } },
        { extend: 'pdfHtml5',   text: '<i class="fa fa-file-pdf-o"></i>',   titleAttr: 'PDF',   exportOptions: { columns: ':not(:last-child)' }, title: "{{ __('inventoryexit::messages.title') }}", pageSize: 'A4' },
        { extend: 'print',      text: '<i class="fa fa-print"></i>',        titleAttr: 'Print', exportOptions: { columns: ':not(:last-child)' } },
    ];

    $(document).ready(function () {

        const table = initGlobalDataTable('#inventoryExitTable', ajaxUrl, dtColumns, {
            buttons: dtButtons,
            ajax: {
                url: ajaxUrl,
                data: function (d) {
                    d.status_filter      = $('#filter_status').val();
                    d.cost_center_filter = $('#filter_cost_center').val();
                    d.date_from          = $('#filter_date_from').val();
                    d.date_to            = $('#filter_date_to').val();
                }
            }
        });

        // Filtros
        let filterDelay;
        $('#filter_status, #filter_cost_center, #filter_date_from, #filter_date_to').on('change keyup', function () {
            clearTimeout(filterDelay);
            filterDelay = setTimeout(() => table.ajax.reload(), 350);
        });

        // ---------------------------------------------------------------
        // Abrir modal de creación
        // ---------------------------------------------------------------
        $('#btnNewExit').on('click', function (e) {
            e.preventDefault();
            resetCreateModal();
            $('#createExitModal').modal('show');
        });

        // ---------------------------------------------------------------
        // Ver detalle
        // ---------------------------------------------------------------
        $(document).on('click', '.view_exit_detail', function () {
            const id  = $(this).data('id');
            const url = "{{ route('inventory_exit.detail', ['id' => ':id']) }}".replace(':id', id);
            $('#pre-loader').removeClass('d-none');

            $.get(url, function (html) {
                $('#exitDetailContainer').html(html);
                $('#exitDetailModal').modal('show');
            }).always(() => $('#pre-loader').addClass('d-none'));
        });

        // ---------------------------------------------------------------
        // Cambiar estado — abrir modal aprobación
        // ---------------------------------------------------------------
        $(document).on('click', '.change_exit_status', function () {
            const id = $(this).data('id');
            $('#approveExitForm').data('exit-id', id);
            $('#approve_exit_date').text(new Date().toLocaleString('es-CO'));
            $('#approveExitModal').modal('show');
        });

        // ---------------------------------------------------------------
        // Submit creación — muestra modal de confirmación primero
        // ---------------------------------------------------------------
        $('#createExitForm').on('submit', function (e) {
            e.preventDefault();

            if ($('#exitItemsBody tr[data-sku-id]').length === 0) {
                toastr.warning('{{ __('inventoryexit::validation.items_required') }}');
                return;
            }

            let hasError = false;

            $('#exitItemsBody tr[data-sku-id]').each(function () {
                const $row           = $(this);
                const availableStock = parseFloat($row.data('stock'));
                const requestedQty   = parseFloat($row.find('.qty-input').val()) || 0;

                if (requestedQty <= 0) {
                    $row.find('.qty-input').addClass('border-danger');
                    toastr.error('{{__('validation.quantity_must_be_greater_than_zero')}}');
                    hasError = true;
                    return false;
                }

                if (requestedQty > availableStock) {
                    $row.find('.qty-input').addClass('border-danger');
                    const errorMessageQtyExceeds = '{{ __('validation.quantity_less_than_available', ['requested' => ':requested', 'available' => ':available']) }}';
                    toastr.error(
                        errorMessageQtyExceeds
                            .replace(':requested', requestedQty)
                            .replace(':available', availableStock)
                    );
                    hasError = true;
                    return false;
                }
            });

            if (hasError) { return; }

            const ccName = $('#exit_cost_center option:selected').text();
            $('#confirmExitCcName').text(ccName);
            $('#confirmExitModal').modal('show');
        });

        // Confirmación final — registrado una sola vez
        $('#btnConfirmExitFinal').on('click', function () {
            $('#confirmExitModal').modal('hide');

            const formData = new FormData($('#createExitForm')[0]);
            $('#exitItemsBody tr[data-sku-id]').each(function (i) {
                formData.append('items[' + i + '][product_sku_id]', $(this).data('sku-id'));
                formData.append('items[' + i + '][lot_id]',         $(this).data('lot-id'));
                formData.append('items[' + i + '][qty_requested]',  $(this).find('.qty-input').val());
            });

            submitExitForm(formData, storeUrl, table);
        });

        // ---------------------------------------------------------------
        // Submit aprobación
        // ---------------------------------------------------------------
        $('#approveExitForm').on('submit', function (e) {
            e.preventDefault();
            const id  = $(this).data('exit-id');
            const url = "{{ route('inventory_exit.approve', ['id' => ':id']) }}".replace(':id', id);

            $('#pre-loader').removeClass('d-none');

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.success) {
                        $('#approveExitModal').modal('hide');
                        toastr.success(res.message);
                        table.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors ?? {};
                        toastr.error(Object.values(errors).flat().join(' | '));
                    } else {
                        toastr.error(xhr.responseJSON?.message ?? '{{ __('common.error_message') }}');
                    }
                },
                complete: () => $('#pre-loader').addClass('d-none'),
            });
        });

    });

    function submitExitForm(formData, url, table) {
        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    $('#createExitModal').modal('hide');
                    toastr.success(res.message);
                    table.ajax.reload();
                } else {
                    toastr.error(res.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors ?? {};
                    toastr.error(Object.values(errors).flat().join(' | '));
                } else {
                    toastr.error(xhr.responseJSON?.message ?? '{{ __('common.error_message') }}');
                }
            },
            complete: () => $('#pre-loader').addClass('d-none'),
        });
    }

    function resetCreateModal() {
        $('#createExitForm')[0].reset();
        $('#exitItemsBody').empty();
        $('#exitItemsTable').hide();
        $('#productSkuSelect').val('').find('option:not(:first)').remove().end()
            .prop('disabled', true).niceSelect('update');
    }

})(jQuery);
</script>
@endpush
