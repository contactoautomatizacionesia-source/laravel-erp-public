@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-customer-list">
    <div class="row">
        <div class="col-md-12 mb-10">
           

            <div class="box_header_right">
                <div class="pos_tab_btn justify-content-end">
                    <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                        @if($canCreate)
                        <li class="nav-item">
                            <a class="nav-link action" href="{{ route('inventory_count.create') }}"><i class="ti-plus mr-2"></i>{{ __('inventorycount::messages.create_count') }}</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="box_header common_table_header ">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('inventorycount::menu.counts') }}</h3>
                </div>
            </div>
            @if(session('count_result'))
                @php $r = session('count_result'); @endphp
                <div class="alert alert-{{ $r['status'] === 'correct' ? 'success' : 'warning' }} alert-dismissible fade show">
                    {{ $r['message'] }}
                    <button type="button" class="close" data-dismiss="alert"><i class="ti-close"></i></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="ti-alert mr-1"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><i class="ti-close"></i></button>
                </div>
            @endif
            <x-admin.table-container>
                <table id="countTable" class="table table-hover display text-center">
                    <thead>
                        <tr>
                            <th>{{ __('common.sl') }}</th>
                            <th>{{ __('common.created_at') }}</th>
                            <th>{{ __('inventorycount::messages.count_code') }}</th>
                            <th>{{ __('inventorycount::messages.cost_center') }}</th>
                            <th>{{ __('inventorycount::messages.responsible') }}</th>
                            <th>{{ __('inventorycount::messages.attempts') }}</th>
                            <th>{{ __('inventorycount::messages.status') }}</th>
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
    const ajaxUrl = '{{ route("inventory_count.data") }}';

    initGlobalDataTable('#countTable', ajaxUrl, [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'created_at', defaultContent: '' },
        { data: 'count_code', defaultContent: '' },
        { data: 'cost_center', defaultContent: '' },
        { data: 'responsible', defaultContent: '' },
        { data: 'total_attempts', orderable: false, searchable: false, className: 'text-center' },
        { data: 'status_label', orderable: false, searchable: false },
        { data: 'audit_status_label', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false },
    ]);

});
</script>
@endpush
