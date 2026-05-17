@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">

        {{-- Botón de acción --}}
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <span class="badge_{{ $session->status === 'OPEN' ? '1' : '3' }} px-3 py-2" style="font-size:13px;">
                        <i class="ti-reload mr-1"></i>
                        {{ __('cashmanager::cash_manager.session_status_' . strtolower($session->status)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Título --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('cashmanager::cash_manager.operations_title') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">

            {{-- ── Columna izquierda: Resumen de sesión + Medios de pago ────── --}}
            <div class="col-lg-4">

                {{-- Resumen de sesión --}}
                <div class="white_box p-4 mb-4 shadow-sm">
                    <h5 class="text-black mb-3">
                        <i class="ti-id-badge mr-2"></i>{{ __('cashmanager::cash_manager.session_summary') }}
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">{{ __('cashmanager::cash_manager.user') }}</span>
                            <strong>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">{{ __('cashmanager::cash_manager.active_box') }}</span>
                            <strong>{{ $box->code }}</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">{{ __('cashmanager::cash_manager.cost_center') }}</span>
                            <strong>{{ $box->costCenter?->name ?? '—' }}</strong>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">{{ __('cashmanager::cash_manager.base_to_deduct') }}</span>
                            <strong>$ {{ number_format($session->opening_base, 0, ',', '.') }}</strong>
                        </li>
                        <li class="pt-2 border-top text-muted small text-center mt-2">
                            <i class="ti-time mr-1"></i>
                            {{ __('cashmanager::cash_manager.session_opened_at') }}:
                            <span title="{{ $session->opened_at->format('Y-m-d H:i:s') }}">
                                {{ $session->opened_at->diffForHumans() }}
                            </span>
                        </li>
                    </ul>
                </div>

                {{-- Medios de pago declarados por el cajero --}}
                <div class="white_box p-4 shadow-sm">
                    <h5 class="text-black mb-3">
                        <i class="ti-credit-card mr-2"></i>{{ __('cashmanager::cash_manager.payment_methods_declared') }}
                    </h5>
                    <p class="text-muted small mb-3">{{ __('cashmanager::cash_manager.payment_methods_hint') }}</p>

                    <div id="payments-container">
                        @foreach($paymentForms as $pf)
                        <div class="payment-row mb-3 p-3"
                             style="border:1px solid #e8e8e8; border-radius:6px;"
                             data-payment-id="{{ $pf->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-black">{{ $pf->getTranslation('name', app()->getLocale()) }}</strong>
                                <label class="switch_toggle" for="pf-enable-{{ $pf->id }}">
                                    <input type="checkbox" id="pf-enable-{{ $pf->id }}" class="pf-toggle" data-id="{{ $pf->id }}">
                                    <div class="slider round"></div>
                                    <span class="sr-only">{{ __('cashmanager::cash_manager.enable_payment_form') }}</span>
                                </label>
                            </div>
                            <div class="pf-fields d-none">
                                <div class="primary_input mb-2">
                                    <label class="primary_input_label" for="pf-amount-{{ $pf->id }}">
                                        {{ __('cashmanager::cash_manager.total_amount') }}
                                    </label>
                                    <input type="number"
                                           id="pf-amount-{{ $pf->id }}"
                                           class="primary_input_field pf-amount"
                                           min="0"
                                           placeholder="0"
                                           data-id="{{ $pf->id }}">
                                </div>
                                <div class="primary_input mb-2">
                                    <label class="primary_input_label" for="pf-count-{{ $pf->id }}">
                                        {{ __('cashmanager::cash_manager.transaction_count') }}
                                    </label>
                                    <input type="number"
                                           id="pf-count-{{ $pf->id }}"
                                           class="primary_input_field"
                                           min="0"
                                           placeholder="0">
                                </div>
                                <div class="primary_input">
                                    <label class="primary_input_label" for="pf-ref-{{ $pf->id }}">
                                        {{ __('cashmanager::cash_manager.reference_data') }}
                                        <small class="text-muted">({{ __('common.optional') }})</small>
                                    </label>
                                    <input type="text"
                                           id="pf-ref-{{ $pf->id }}"
                                           class="primary_input_field"
                                           placeholder="{{ __('cashmanager::cash_manager.reference_placeholder') }}">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Total declarado --}}
                    <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted">{{ __('cashmanager::cash_manager.total_declared') }}</span>
                        <strong id="ui-total-declared" style="font-size:18px;">$ 0</strong>
                    </div>
                </div>

            </div>

            {{-- ── Columna derecha: Arqueo de denominaciones ──────────────── --}}
            <div class="col-lg-8">
                <div class="white_box p-4 shadow-sm">
                    <h5 class="text-black mb-4">
                        <i class="ti-money mr-2"></i>{{ __('cashmanager::cash_manager.physical_count') }}
                    </h5>

                    <div class="QA_section QA_section_heading_custom">
                        <div class="QA_table">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('cashmanager::cash_manager.denomination') }}</th>
                                        <th style="width:160px;">{{ __('cashmanager::cash_manager.quantity') }}</th>
                                        <th class="text-right" style="width:160px;">{{ __('cashmanager::cash_manager.subtotal') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Billetes --}}
                                    @php $currentType = null; @endphp
                                    @foreach($denominations as $den)
                                    @if($den->type !== $currentType)
                                    @php $currentType = $den->type; @endphp
                                    <tr class="bg-light">
                                        <td colspan="3" class="py-1">
                                            <small class="font-weight-bold text-muted text-uppercase">
                                                {{ $den->type === 'BILLETE'
                                                    ? __('cashmanager::cash_manager.type_bill') . 's'
                                                    : __('cashmanager::cash_manager.type_coin') . 's' }}
                                            </small>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td>
                                            <strong style="font-size:15px;">
                                                $ {{ number_format($den->value, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            <input type="number"
                                                   min="0"
                                                   class="primary_input_field denomination-input"
                                                   data-value="{{ $den->value }}"
                                                   data-id="{{ $den->id }}"
                                                   placeholder="0"
                                                   style="width:120px;">
                                        </td>
                                        <td class="text-right font-weight-bold row-subtotal" style="font-size:15px;">
                                            $ 0
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr>

                    {{-- Resumen del cuadre --}}
                    <div class="row mt-3">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-borderless table-sm">
                                <caption class="sr-only">{{ __('cashmanager::cash_manager.count_summary') }}</caption>
                                <tbody>
                                    <tr>
                                        <th scope="row" class="text-muted font-weight-normal">{{ __('cashmanager::cash_manager.total_counted') }}</th>
                                        <td class="text-right font-weight-bold" id="ui-total-counted">$ 0</td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted font-weight-normal">{{ __('cashmanager::cash_manager.base_to_deduct') }}</th>
                                        <td class="text-right text-danger font-weight-bold"
                                            id="ui-base-amount"
                                            data-base="{{ $session->opening_base }}">
                                            − $ {{ number_format($session->opening_base, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    <tr class="border-top">
                                        <th scope="row"><strong>{{ __('cashmanager::cash_manager.cash_to_deliver') }}</strong></th>
                                        <td class="text-right">
                                            <strong id="ui-cash-deliver" style="font-size:18px; color:#28a745;">$ 0</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- Comparación vs declarado --}}
                            <div id="ui-difference-panel" class="alert mt-3" style="display:none; border-radius:8px;">
                                <div class="d-flex justify-content-between">
                                    <span>{{ __('cashmanager::cash_manager.total_declared') }}</span>
                                    <strong id="ui-declared-echo">$ 0</strong>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <strong>{{ __('cashmanager::cash_manager.difference') }}</strong>
                                    <strong id="ui-difference-value">$ 0</strong>
                                </div>
                            </div>

                            {{-- Novedad: tipo + justificación + notas (visible solo si hay discrepancia) --}}
                            <div id="ui-justification-container" style="display:none;" class="mt-3">
                                <div class="primary_input mb-3">
                                    <label class="primary_input_label text-danger" for="sel-discrepancy-type">
                                        <i class="ti-alert mr-1"></i>{{ __('cashmanager::cash_manager.discrepancy_type') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select id="sel-discrepancy-type" class="primary_input_select">
                                        <option value="">{{ __('cashmanager::cash_manager.discrepancy_type_placeholder') }}</option>
                                        @foreach($discrepancyTypes as $dt)
                                            <option value="{{ $dt->id }}" data-code="{{ $dt->code }}">
                                                {{ $dt->getTranslation('name', app()->getLocale()) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="primary_input mb-3">
                                    <label class="primary_input_label text-danger" for="txt-justification">
                                        {{ __('cashmanager::cash_manager.justification') }} <span class="text-danger">*</span>
                                    </label>
                                    <textarea id="txt-justification"
                                              class="primary_textarea"
                                              rows="2"
                                              placeholder="{{ __('cashmanager::cash_manager.justification_placeholder') }}"></textarea>
                                </div>
                                <div class="primary_input" id="ui-notes-container">
                                    <label class="primary_input_label" for="txt-notes">
                                        {{ __('cashmanager::cash_manager.notes') }}
                                        <span id="notes-required-mark" class="text-danger d-none">*</span>
                                    </label>
                                    <textarea id="txt-notes"
                                              class="primary_textarea"
                                              rows="2"
                                              placeholder="{{ __('cashmanager::cash_manager.notes_placeholder') }}"></textarea>
                                </div>
                            </div>

                            @if($session->status === 'OPEN')
                            <button id="btn-close-box"
                                    class="btn-toolkit btn-primary w-100 mt-4 py-3"
                                    style="font-size:15px;">
                                <i class="ti-lock mr-2"></i>{{ __('cashmanager::cash_manager.close_box') }}
                            </button>
                            @elseif($session->status === 'PENDING_RECEIPT')
                            <div class="alert alert-warning mt-4 text-center">
                                <i class="ti-time mr-2"></i>
                                <strong>{{ __('cashmanager::cash_manager.session_status_pending_receipt') }}</strong>
                                <p class="mb-0 mt-1 small">{{ __('cashmanager::cash_manager.session_closed_success') }}</p>
                            </div>
                            @else
                            <div class="alert alert-success mt-4 text-center">
                                <i class="ti-check mr-2"></i>
                                <strong>{{ __('cashmanager::cash_manager.session_status_closed') }}</strong>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- Historial de cierres anteriores ───────────────────────────────────── --}}
    @if($history->isNotEmpty())
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="text-muted mb-3">
                <i class="ti-agenda mr-1"></i>{{ __('cashmanager::cash_manager.history_title') }}
            </h5>

            @foreach($history as $h)
            @php $hid = 'haux-' . substr($h['session_id'], 0, 8); @endphp
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
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($h['has_incidents'])
                            <span class="badge_3"><i class="ti-alert mr-1"></i>{{ __('cashmanager::cash_manager.has_incidents_badge') }}</span>
                        @else
                            <span class="badge_1">{{ __('cashmanager::cash_manager.no_incidents_badge') }}</span>
                        @endif
                        <strong class="text-black ml-2">
                            {{ __('cashmanager::cash_manager.cash_to_deliver') }}:
                            $ {{ number_format($h['total_physical'] - $h['opening_base'], 0, ',', '.') }}
                        </strong>
                        <i class="ti-angle-down text-muted ml-2 hist-icon"></i>
                    </div>
                </div>

                {{-- Detalle (oculto por defecto) --}}
                <div id="{{ $hid }}" style="display:none;">
                    <div class="border-top p-3">
                        <div class="row">
                            {{-- Resumen financiero --}}
                            <div class="col-md-4">
                                <p class="text-muted small mb-2 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                                    {{ __('cashmanager::cash_manager.count_summary') }}
                                </p>
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <th scope="row" class="text-muted font-weight-normal pl-0 small">{{ __('cashmanager::cash_manager.opening_base') }}</th>
                                            <td class="text-right small">$ {{ number_format($h['opening_base'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="text-muted font-weight-normal pl-0 small">{{ __('cashmanager::cash_manager.total_counted') }}</th>
                                            <td class="text-right small font-weight-bold">$ {{ number_format($h['total_physical'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row" class="text-muted font-weight-normal pl-0 small">{{ __('cashmanager::cash_manager.total_declared') }}</th>
                                            <td class="text-right small">$ {{ number_format($h['total_declared'], 0, ',', '.') }}</td>
                                        </tr>
                                        @if($h['discrepancy_amount'] != 0)
                                        <tr>
                                            <th scope="row" class="text-danger font-weight-normal pl-0 small">{{ __('cashmanager::cash_manager.difference') }}</th>
                                            <td class="text-right small text-danger font-weight-bold">$ {{ number_format($h['discrepancy_amount'], 0, ',', '.') }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- Denominaciones --}}
                            @if($h['denominations']->isNotEmpty())
                            <div class="col-md-4">
                                <p class="text-muted small mb-2 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                                    {{ __('cashmanager::cash_manager.physical_count') }}
                                </p>
                                @foreach($h['denominations'] as $d)
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">{{ $d['label'] }}</span>
                                    <span>× {{ $d['quantity'] }} = <strong>$ {{ number_format($d['subtotal'], 0, ',', '.') }}</strong></span>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Medios de pago + novedades --}}
                            <div class="col-md-4">
                                @if($h['payments']->isNotEmpty())
                                <p class="text-muted small mb-2 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                                    {{ __('cashmanager::cash_manager.payment_methods_declared') }}
                                </p>
                                @foreach($h['payments'] as $p)
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">{{ $p['name'] }}</span>
                                    <strong>$ {{ number_format($p['amount'], 0, ',', '.') }}</strong>
                                </div>
                                @endforeach
                                @endif

                                @if($h['discrepancies']->isNotEmpty())
                                <div class="mt-2">
                                    <p class="text-muted small mb-1 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                                        {{ __('cashmanager::cash_manager.discrepancy_type') }}
                                    </p>
                                    @foreach($h['discrepancies'] as $d)
                                    <p class="small text-danger mb-1"><strong>{{ $d['type'] }}</strong>: $ {{ number_format($d['amount'], 0, ',', '.') }}</p>
                                    @if($d['justification'])<p class="small text-muted mb-0"><i class="ti-comment mr-1"></i>{{ $d['justification'] }}</p>@endif
                                    @if($d['notes'])<p class="small text-muted mb-0"><i class="ti-notepad mr-1"></i>{{ $d['notes'] }}</p>@endif
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
</section>

{{-- Datos para JS --}}
<script>
    window.CashManager = {
        sessionId:   "{{ $session->id }}",
        openingBase: {{ $session->opening_base }},
        closeUrl:    "{{ route('cash_manager.operations.close') }}",
        csrf:        "{{ csrf_token() }}",
    };
</script>
@endsection

@push('scripts')
<script>
(function ($) {
    "use strict";

    const CM = window.CashManager;

    // ── Mostrar/ocultar campos de medio de pago al activar toggle ────────────
    $(document).on('change', '.pf-toggle', function () {
        const id     = $(this).data('id');
        const fields = $(this).closest('.payment-row').find('.pf-fields');
        fields.toggleClass('d-none', !$(this).is(':checked'));
        if (!$(this).is(':checked')) {
            $('#pf-amount-' + id).val('').trigger('input');
        }
        recalcDeclared();
    });

    // ── Recalcular total declarado cuando cambia un monto ────────────────────
    $(document).on('input', '.pf-amount', function () { recalcDeclared(); });

    function recalcDeclared() {
        let total = 0;
        $('.pf-amount').each(function () {
            const row = $(this).closest('.payment-row');
            if (!row.find('.pf-toggle').is(':checked')) return;
            total += parseFloat($(this).val()) || 0;
        });
        $('#ui-total-declared').text('$ ' + total.toLocaleString('es-CO'));
        recalcDifference();
    }

    // ── Calcular subtotales de denominaciones ────────────────────────────────
    $(document).on('input', '.denomination-input', function () {
        const qty      = parseInt($(this).val()) || 0;
        const val      = parseFloat($(this).data('value'));
        const subtotal = qty * val;
        $(this).closest('tr').find('.row-subtotal').text('$ ' + subtotal.toLocaleString('es-CO'));
        recalcPhysical();
    });

    function recalcPhysical() {
        let total = 0;
        $('.denomination-input').each(function () {
            total += (parseInt($(this).val()) || 0) * parseFloat($(this).data('value'));
        });
        const toDeliver = total - CM.openingBase;

        $('#ui-total-counted').text('$ ' + total.toLocaleString('es-CO'));
        $('#ui-cash-deliver').text('$ ' + toDeliver.toLocaleString('es-CO'));
        recalcDifference();
    }

    function recalcDifference() {
        const toDeliver = (function () {
            let t = 0;
            $('.denomination-input').each(function () {
                t += (parseInt($(this).val()) || 0) * parseFloat($(this).data('value'));
            });
            return t - CM.openingBase;
        })();

        let declared = 0;
        $('.pf-amount').each(function () {
            const row = $(this).closest('.payment-row');
            if (!row.find('.pf-toggle').is(':checked')) return;
            declared += parseFloat($(this).val()) || 0;
        });

        const diff  = toDeliver - declared;
        const panel = $('#ui-difference-panel');

        if (declared > 0) {
            panel.show();
            $('#ui-declared-echo').text('$ ' + declared.toLocaleString('es-CO'));
            $('#ui-difference-value').text('$ ' + diff.toLocaleString('es-CO'));

            if (Math.abs(diff) > 0) {
                panel.removeClass('alert-success').addClass('alert-danger');
                $('#ui-justification-container').show();
            } else {
                panel.removeClass('alert-danger').addClass('alert-success');
                $('#ui-justification-container').hide();
            }
        } else {
            panel.hide();
            $('#ui-justification-container').hide();
        }
    }

    // ── Marcar notas como obligatorias cuando tipo = 'other' ────────────────
    $(document).on('change', '#sel-discrepancy-type', function () {
        const isOther = $(this).find(':selected').data('code') === 'other';
        $('#notes-required-mark').toggleClass('d-none', !isOther);
    });

    // ── Botón de cierre ──────────────────────────────────────────────────────
    $('#btn-close-box').on('click', function () {
        const $btn = $(this).prop('disabled', true);

        // Construir arrays de denominaciones
        const denominations = [];
        $('.denomination-input').each(function () {
            const qty = parseInt($(this).val()) || 0;
            if (qty > 0) {
                denominations.push({
                    denomination_id: $(this).data('id'),
                    quantity: qty,
                });
            }
        });

        if (denominations.length === 0) {
            toastr.warning("{{ __('cashmanager::cash_manager.error_no_denominations') }}");
            $btn.prop('disabled', false);
            return;
        }

        // Construir array de pagos
        const payments = [];
        $('.payment-row').each(function () {
            const toggle = $(this).find('.pf-toggle');
            if (!toggle.is(':checked')) return;
            const id = $(this).data('payment-id');
            payments.push({
                payment_form_id:   id,
                total_amount:      parseFloat($('#pf-amount-' + id).val()) || 0,
                transaction_count: parseInt($('#pf-count-' + id).val()) || 0,
                reference_data:    $('#pf-ref-' + id).val() || null,
            });
        });

        if (payments.length === 0) {
            toastr.warning("{{ __('cashmanager::cash_manager.error_no_payments') }}");
            $btn.prop('disabled', false);
            return;
        }

        const hasDiff          = $('#ui-difference-panel').hasClass('alert-danger');
        const discrepancyTypeId = parseInt($('#sel-discrepancy-type').val()) || null;
        const justification     = $('#txt-justification').val() || null;
        const notes             = $('#txt-notes').val() || null;
        const typeCode          = $('#sel-discrepancy-type option:selected').data('code');

        if (hasDiff) {
            if (!discrepancyTypeId) {
                toastr.error("{{ __('cashmanager::cash_manager.error_discrepancy_type_required') }}");
                $btn.prop('disabled', false);
                return;
            }
            if (!justification) {
                toastr.error("{{ __('cashmanager::cash_manager.error_justification_required') }}");
                $btn.prop('disabled', false);
                return;
            }
            if (typeCode === 'other' && !notes) {
                toastr.error("{{ __('cashmanager::cash_manager.notes_required_for_other') }}");
                $btn.prop('disabled', false);
                return;
            }
        }

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url:  CM.closeUrl,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                _token:               CM.csrf,
                session_id:           CM.sessionId,
                denominations:        denominations,
                payments:             payments,
                discrepancy_type_id:  discrepancyTypeId,
                justification:        justification,
                notes:                notes,
            }),
            success: function (res) {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1200);
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.values(errors).flat().forEach(m => toastr.error(m));
                } else {
                    toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
                }
                $btn.prop('disabled', false);
            },
            complete: function () { $('#pre-loader').addClass('d-none'); },
        });
    });

    // ── Historial: toggle manual sin Bootstrap collapse ──────────────────────
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
