@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-customer-list">
    <div class="row">
        <div class="col-12">
            <div class="box_header common_table_header">
                <div class="main-title d-flex align-items-center gap-2">
                    <x-backEnd.back-button :text="false" />
                    <h3 class="mb-0">
                        Ciclo <span class="text-muted">{{ $cycle->period_label }}</span>
                        &nbsp;@include('cycleclosure::cycles.partials.status_badge', ['cycle' => $cycle])
                    </h3>
                </div>
            </div>

            <x-admin.table-container>
                <div class="px-md-4 py-3">

                    {{-- ─── Encabezado del ciclo ─────────────────────────────── --}}
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <span class="text-muted small d-block">Período</span>
                            <p class="font-weight-bold mb-0">
                                {{ $cycle->period_start?->format('d/m/Y') }} — {{ $cycle->period_end?->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-md-2">
                            <span class="text-muted small d-block">Ejecutor</span>
                            <p class="font-weight-bold mb-0">{{ optional($cycle->executor)->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-2">
                            <span class="text-muted small d-block">Co-Aprobador</span>
                            <p class="font-weight-bold mb-0">{{ optional($cycle->coApprover)->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-2">
                            <span class="text-muted small d-block">Fecha Ejecución</span>
                            <p class="mb-0">
                                @if($cycle->executed_at)
                                    <span title="{{ $cycle->executed_at->format('d/m/Y H:i:s') }}">
                                        {{ $cycle->executed_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-2">
                            @if($cycle->status === 'closed' && permissionCheck('cycle_closure.acta'))
                            <a href="{{ route('cycle_closure.acta', $cycle->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="ti-file mr-1"></i> Descargar Acta
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- ─── Aprobar (si está pendiente y es el co-aprobador) ──── --}}
                    @if($cycle->status === 'pending_approval' && $cycle->co_approver_id === auth()->id() && permissionCheck('cycle_closure.approve'))
                    <div class="alert alert-warning d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <i class="ti-alert mr-2"></i>
                            <strong>Este cierre está pendiente de tu aprobación.</strong>
                            Al aprobar, se ejecutará la consolidación y se generará el acta.
                        </div>
                        <button type="button" class="btn-toolkit btn-primary ml-3" id="btnApproveCycle">
                            <i class="ti-check mr-1"></i> Firmar y Aprobar
                        </button>
                    </div>
                    @endif

                    {{-- ─── Bitácora / Trazabilidad ──────────────────────────── --}}
                    <h6 class="font-weight-bold text-uppercase mb-3 border-bottom pb-2">
                        <i class="ti-list mr-1"></i> Trazabilidad del Proceso
                    </h6>

                    <div class="table-responsive ign-scrollbar">
                        <table id="logsTable" class="table dataTable table-hover display text-center">
                            <thead>
                                <tr>
                                    <th>{{ __('common.sl') }}</th>
                                    <th>Fase</th>
                                    <th>Nivel</th>
                                    <th>Mensaje</th>
                                    <th>Usuario</th>
                                    <th>{{ __('common.date') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </x-admin.table-container>
        </div>
    </div>
</x-admin.section>

{{-- Modal aprobar ciclo --}}
@if($cycle->status === 'pending_approval' && $cycle->co_approver_id === auth()->id())
<dialog id="approveCycleModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger-toolkit">
                <h5 class="modal-title"><i class="ti-check mr-1"></i> Aprobar Cierre de Ciclo</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <p>Al confirmar:</p>
                <ul>
                    <li>Se ejecutará la consolidación de datos (Fase 2)</li>
                    <li>Se generará el Acta de Cierre en PDF (Fase 3)</li>
                    <li>Se bloqueará el período para modificaciones</li>
                </ul>
                <p class="text-danger mb-0"><strong>Esta acción es irreversible.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <form action="{{ route('cycle_closure.coapprover-approve', $cycle->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="btn-toolkit btn-primary">
                        <i class="ti-check mr-1"></i> Confirmar y Aprobar
                    </button>
                </form>
            </div>
        </div>
    </div>
</dialog>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const logsUrl = '{{ route("cycle_closure.logs-data", $cycle->id) }}';

    initGlobalDataTable('#logsTable', logsUrl, [
        { data: 'DT_RowIndex',     orderable: false, searchable: false },
        { data: 'phase_label',     defaultContent: '' },
        { data: 'level_badge',     orderable: false, searchable: false },
        { data: 'message',         defaultContent: '', className: 'text-left' },
        { data: 'user_name',       defaultContent: '' },
        { data: 'created_at_col',  orderable: false, searchable: false },
    ]);

    $('#btnApproveCycle').on('click', function () {
        $('#approveCycleModal').modal('show');
    });

});
</script>
@endpush
