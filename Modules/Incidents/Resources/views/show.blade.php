@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-incident-show">

    {{-- Header --}}
    <div class="row mb-15">
        <div class="col-12 d-md-flex align-items-start justify-content-between flex-wrap gap-2">
            <div class="mb-3">
                <div class="mb-0 box_header common_table_header">
                    <div class="main-title d-flex align-items-center mb-2">
                        <x-backEnd.back-button :text="false" />
                        <h3 class="mb-0">
                            {{ __('incidents::menu.incident') }}: {{ $incident->sequential_code }}
                        </h3>
                        {!! view('incidents::components.status_badge', ['status' => $incident->status])->render() !!}
                        
                    </div>
                </div>
                <small class="text-muted">{{ __('incidents::messages.created_at') }}: {{ $incident->created_at?->format('d/m/Y H:i') }}</small>
            </div>
            
            {!! view('incidents::components.type_badge', ['type' => $incident->incident_type])->render() !!}
        </div>
    </div>

    <div class="row">
        {{-- Columna izquierda: datos principales --}}
        <div class="col-md-7">

            {{-- Información del producto --}}
            <div class="form-card mb-15">
                <h3>{{ __('incidents::messages.product_info') }}</h3>
                <div class="">
                    <div class="row">
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.product') }}</span>
                            <span class="text-black"><strong>{{ $incident->product_name_snapshot }}</strong></span>
                        </div>
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.missing_units') }}</span>
                            <strong class="text-danger">{{ $incident->missing_units }} uds</strong>
                        </div>
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.public_price') }}</span>
                            <span class="text-black"><strong>$ {{ number_format($incident->public_price_snapshot, 2, ',', '.') }}</strong></span>
                        </div>
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.total_value') }}</span>
                            <strong class="text-danger">$ {{ number_format($incident->total_value, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Responsabilidad --}}
            <div class="form-card mb-15">
                <h3>{{ __('incidents::messages.responsibility') }}</h3>
                <div class="">
                    <div class="row">
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.responsible_branch') }}</span>
                            <span class="text-black"><strong>{{ $incident->responsibleBranch?->name ?? '—' }}</strong></span>
                        </div>
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.responsible_advisor') }}</span>
                            <span class="text-black"><strong>{{ $incident->responsibleUser?->name ?? '—' }}</strong></span>
                        </div>
                        @if($incident->incident_type === 'transfer')
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.origin_branch') }}</span>
                            <span class="text-black"><strong>{{ $incident->originBranch?->name ?? '—' }}</strong></span>
                        </div>
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.origin_advisor') }}</span>
                            <span class="text-black"><strong>{{ $incident->originUser?->name ?? '—' }}</strong></span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pronunciamiento (solo transferencias) --}}
            @if($incident->incident_type === 'transfer')
            <div class="form-card mb-15">
                <h3>{{ __('incidents::messages.statement_info') }}</h3>
                <div class="">
                    <div class="row">
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.statement_deadline') }}</span>
                            <span class="text-black"><strong>{{ $incident->statement_expires_at?->format('d/m/Y H:i') ?? '—' }}</strong></span>
                            @if($incident->statementDeadlineExpired() && $incident->isAwaitingStatement())
                                <span class="badge_2 ml-1">{{ __('incidents::messages.expired') }}</span>
                            @endif
                        </div>
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.statement_type') }}</span>
                            <span class="text-black"><strong>{{ $incident->statement_type ? __('incidents::messages.statement_'.$incident->statement_type) : '—' }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Resolución --}}
            @if($incident->resolved_at)
            <div class="form-card mb-15">
                <h3>{{ __('incidents::messages.resolution_info') }}</h3>
                <div class="">
                    <div class="row">
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.resolution_party') }}</span>
                            <span class="text-black"><strong>{{ $incident->resolution_party ? __('incidents::messages.party_'.$incident->resolution_party) : '—' }}</strong></span>
                        </div>
                        <div class="col-sm-6 mb-10">
                            <span class="text-muted d-block">{{ __('incidents::messages.resolved_at') }}</span>
                            <span class="text-black"><strong>{{ $incident->resolved_at?->format('d/m/Y H:i') }}</strong></span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted d-block">{{ __('incidents::messages.resolution_notes') }}</span>
                            <p class="mb-0">{{ $incident->resolution_notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            
        </div>

        {{-- Columna derecha: evidencias y log --}}
        <div class="col-md-5">

            {{-- Evidencias --}}
            <div class="form-card mb-15">
                <h3>{{ __('incidents::messages.evidences') }}</h3>
                
                <div class=" p-2">
                    @forelse($incident->evidences as $evidence)
                    <div class="d-flex align-items-start mb-10 p-2 border rounded">
                        @if($evidence->isImage())
                            <a href="{{ $evidence->file_url }}" target="_blank">
                                <img src="{{ $evidence->file_url }}" alt="{{ $evidence->file_name }}" style="width:48px;height:48px;object-fit:cover;" class="rounded mr-2">
                            </a>
                        @else
                            <a href="{{ $evidence->file_url }}" target="_blank" class="mr-2">
                                <i class="ti-file" style="font-size:2rem;"></i>
                            </a>
                        @endif
                        <div>
                            <div><strong>{{ $evidence->file_name }}</strong> <span class="badge badge-light">{{ __("incidents::messages.role_{$evidence->actor_role}") }}</span></div>
                            @if($evidence->notes)
                            <small class="text-muted">{{ $evidence->notes }}</small>
                            @endif
                            <div><small class="text-muted">{{ $evidence->uploadedBy?->name }} · {{ $evidence->created_at?->format('d/m/Y H:i') }}</small></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">{{ __('incidents::messages.no_evidences') }}</p>
                    @endforelse
                </div>
                <div class="text-center mt-5">
                    @if($incident->isOpen())
                    <button class="btn-toolkit btn-sm btn-primary btn-icon" data-toggle="modal" data-target="#evidenceModal">
                        <i class="ti-upload mr-1"></i>{{ __('incidents::messages.add_evidence') }}
                    </button>
                    @endif
                </div>
            </div>

            {{-- Log de auditoría --}}
            <div class="form-card">
                <h3>{{ __('incidents::messages.audit_log') }}</h3>
                <div class=" p-2" style="max-height:400px;overflow-y:auto;">
                    @forelse($incident->auditLogs as $log)
                    <div class="d-flex mb-10">
                        <div class="mr-2 text-center" style="width:36px;">
                            <span class="badge badge-light" style="font-size:0.65rem;">{{ $log->created_at?->format('d/m') }}</span>
                        </div>
                        <div class="flex-1">
                            <div><strong>{{ $log->actor_label_translated }}</strong>
                                @if($log->previous_status && $log->new_status)
                                <span class="text-muted small ml-1">
                                    {{ __("incidents::messages.status_{$log->previous_status}") }}
                                    → {{ __("incidents::messages.status_{$log->new_status}") }}
                                </span>
                                @endif
                            </div>
                            <div class="small">{{ $log->action_translated }}</div>
                            <div class="text-muted" style="font-size:0.7rem;">{{ $log->created_at?->format('H:i:s') }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">{{ __('incidents::messages.no_audit_logs') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            {{-- Botones de acción --}}
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-15">
                {{-- Pronunciamiento de origen (solo transferencias en estado awaiting_statement) --}}
                @if($incident->isAwaitingStatement() && $incident->incident_type === 'transfer' && !$incident->statementDeadlineExpired())
                <button class="btn-toolkit btn-primary btn-icon" data-toggle="modal" data-target="#statementModal">
                    <i class="ti-comment-alt mr-1"></i>{{ __('incidents::messages.submit_statement') }}
                </button>
                @endif

                {{-- Acciones del administrador --}}
                @if(in_array($incident->status, ['under_investigation', 'pending']))
                <button class="btn-toolkit btn-primary btn-icon" data-toggle="modal" data-target="#resolveModal" data-party="advisor">
                    <i class="ti-money mr-1"></i>{{ __('incidents::messages.btn_advisor_pays') }}
                </button>
                <button class="btn-toolkit btn-secondary btn-icon" data-toggle="modal" data-target="#resolveModal" data-party="organization">
                    <i class="ti-export mr-1"></i>{{ __('incidents::messages.btn_org_assumes') }}
                </button>
                <button class="btn-toolkit btn-secondary-outline" data-toggle="modal" data-target="#voidModal"
                    style="background-color:#efefef; color:#555; border-color:#efefef;">
                    <i class="ti-close mr-1"></i>{{ __('incidents::messages.btn_void') }}
                </button>
                @endif

                {{-- Vincular a cierre de caja --}}
                @if($incident->status === 'closed' && !$incident->cash_closing_id)
                <button class="btn-toolkit btn-secondary btn-icon" data-toggle="modal" data-target="#closingModal">
                    <i class="ti-link mr-1"></i>{{ __('incidents::messages.btn_link_closing') }}
                </button>
                @endif
            </div>
        </div>
    </div>
</x-admin.section>

{{-- Modales --}}
@include('incidents::components.statement_modal')
@include('incidents::components.resolve_modal')
@include('incidents::components.void_modal')
@include('incidents::components.evidence_modal')
@include('incidents::components.closing_modal')
@endsection

@push('scripts')
<script src="{{ asset('public/backend/js/file-input.js') }}" defer></script>
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const INCIDENT_ID = '{{ $incident->id }}';

// Modal de resolución: pre-cargar el party según el botón
$('[data-target="#resolveModal"]').on('click', function () {
    $('#resolve-party').val($(this).data('party'));
});

// Submit pronunciamiento
$('#form-statement').on('submit', function (e) {
    e.preventDefault();
    const $btn = $('#btn-statement-submit');
    $btn.prop('disabled', true);
    $('#btn-statement-text').addClass('d-none');
    $('#btn-statement-loader').removeClass('d-none');

    const fd = new FormData(this);
    $.ajax({
        url: '{{ route("incidents.statement", $incident->id) }}',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.success) { location.reload(); }
            else {
                toastr.error(res.message);
                $btn.prop('disabled', false);
                $('#btn-statement-text').removeClass('d-none');
                $('#btn-statement-loader').addClass('d-none');
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.values(errors).flat().forEach(m => toastr.error(m));
            } else {
                toastr.error(xhr.responseJSON?.message ?? '{{ __("incidents::messages.error_generic") }}');
            }
            $btn.prop('disabled', false);
            $('#btn-statement-text').removeClass('d-none');
            $('#btn-statement-loader').addClass('d-none');
        }
    });
});

// Submit resolución
$('#form-resolve').on('submit', function (e) {
    e.preventDefault();
    const $btn = $('#btn-resolve-submit');
    $btn.prop('disabled', true);
    $('#btn-resolve-text').addClass('d-none');
    $('#btn-resolve-loader').removeClass('d-none');
    $.ajax({
        url: '{{ route("incidents.resolve", $incident->id) }}',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            _token:           CSRF_TOKEN,
            resolution_party: $('#resolve-party').val(),
            resolution_notes: $('#resolve-notes').val(),
        }),
        success: function (res) {
            if (res.success) { location.reload(); }
            else {
                toastr.error(res.message);
                $btn.prop('disabled', false);
                $('#btn-resolve-text').removeClass('d-none');
                $('#btn-resolve-loader').addClass('d-none');
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.values(errors).flat().forEach(m => toastr.error(m));
            } else {
                toastr.error(xhr.responseJSON?.message ?? '{{ __("incidents::messages.error_generic") }}');
            }
            $btn.prop('disabled', false);
            $('#btn-resolve-text').removeClass('d-none');
            $('#btn-resolve-loader').addClass('d-none');
        }
    });
});

