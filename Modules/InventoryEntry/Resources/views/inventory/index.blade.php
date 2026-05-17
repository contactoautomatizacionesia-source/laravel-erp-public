@extends('backEnd.master')
@section('page-title', __('inventoryentry::inventory.menu_inventory_entry'))

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row justify-content-center">

            {{-- Tabs + Boton accion --}}
            <div class="col-12 mb-3">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a href="{{ route('inventory_entry.create') }}" class="nav-link action">
                                    <i class="ti-plus mr-2"></i> {{ __('inventoryentry::inventory.new_entry') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active show" href="#active_inventory_entries" role="tab" data-toggle="tab" aria-selected="true">
                                    {{ __('inventoryentry::inventory.tab_active') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#modified_inventory_entries" role="tab" data-toggle="tab" aria-selected="false">
                                    {{ __('inventoryentry::inventory.tab_modified') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#deleted_inventory_entries" role="tab" data-toggle="tab" aria-selected="false">
                                    {{ __('inventoryentry::inventory.tab_deleted') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Titulo --}}
            <div class="col-lg-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">
                            {{ __('inventoryentry::inventory.inventory_entry_management') }}
                        </h3>
                    </div>
                </div>

                <div class="form-card">
                    <h3>{{__('common.filters')}}</h3>
                    {{-- Filtros --}}
                    <div class="row">
                        <div class="col-md-3">
                            <label for="filter_product" class="primary_input_label">{{ __('inventoryentry::inventory.col_product') }}</label>
                            <input type="text" id="filter_product" class="primary_input_field"
                                placeholder="{{ __('inventoryentry::inventory.filter_product') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_lot" class="primary_input_label">{{ __('inventoryentry::inventory.col_lot') }}</label>
                            <input type="text" id="filter_lot" class="primary_input_field"
                                placeholder="{{ __('inventoryentry::inventory.filter_lot') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_status" class="primary_input_label">{{ __('inventoryentry::inventory.col_status') }}</label>
                            <select id="filter_status" class="primary_input_select nice-select-regular">
                                <option value="">{{ __('inventoryentry::inventory.filter_status') }}</option>
                                <option value="vigente">{{ __('inventoryentry::inventory.status_valid') }}</option>
                                <option value="por_vencer">{{ __('inventoryentry::inventory.status_expiring') }}</option>
                                <option value="vencido">{{ __('inventoryentry::inventory.status_expired') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter_exp_from" class="primary_input_label">{{ __('inventoryentry::inventory.filter_date_from') }}</label>
                            <input type="date" id="filter_exp_from" class="primary_input_field">
                        </div>
                        <div class="col-md-2">
                            <label for="filter_exp_to" class="primary_input_label">{{ __('inventoryentry::inventory.filter_date_to') }}</label>
                            <input type="date" id="filter_exp_to" class="primary_input_field">
                        </div>
                        <div class="col-md-1 d-flex align-items-end pb-1">
                            <button id="btn_clear_filters" class="btn-toolkit btn-secondary-outline btn-sm" type="button">
                                <i class="ti-close"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tablas --}}
                <div class="tab-content">
                    {{-- Activos --}}
                    <div role="tabpanel" class="tab-pane fade active show" id="active_inventory_entries">
                        <div class="QA_section QA_list_admin_dashboard_inventory">
                            <div class="QA_table">
                                <div class="table-responsive ign-scrollbar">
                                    <table class="table" id="inventoryEntryTableActive">
                                        <thead>
                                            <tr>
                                                <th>{{ __('common.sl') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_product') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_sku') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_lot') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_manufacture') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_expiration') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_quantity') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_status') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_created_by') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_created_at') }}</th>
                                                <th>{{ __('common.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modificados --}}
                    <div role="tabpanel" class="tab-pane fade" id="modified_inventory_entries">
                        <div class="QA_section QA_list_admin_dashboard_inventory">
                            <div class="QA_table">
                                <div class="table-responsive ign-scrollbar">
                                    <table class="table" id="inventoryEntryTableModified">
                                        <thead>
                                            <tr>
                                                <th>{{ __('common.sl') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_product') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_sku') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_lot') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_manufacture') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_expiration') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_quantity') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_status') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_created_by') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_created_at') }}</th>
                                                <th>{{ __('common.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Eliminados --}}
                    <div role="tabpanel" class="tab-pane fade" id="deleted_inventory_entries">
                        <div class="QA_section QA_list_admin_dashboard_inventory">
                            <div class="QA_table">
                                <div class="table-responsive ign-scrollbar">
                                    <table class="table" id="inventoryEntryTableDeleted">
                                        <thead>
                                            <tr>
                                                <th>{{ __('common.sl') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_product') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_sku') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_lot') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_quantity') }}</th>
                                                <th>{{ __('inventoryentry::inventory.col_created_at') }}</th>
                                                <th>{{ __('inventoryentry::inventory.audit_responsible') }}</th>
                                                <th>{{ __('inventoryentry::inventory.audit_notes') }}</th>
                                                <th>{{ __('inventoryentry::inventory.audit_date_long') }}</th>
                                                <th>{{ __('inventoryentry::inventory.audit_ip') }}</th>
                                                <th>{{ __('inventoryentry::inventory.audit_agent') }}</th>
                                                <th>{{ __('common.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<div id="entryDetailContainer"></div>

{{-- Modal Editar --}}
<div class="modal fade" id="entryEditModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti-pencil mr-2"></i> {{ __('inventoryentry::inventory.edit_entry') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_entry_id">
                <div class="row">
                    <div class="col-md-6">
                        <label class="primary_input_label" for="edit_quantity">{{ __('inventoryentry::inventory.quantity') }} <span>*</span></label>
                        <input type="number" id="edit_quantity" class="primary_input_field" min="1" step="1">
                    </div>
                    <div class="col-md-6">
                        <label class="primary_input_label">{{ __('inventoryentry::inventory.unit_cost') }}</label>
                        <input type="number" id="edit_unit_cost" class="primary_input_field" min="0" step="0.01">
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="primary_input_label">{{ __('inventoryentry::inventory.manufacture_date') }}</label>
                        <input type="date" id="edit_manufacture_date" class="primary_input_field">
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="primary_input_label">{{ __('inventoryentry::inventory.expiration_date') }}</label>
                        <input type="date" id="edit_expiration_date" class="primary_input_field">
                    </div>
                    <div class="col-md-6 mt-3">
                        <label class="primary_input_label">{{ __('inventoryentry::inventory.supplier') }}</label>
                        <input type="text" id="edit_supplier" class="primary_input_field">
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="primary_input_label">{{ __('inventoryentry::inventory.notes') }}</label>
                        <textarea id="edit_notes" class="primary_textarea" rows="3"></textarea>
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="primary_input_label" for="edit_audit_notes">{{ __('inventoryentry::inventory.audit_notes') }} <span>*</span></label>
                        <textarea id="edit_audit_notes" class="primary_textarea" rows="3" placeholder="{{ __('inventoryentry::inventory.audit_notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="mt-3 alert alert-warning d-none" id="edit_warning_box"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn_update_entry" class="btn-toolkit btn-primary">
                    <i class="ti-save mr-1"></i> {{ __('inventoryentry::inventory.save_changes') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Eliminar --}}
<div class="modal fade" id="entryDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti-trash mr-2"></i> {{ __('inventoryentry::inventory.delete_entry') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="delete_entry_id">
                <p class="mb-2">{{ __('inventoryentry::inventory.delete_confirm_text') }}</p>
                <label class="primary_input_label" for="delete_audit_notes">{{ __('inventoryentry::inventory.audit_notes') }} <span>*</span></label>
                <textarea id="delete_audit_notes" class="primary_textarea" rows="3" placeholder="{{ __('inventoryentry::inventory.audit_notes_placeholder') }}"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="btn_delete_entry" class="btn-toolkit btn-primary">
                    <i class="ti-trash mr-1"></i> {{ __('common.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    if ($.fn.niceSelect) {
        $('.nice-select-regular').niceSelect();
    }

    let ajaxUrl = "{{ route('inventory_entry.get-data') }}";

    let columns = [
        { data: 'DT_RowIndex',     name: 'id',              render: d => numbertrans(d) },
        { data: 'product_name',    name: 'product_name',    searchable: false },
        { data: 'sku_variant',     name: 'sku_variant',     searchable: false, orderable: false },
        { data: 'lot_number',      name: 'lot.lot_number',  searchable: false },
        { data: 'manufacture_date',name: 'manufacture_date',searchable: false, orderable: false },
        { data: 'expiration_date', name: 'expiration_date', searchable: false, orderable: false },
        { data: 'quantity',        name: 'quantity',        render: d => numbertrans(parseInt(d)) },
        { data: 'status_badge',    name: 'status_badge',    searchable: false, orderable: false },
        { data: 'created_by_name', name: 'created_by_name', searchable: false, orderable: false },
        { data: 'entry_date',      name: 'created_at',      searchable: false },
        { data: 'actions',         name: 'actions',         orderable: false, searchable: false },
    ];

    let deletedColumns = [
        { data: 'DT_RowIndex',     name: 'id',              render: d => numbertrans(d) },
        { data: 'product_name',    name: 'product_name',    searchable: false },
        { data: 'sku_variant',     name: 'sku_variant',     searchable: false, orderable: false },
        { data: 'lot_number',      name: 'lot.lot_number',  searchable: false },
        { data: 'quantity',        name: 'quantity',        render: d => numbertrans(parseInt(d)) },
        { data: 'entry_date',      name: 'created_at',      searchable: false },
        { data: 'deleted_by',      name: 'deleted_by',      searchable: false, orderable: false },
        { data: 'deleted_notes',   name: 'deleted_notes',   searchable: false, orderable: false },
        { data: 'deleted_date_long', name: 'deleted_date_long', searchable: false, orderable: false },
        { data: 'deleted_ip',      name: 'deleted_ip',      searchable: false, orderable: false },
        { data: 'deleted_agent',   name: 'deleted_agent',   searchable: false, orderable: false },
        { data: 'actions',         name: 'actions',         orderable: false, searchable: false },
    ];

    let activeTable = initGlobalDataTable('#inventoryEntryTableActive', ajaxUrl + '?table=active', columns, {
        stateSave: false,
        ajax: {
            url: ajaxUrl + '?table=active',
            data: function (d) {
                d.product_filter = $('#filter_product').val();
                d.lot_filter     = $('#filter_lot').val();
                d.status_filter  = $('#filter_status').val();
                d.exp_from       = $('#filter_exp_from').val();
                d.exp_to         = $('#filter_exp_to').val();
            }
        }
    });

    let modifiedTable = initGlobalDataTable('#inventoryEntryTableModified', ajaxUrl + '?table=modified', columns, {
        stateSave: false,
        ajax: {
            url: ajaxUrl + '?table=modified',
            data: function (d) {
                d.product_filter = $('#filter_product').val();
                d.lot_filter     = $('#filter_lot').val();
                d.status_filter  = $('#filter_status').val();
                d.exp_from       = $('#filter_exp_from').val();
                d.exp_to         = $('#filter_exp_to').val();
            }
        }
    });

    let deletedTable = initGlobalDataTable('#inventoryEntryTableDeleted', ajaxUrl + '?table=deleted', deletedColumns, {
        stateSave: false,
        ajax: {
            url: ajaxUrl + '?table=deleted',
            data: function (d) {
                d.product_filter = $('#filter_product').val();
                d.lot_filter     = $('#filter_lot').val();
                d.status_filter  = $('#filter_status').val();
                d.exp_from       = $('#filter_exp_from').val();
                d.exp_to         = $('#filter_exp_to').val();
            }
        }
    });

    let currentTable = 'active';

    function reloadCurrentTable() {
        if (currentTable === 'active') {
            activeTable.ajax.reload();
        } else if (currentTable === 'modified') {
            modifiedTable.ajax.reload();
        } else {
            deletedTable.ajax.reload();
        }
    }

    // Aplicar filtros al escribir / cambiar
    let filterDelay;
    $('#filter_product, #filter_lot').on('keyup', function () {
        clearTimeout(filterDelay);
        filterDelay = setTimeout(function () { reloadCurrentTable(); }, 400);
    });
    $('#filter_status, #filter_exp_from, #filter_exp_to').on('change', function () {
        reloadCurrentTable();
    });

    // Limpiar filtros
    $('#btn_clear_filters').on('click', function () {
        $('#filter_product, #filter_lot, #filter_exp_from, #filter_exp_to').val('');
        $('#filter_status').val('');
        reloadCurrentTable();
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        if (target === '#active_inventory_entries') {
            currentTable = 'active';
            activeTable.ajax.reload(null, false);
        } else if (target === '#modified_inventory_entries') {
            currentTable = 'modified';
            modifiedTable.ajax.reload(null, false);
        } else {
            currentTable = 'deleted';
            deletedTable.ajax.reload(null, false);
        }
    });

    // Ver detalle
    $(document).on('click', '.view_entry_detail', function () {
        let id  = $(this).data('id');
        let url = "{{ route('inventory_entry.detail', ['id' => ':id']) }}".replace(':id', id);
        $('#pre-loader').removeClass('d-none');

        $.get(url, function (html) {
            $('#entryDetailContainer').html(html);
            $('#entryDetailModal').modal('show');
            $('#pre-loader').addClass('d-none');
        }).fail(function () {
            $('#pre-loader').addClass('d-none');
            toastr.error("{{ __('common.something_wrong') }}");
        });
    });

    // Editar
    $(document).on('click', '.edit_entry', function () {
        let id = $(this).data('id');
        let url = "{{ route('inventory_entry.edit', ['id' => ':id']) }}".replace(':id', id);
        $('#pre-loader').removeClass('d-none');

        $.get(url, function (response) {
            if (!response.can_edit) {
                toastr.error(response.message || "{{ __('inventoryentry::inventory.cannot_edit') }}");
                $('#pre-loader').addClass('d-none');
                return;
            }

            $('#edit_entry_id').val(response.entry.id);
            $('#edit_quantity').val(parseInt(response.entry.quantity));
            let rawCost = parseFloat(response.entry.unit_cost);
            $('#edit_unit_cost').val(rawCost % 1 === 0 ? rawCost.toFixed(0) : rawCost);
            $('#edit_supplier').val(response.entry.supplier);
            $('#edit_notes').val(response.entry.notes);
            $('#edit_manufacture_date').val(response.entry.manufacture_date);
            $('#edit_expiration_date').val(response.entry.expiration_date);
            $('#edit_audit_notes').val('');
            $('#edit_warning_box').addClass('d-none').text('');

            $('#entryEditModal').modal('show');
            $('#pre-loader').addClass('d-none');
        }).fail(function () {
            $('#pre-loader').addClass('d-none');
            toastr.error("{{ __('common.something_wrong') }}");
        });
    });

    $('#btn_update_entry').on('click', function () {
        let id = $('#edit_entry_id').val();
        let url = "{{ route('inventory_entry.update', ['id' => ':id']) }}".replace(':id', id);
        let notes = $('#edit_audit_notes').val().trim();

        if (!notes) {
            toastr.warning("{{ __('inventoryentry::inventory.audit_note_required') }}");
            return;
        }

        $('#pre-loader').removeClass('d-none');

        $.post(url, {
            _token: "{{ csrf_token() }}",
            quantity: $('#edit_quantity').val(),
            unit_cost: $('#edit_unit_cost').val(),
            supplier: $('#edit_supplier').val(),
            notes: $('#edit_notes').val(),
            manufacture_date: $('#edit_manufacture_date').val(),
            expiration_date: $('#edit_expiration_date').val(),
            audit_notes: notes
        }, function (response) {
            if (response.success) {
                toastr.success(response.message);
                $('#entryEditModal').modal('hide');
                activeTable.ajax.reload(null, false);
                modifiedTable.ajax.reload(null, false);
                deletedTable.ajax.reload(null, false);
            } else {
                toastr.error(response.message || "{{ __('common.error') }}");
            }
        }).fail(function (xhr) {
            let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || "{{ __('common.error_message') }}";
            toastr.error(msg);
        }).always(function () {
            $('#pre-loader').addClass('d-none');
        });
    });

    // Eliminar
    $(document).on('click', '.delete_entry', function () {
        let id = $(this).data('id');
        $('#delete_entry_id').val(id);
        $('#delete_audit_notes').val('');
        $('#entryDeleteModal').modal('show');
    });

    $('#btn_delete_entry').on('click', function () {
        let id = $('#delete_entry_id').val();
        let url = "{{ route('inventory_entry.destroy', ['id' => ':id']) }}".replace(':id', id);
        let notes = $('#delete_audit_notes').val().trim();

        if (!notes) {
            toastr.warning("{{ __('inventoryentry::inventory.audit_note_required') }}");
            return;
        }

        $('#pre-loader').removeClass('d-none');

        $.post(url, {
            _token: "{{ csrf_token() }}",
            audit_notes: notes
        }, function (response) {
            if (response.success) {
                toastr.success(response.message);
                $('#entryDeleteModal').modal('hide');
                activeTable.ajax.reload(null, false);
                modifiedTable.ajax.reload(null, false);
                deletedTable.ajax.reload(null, false);
            } else {
                toastr.error(response.message || "{{ __('common.error') }}");
            }
        }).fail(function (xhr) {
            let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || "{{ __('common.error_message') }}";
            toastr.error(msg);
        }).always(function () {
            $('#pre-loader').addClass('d-none');
        });
    });

    $(window).on('load pageshow', function () {
        $('#pre-loader').addClass('d-none');
    });
});
</script>
@endpush
