@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-customer-list">
    <div class="row">

        {{-- Título --}}
        <div class="col-12">
            <div class="box_header common_table_header">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('cycleclosure::menu.closures') }}</h3>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="col-12">
            <x-admin.table-container>
                <div class="table-responsive ign-scrollbar">
                    <table id="cyclesTable" class="table dataTable table-hover display text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('common.sl') }}</th>
                                <th>Periodo</th>
                                <th>Fecha Ejecución</th>
                                <th>Ejecutor</th>
                                <th>Co-Aprobador</th>
                                <th>Estado</th>
                                <th>{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </x-admin.table-container>
        </div>

    </div>
</x-admin.section>

{{-- Modal cambiar estado --}}
<dialog id="changeStatusModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <form id="changeStatusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="primary_input_label" for="newStatus">Nuevo estado</label>
                        <select name="status" id="newStatus" class="primary_input_select" required>
                            <option value="pending_approval">{{ __('cycleclosure::messages.status_pending_approval') }}</option>
                            <option value="cancelled">{{ __('cycleclosure::messages.status_cancelled') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="btn-toolkit btn-primary">Cambiar</button>
                </div>
            </form>
        </div>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const ajaxUrl = '{{ route("cycle_closure.data") }}';

    initGlobalDataTable('#cyclesTable', ajaxUrl, [
        { data: 'DT_RowIndex',       orderable: false, searchable: false },
        { data: 'period',            defaultContent: '' },
        { data: 'executed_at_col',   orderable: false, searchable: false },
        { data: 'executor_name',     defaultContent: '' },
        { data: 'co_approver_name',  orderable: false, searchable: false },
        { data: 'status_badge',      orderable: false, searchable: false },
        { data: 'actions',           orderable: false, searchable: false },
    ]);

    // ── Cambiar estado ─────────────────────────────────────────────────────
    $(document).on('click', '.js-change-status', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#changeStatusForm').attr('action', '/cycle-closure/' + id + '/status');
        $('#changeStatusModal').modal('show');
    });

    // ── Aprobar ciclo pendiente ────────────────────────────────────────────
    $(document).on('click', '.js-approve-cycle', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        window.location.href = '/cycle-closure/' + id;
    });

});
</script>
@endpush
