@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-incidents-list">

    {{-- Tarjetas de métricas --}}
    <div class="row mb-3" id="incident-metrics">
        <x-backEnd.stat-card
            id="metric-pending"
            color="warning"
            icon="ti-time"
            :label="__('incidents::messages.status_pending')"
            
        />
        <x-backEnd.stat-card
            id="metric-awaiting"
            color="danger"
            icon="ti-alert"
            :label="__('incidents::messages.status_awaiting')"
        />
        <x-backEnd.stat-card
            id="metric-investigating"
            color="purple"
            icon="ti-search"
            :label="__('incidents::messages.status_investigating')"
        />
        <x-backEnd.stat-card
            id="metric-total-value"
            color="info"
            icon="ti-money"
            :label="__('incidents::messages.total_open_value')"
        />
    </div>

    <div class="row">
        <div class="col-12">
            <div class="box_header common_table_header">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('incidents::menu.incidents') }}</h3>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="form-card">
                <h3>{{ __('common.filters') }}</h3>
            
                <div class="row mb-15 align-items-end">
                    <div class="col-xl-2 col-lg-3 col-md-4 mb-2">
                        <select id="filter-status" class="primary_input_select">
                            <option value="">{{ __('incidents::messages.all_statuses') }}</option>
                            <option value="pending">{{ __('incidents::messages.status_pending') }}</option>
                            <option value="awaiting_statement">{{ __('incidents::messages.status_awaiting') }}</option>
                            <option value="under_investigation">{{ __('incidents::messages.status_investigating') }}</option>
                            <option value="closed">{{ __('incidents::messages.status_closed') }}</option>
                            <option value="voided">{{ __('incidents::messages.status_voided') }}</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 mb-2">
                        <select id="filter-type" class="primary_input_select">
                            <option value="">{{ __('incidents::messages.all_types') }}</option>
                            <option value="transfer">{{ __('incidents::messages.type_transfer') }}</option>
                            <option value="inventory_count">{{ __('incidents::messages.type_inventory_count') }}</option>
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 mb-2">
                        <input type="date" id="filter-date-from" class="primary_input_field">
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4 mb-2">
                        <input type="date" id="filter-date-to" class="primary_input_field">
                    </div>
                    <div class="col-md-4  mb-2">
                        <button id="btn-apply-filters" class="btn-toolkit  btn-primary">
                            <i class="ti-filter mr-1"></i>{{ __('incidents::messages.apply_filters') }}
                        </button>
                        <button id="btn-clear-filters" class="btn-toolkit  btn-secondary ml-1">
                            <i class="ti-close"></i>
                        </button>
                    </div>
                </div>
            </div>

            <x-admin.table-container>
                <table id="incidentsTable" class="table table-hover display text-center">
                    <thead>
                        <tr>
                            <th>{{ __('common.sl') }}</th>
                            <th>{{ __('incidents::messages.code') }}</th>
                            <th>{{ __('incidents::messages.type') }}</th>
                            <th>{{ __('incidents::messages.status') }}</th>
                            <th>{{ __('incidents::messages.product') }}</th>
                            <th>{{ __('incidents::messages.branch') }}</th>
                            <th>{{ __('incidents::messages.advisor') }}</th>
                            <th>{{ __('incidents::messages.missing_units') }}</th>
                            <th>{{ __('incidents::messages.total_value') }}</th>
                            <th>{{ __('common.created_at') }}</th>
                            <th>{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                </table>
            </x-admin.table-container>
        </div>
    </div>
</x-admin.section>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    // Cargar métricas
    $.get('{{ route("incidents.metrics") }}', function (data) {
        $('#metric-pending').text(data.pending);
        $('#metric-awaiting').text(data.awaiting_statement);
        $('#metric-investigating').text(data.under_investigation);
        $('#metric-total-value').text('$ ' + parseFloat(data.total_pending_value).toLocaleString('es-CO', {minimumFractionDigits: 2}));
    });

    const ajaxUrl = '{{ route("incidents.get-data") }}';

    const table = initGlobalDataTable('#incidentsTable', ajaxUrl, [
        { data: 'DT_RowIndex',           orderable: false, searchable: false },
        { data: 'sequential_code',       defaultContent: '' },
        { data: 'type_badge',            orderable: false, searchable: false },
        { data: 'status_badge',          orderable: false, searchable: false },
        { data: 'product_name_snapshot', defaultContent: '' },
        { data: 'branch',                orderable: false, searchable: false },
        { data: 'advisor',               orderable: false, searchable: false },
        { data: 'missing_units',         defaultContent: '' },
        { data: 'total_value_fmt',       orderable: false, searchable: false },
        { data: 'created_at_fmt',        defaultContent: '' },
        { data: 'action',                orderable: false, searchable: false },
    ], {
        ajax: {
            url: ajaxUrl,
            data: function (d) {
                d.status        = $('#filter-status').val();
                d.incident_type = $('#filter-type').val();
                d.date_from     = $('#filter-date-from').val();
                d.date_to       = $('#filter-date-to').val();
            }
        }
    });

    $('#btn-apply-filters').on('click', function () {
        table.ajax.reload();
    });

    $('#btn-clear-filters').on('click', function () {
        $('#filter-status, #filter-type').val('');
        $('#filter-date-from, #filter-date-to').val('');
        table.ajax.reload();
    });
});
</script>
@endpush