// Submit anulación
$('#form-void').on('submit', function (e) {
    e.preventDefault();
    const $btn = $('#btn-void-submit');
    $btn.prop('disabled', true);
    $('#btn-void-text').addClass('d-none');
    $('#btn-void-loader').removeClass('d-none');
    $.ajax({
        url: '{{ route("incidents.void", $incident->id) }}',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ _token: CSRF_TOKEN, reason: $('#void-reason').val() }),
        success: function (res) {
            if (res.success) { location.reload(); }
            else {
                toastr.error(res.message);
                $btn.prop('disabled', false);
                $('#btn-void-text').removeClass('d-none');
                $('#btn-void-loader').addClass('d-none');
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.values(errors).flat().forEach(m => toastr.error(m));
            } else {
                toastr.error(xhr.responseJSON?.message ?? '{{ __("incidents::messages.error_generic") }}');
            }
            $btn.prop('disabled', false);
            $('#btn-void-text').removeClass('d-none');
            $('#btn-void-loader').addClass('d-none');
        }
    });
});

// Upload evidencia
$('#form-evidence').on('submit', function (e) {
    e.preventDefault();
    const $btn = $('#btn-evidence-submit');
    $btn.prop('disabled', true);
    $('#btn-evidence-text').addClass('d-none');
    $('#btn-evidence-loader').removeClass('d-none');

    const fd = new FormData(this);
    fd.append('_token', CSRF_TOKEN);
    $.ajax({
        url: '{{ route("incidents.evidence.store", $incident->id) }}',
        type: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.success) { location.reload(); }
            else {
                toastr.error(res.message);
                $btn.prop('disabled', false);
                $('#btn-evidence-text').removeClass('d-none');
                $('#btn-evidence-loader').addClass('d-none');
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.values(errors).flat().forEach(m => toastr.error(m));
            } else {
                toastr.error(xhr.responseJSON?.message ?? '{{ __("incidents::messages.error_generic") }}');
            }
            $btn.prop('disabled', false);
            $('#btn-evidence-text').removeClass('d-none');
            $('#btn-evidence-loader').addClass('d-none');
        }
    });
});

// Vincular cierre
$('#form-closing').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
        url: '{{ route("incidents.link-closing", $incident->id) }}',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ _token: CSRF_TOKEN, cash_closing_id: $('#closing-id').val() }),
        success: function (res) {
            if (res.success) { location.reload(); }
            else { toastr.error(res.message); }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                Object.values(errors).flat().forEach(m => toastr.error(m));
            } else {
                toastr.error(xhr.responseJSON?.message ?? '{{ __("incidents::messages.error_generic") }}');
            }
        }
    });
});
</script>
@endpush
