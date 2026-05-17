@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">

        {{-- Botón de acción + estado --}}
        @if($session)
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right d-flex align-items-center justify-content-end gap-2">

                    @if($box->type === 'PRINCIPAL')
                        @if($allChildrenClosed && $session->status === 'OPEN')
                            <button id="btn-submit-to-vault" class="btn-toolkit btn-primary"
                                    data-box-id="{{ $box->id }}"
                                    data-box-name="{{ $box->name }}">
                                <i class="ti-upload mr-1"></i>{{ __('cashmanager::cash_manager.submit_to_parent') }}
                            </button>
                        @elseif($session->status === 'OPEN')
                            <button class="btn-toolkit btn-secondary-outline" disabled
                                    title="{{ __('cashmanager::cash_manager.submit_pending_children') }}">
                                <i class="ti-time mr-1"></i>{{ __('cashmanager::cash_manager.submit_to_parent') }}
                            </button>
                        @endif
                    @endif

                    <span class="badge_{{ $session->status === 'OPEN' ? '1' : '3' }} px-3 py-2" style="font-size:13px;">
                        <i class="ti-reload mr-1"></i>
                        {{ __('cashmanager::cash_manager.session_status_' . strtolower($session->status)) }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        {{-- Título --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex align-items-center">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">
                            {{ $box->type === 'VAULT'
                                ? __('cashmanager::cash_manager.review_title_vault')
                                : __('cashmanager::cash_manager.review_title_principal') }}
                        </h3>
                        <span class="badge_5 ml-2" style="font-size:12px;">{{ $box->code }} — {{ $box->name }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado: PENDING_RECEIPT (ya enviado, esperando confirmación de nivel superior) --}}
        @if($session?->status === 'PENDING_RECEIPT')
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-warning text-center py-4">
                    <i class="ti-time mr-2" style="font-size:1.4rem;"></i>
                    <strong>{{ __('cashmanager::cash_manager.review_submitted_waiting') }}</strong>
                    <p class="mb-0 mt-1 text-muted">{{ __('cashmanager::cash_manager.review_submitted_hint') }}</p>
                </div>
            </div>
        </div>
        @endif

        {{-- ══ VISTA PRINCIPAL ══════════════════════════════════════════════════════ --}}
        @if($box->type === 'PRINCIPAL')

            {{-- Cierres pendientes de confirmación --}}
            @if($pendingSessions->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="text-black mb-3">
                        <i class="ti-alert mr-1 text-warning"></i>
                        {{ __('cashmanager::cash_manager.review_pending_count', ['count' => $pendingSessions->count()]) }}
                    </h5>
                    @foreach($pendingSessions as $cs)
                        @include('cashmanager::operations._auxiliary_session_card', [
                            'cs'          => $cs,
                            'showConfirm' => $session?->status === 'OPEN',
                        ])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Cierres ya confirmados (historial del turno) --}}
            @if($receivedTransfers->isNotEmpty())
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="text-muted mb-3">
                        <i class="ti-check mr-1"></i>
                        {{ __('cashmanager::cash_manager.review_already_confirmed') }}
                    </h5>
                    @foreach($receivedTransfers as $cs)
                        @include('cashmanager::operations._auxiliary_session_card', [
                            'cs'          => $cs,
                            'showConfirm' => false,
                        ])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Sin nada --}}
            @if($pendingSessions->isEmpty() && $receivedTransfers->isEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <div class="white_box p-5 text-center">
                        <i class="ti-check-box text-muted mb-3" style="font-size:3.5rem; display:block;"></i>
                        <h5 class="text-muted">{{ __('cashmanager::cash_manager.review_no_pending') }}</h5>
                        <p class="text-muted small mb-0">{{ __('cashmanager::cash_manager.review_no_pending_hint') }}</p>
                    </div>
                </div>
            </div>
            @endif

        {{-- ══ VISTA VAULT ══════════════════════════════════════════════════════════ --}}
        @else

            @if($pendingSessions->isEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <div class="white_box p-5 text-center">
                        <i class="ti-check-box text-muted mb-3" style="font-size:3.5rem; display:block;"></i>
                        <h5 class="text-muted">{{ __('cashmanager::cash_manager.review_no_pending') }}</h5>
                        <p class="text-muted small mb-0">{{ __('cashmanager::cash_manager.review_no_pending_hint') }}</p>
                    </div>
                </div>
            </div>
            @else
            <p class="text-muted mt-3 mb-3">
                <i class="ti-info-alt mr-1"></i>
                {{ __('cashmanager::cash_manager.review_pending_count', ['count' => $pendingSessions->count()]) }}
            </p>

            @foreach($pendingSessions as $principal)
            <div class="white_box p-4 mb-4 shadow-sm">

                {{-- Cabecera PRINCIPAL --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <strong class="text-black" style="font-size:16px;">{{ $principal['box_name'] }}</strong>
                        <span class="text-muted ml-2">{{ $principal['box_code'] }}</span>
                        <div class="text-muted small mt-1">
                            <i class="ti-user mr-1"></i>{{ $principal['operator_name'] }}
                            &nbsp;·&nbsp;
                            <i class="ti-time mr-1"></i>
                            <span title="{{ $principal['closed_at']?->format('Y-m-d H:i:s') }}">
                                {{ __('cashmanager::cash_manager.closed_at') }}: {{ $principal['closed_at']?->diffForHumans() ?? '—' }}
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($principal['has_incidents'])
                            <span class="badge_3"><i class="ti-alert mr-1"></i>{{ __('cashmanager::cash_manager.has_incidents_badge') }}</span>
                        @else
                            <span class="badge_1">{{ __('cashmanager::cash_manager.no_incidents_badge') }}</span>
                        @endif
                        <span class="badge_3">{{ __('cashmanager::cash_manager.session_pending_receipt_badge') }}</span>
                    </div>
                </div>

                {{-- Total consolidado --}}
                <div class="alert alert-light border mb-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">{{ __('cashmanager::cash_manager.review_vault_total_received') }}</span>
                    <strong style="font-size:18px; color:#28a745;">
                        $ {{ number_format($principal['total_received'], 0, ',', '.') }}
                    </strong>
                </div>

                {{-- Desglose por AUXILIARY --}}
                @if($principal['breakdown']->isNotEmpty())
                <p class="text-muted small mb-2 text-uppercase font-weight-bold" style="letter-spacing:.5px;">
                    {{ __('cashmanager::cash_manager.review_breakdown_by_auxiliary') }}
                </p>
                @foreach($principal['breakdown'] as $cs)
                    @include('cashmanager::operations._auxiliary_session_card', [
                        'cs'          => $cs,
                        'showConfirm' => false,
                        'compact'     => true,
                    ])
                @endforeach
                @endif

                {{-- Botón confirmar --}}
                @if($session?->status === 'OPEN')
                <div class="text-right mt-3">
                    <button class="btn-toolkit btn-primary btn-review-confirm"
                            data-session-id="{{ $principal['session_id'] }}"
                            data-box-name="{{ $principal['box_name'] }}"
                            data-operator="{{ $principal['operator_name'] }}"
                            data-amount="$ {{ number_format($principal['total_received'], 0, ',', '.') }}">
                        <i class="ti-check mr-1"></i>{{ __('cashmanager::cash_manager.confirm_receipt') }}
                    </button>
                </div>
                @endif

            </div>
            @endforeach
            @endif

        @endif

    {{-- Historial de turnos anteriores ────────────────────────────────────── --}}
    @if($history->isNotEmpty())
    <div class="row mt-5">
        <div class="col-12">
            <hr>
            <h5 class="text-muted mb-3 mt-3">
                <i class="ti-agenda mr-1"></i>{{ __('cashmanager::cash_manager.history_title') }}
            </h5>

            @foreach($history as $h)
            @php $hid = 'hrev-' . substr($h['session_id'], 0, 8); @endphp
            <div class="mb-3 shadow-sm" style="border-left: 3px solid {{ $h['has_incidents'] ? '#ffc107' : '#28a745' }}; border-radius:4px; background:#fff;">
                {{-- Cabecera --}}
                <div class="p-3 d-flex justify-content-between align-items-center hist-toggle"
                     style="cursor:pointer;"
                     data-target="{{ $hid }}">
                    <div>
                        <strong class="text-black">
                            {{ $h['closed_at']?->format('d/m/Y H:i') ?? '—' }}
                        </strong>
                        <span class="text-muted small ml-2">
                            {{ __('cashmanager::cash_manager.history_opened') }}: {{ $h['opened_at']?->format('H:i') }}
                            &nbsp;·&nbsp; {{ $h['operator_name'] }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($h['has_incidents'])
                            <span class="badge_3"><i class="ti-alert mr-1"></i>{{ __('cashmanager::cash_manager.has_incidents_badge') }}</span>
                        @else
                            <span class="badge_1">{{ __('cashmanager::cash_manager.no_incidents_badge') }}</span>
                        @endif
                        <strong class="text-black ml-2">
                            {{ __('cashmanager::cash_manager.review_vault_total_received') }}:
                            $ {{ number_format($h['total_received'], 0, ',', '.') }}
                        </strong>
                        <i class="ti-angle-down text-muted ml-2 hist-icon"></i>
                    </div>
                </div>

                <div id="{{ $hid }}" style="display:none;">
                    <div class="border-top p-3">

                        {{-- Notas del revisor si las hay --}}
                        @if($h['reviewer_notes'])
                        <div class="alert alert-light border mb-3">
                            <i class="ti-comment mr-1"></i>
                            <strong>{{ __('cashmanager::cash_manager.reviewer_notes_label') }}:</strong>
                            {{ $h['reviewer_notes'] }}
                        </div>
                        @endif

                        {{-- Desglose: PRINCIPAL muestra breakdown de AUXILIARY, VAULT muestra PRINCIPAL --}}
                        @if($box->type === 'PRINCIPAL' && isset($h['breakdown']) && $h['breakdown']->isNotEmpty())
                            <p class="text-muted small mb-2 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                                {{ __('cashmanager::cash_manager.review_breakdown_by_auxiliary') }}
                            </p>
                            @foreach($h['breakdown'] as $cs)
                                @include('cashmanager::operations._auxiliary_session_card', [
                                    'cs'          => $cs,
                                    'showConfirm' => false,
                                    'compact'     => true,
                                ])
                            @endforeach

                        @elseif($box->type === 'VAULT' && isset($h['principals']) && $h['principals']->isNotEmpty())
                            <p class="text-muted small mb-2 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                                {{ __('cashmanager::cash_manager.review_breakdown_by_principal') }}
                            </p>
                            @foreach($h['principals'] as $p)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                                <div>
                                    <strong>{{ $p['box_name'] }}</strong>
                                    <span class="text-muted ml-1">{{ $p['box_code'] }}</span>
                                    <span class="text-muted ml-2">· {{ $p['operator_name'] }}</span>
                                </div>
                                <strong class="text-black">$ {{ number_format($p['amount'], 0, ',', '.') }}</strong>
                            </div>
                            @endforeach
                        @endif

                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    </div>
</section>

{{-- MODAL: Confirmar recepción --}}
<dialog class="modal fade" id="reviewReceiptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('cashmanager::cash_manager.confirm_receipt') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <p id="review-receipt-text" class="text-muted mb-3"></p>

                <div class="form-card mb-3">
                    <label class="switch_toggle" for="review-has-incidents">
                        <input type="checkbox" id="review-has-incidents">
                        <div class="slider round"></div>
                        <span class="ml-2">{{ __('cashmanager::cash_manager.reviewer_has_incidents') }}</span>
                    </label>
                </div>

                <div id="review-notes-container" class="d-none">
                    <label class="primary_input_label" for="review-reviewer-notes">
                        {{ __('cashmanager::cash_manager.reviewer_notes_label') }}
                    </label>
                    <textarea id="review-reviewer-notes"
                              class="primary_textarea"
                              rows="3"
                              placeholder="{{ __('cashmanager::cash_manager.reviewer_notes_placeholder') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <button type="button" id="btn-do-review-confirm" class="btn-toolkit btn-primary"
                        data-session-id="">
                    <i class="ti-check mr-1"></i>{{ __('cashmanager::cash_manager.confirm_receipt') }}
                </button>
            </div>
        </div>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
(function ($) {
    "use strict";

    const CSRF = "{{ csrf_token() }}";

    // ── Abrir modal de confirmación ──────────────────────────────────────────
    $(document).on('click', '.btn-review-confirm', function () {
        const sessionId  = $(this).data('session-id');
        const boxName    = $(this).data('box-name');
        const operator   = $(this).data('operator');
        const amount     = $(this).data('amount');

        const warning = "{{ __('cashmanager::cash_manager.confirm_receipt_warning', ['amount' => ':amount', 'user' => ':user']) }}"
            .replace(':amount', amount)
            .replace(':user', operator + ' (' + boxName + ')');

        $('#review-receipt-text').text(warning);
        $('#btn-do-review-confirm').data('session-id', sessionId);
        $('#review-has-incidents').prop('checked', false);
        $('#review-notes-container').addClass('d-none');
        $('#review-reviewer-notes').val('');
        $('#reviewReceiptModal').modal('show');
    });

    $('#review-has-incidents').on('change', function () {
        $('#review-notes-container').toggleClass('d-none', !$(this).is(':checked'));
    });

    $('#btn-do-review-confirm').on('click', function () {
        const sessionId    = $(this).data('session-id');
        const hasIncidents = $('#review-has-incidents').is(':checked') ? 1 : 0;
        const notes        = $('#review-reviewer-notes').val() || null;
        const $btn         = $(this).prop('disabled', true);

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url:  "{{ url('cashmanager/assignments/sessions') }}/" + sessionId + "/confirm",
            type: 'POST',
            data: { _token: CSRF, has_incidents: hasIncidents, reviewer_notes: notes },
            success: function (res) {
                toastr.success(res.message);
                $('#reviewReceiptModal').modal('hide');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
                $btn.prop('disabled', false);
            },
            complete: function () { $('#pre-loader').addClass('d-none'); },
        });
    });

    // ── Enviar reporte al VAULT (solo PRINCIPAL) ─────────────────────────────
    $('#btn-submit-to-vault').on('click', function () {
        const boxId   = $(this).data('box-id');
        const boxName = $(this).data('box-name');
        const $btn    = $(this);

        if (!confirm("{{ __('cashmanager::cash_manager.submit_to_parent_confirm') }}\n\n" + boxName)) return;

        $btn.prop('disabled', true);
        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url:  "{{ url('cashmanager/assignments/boxes') }}/" + boxId + "/submit",
            type: 'POST',
            data: { _token: CSRF },
            success: function (res) {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
                $btn.prop('disabled', false);
            },
            complete: function () { $('#pre-loader').addClass('d-none'); },
        });
    });

    // ── Historial: toggle manual ─────────────────────────────────────────────
    $(document).on('click', '.hist-toggle', function () {
        const targetId = $(this).data('target');
        const $detail  = $('#' + targetId);
        const $icon    = $(this).find('.hist-icon');

        $detail.slideToggle(200);
        $icon.toggleClass('ti-angle-down ti-angle-up');
    });

})(jQuery);
</script>
@endpush
