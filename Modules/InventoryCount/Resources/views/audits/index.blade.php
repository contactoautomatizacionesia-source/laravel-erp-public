@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-customer-list">
    <div class="row">
       
        <div class="col-12">
            <div class="box_header common_table_header ">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('inventorycount::menu.audits') }}</h3>
                </div>
            </div>
            
            <x-admin.table-container>
                <table id="auditTable" class="table table-hover display text-center">
                    <thead>
                        <tr>
                            <th>{{ __('common.sl') }}</th>
                            <th>{{ __('common.created_at') }}</th>
                            <th>{{ __('inventorycount::messages.count_code') }}</th>
                            <th>{{ __('inventorycount::messages.cost_center') }}</th>
                            <th>{{ __('inventorycount::messages.asesor') }}</th>
                            <th>{{ __('inventorycount::messages.auditor') }}</th>
                            <th>{{ __('inventorycount::messages.audit_status') }}</th>
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
    initGlobalDataTable('#auditTable', '{{ route("inventory_count.audits.data") }}', [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'created_at', defaultContent: '' },
        { data: 'count_code', defaultContent: '' },
        { data: 'cost_center', defaultContent: '' },
        { data: 'asesor', defaultContent: '' },
        { data: 'auditor_name', defaultContent: '' },
        { data: 'status_label', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false },
    ]);
});
</script>
@endpush
