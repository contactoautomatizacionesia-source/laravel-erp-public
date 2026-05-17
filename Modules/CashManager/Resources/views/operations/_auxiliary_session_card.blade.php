{{--
    Partial: card de una sesión AUXILIARY.
    Variables:
      $cs           — array mapeado por mapAuxiliarySession()
      $showConfirm  — bool: mostrar botón "Confirmar Recepción"
      $compact      — bool (opcional): diseño más compacto para desglose en VAULT
--}}
@php $compact = $compact ?? false; @endphp

<div class="white_box {{ $compact ? 'p-3 border' : 'p-4 shadow-sm' }} mb-3"
     style="{{ $compact ? 'background:#fafafa;' : '' }}">
    <div class="row align-items-start">

        {{-- Cabecera --}}
        <div class="col-12 mb-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong class="text-black" style="font-size:{{ $compact ? '14px' : '16px' }};">{{ $cs['box_name'] }}</strong>
                    <span class="text-muted ml-2 small">{{ $cs['box_code'] }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($cs['has_incidents'])
                        <span class="badge_3">
                            <i class="ti-alert mr-1"></i>{{ __('cashmanager::cash_manager.has_incidents_badge') }}
                        </span>
                    @else
                        <span class="badge_1">{{ __('cashmanager::cash_manager.no_incidents_badge') }}</span>
                    @endif
                    @if($showConfirm)
                        <span class="badge_3">{{ __('cashmanager::cash_manager.session_pending_receipt_badge') }}</span>
                    @else
                        <span class="badge_1">{{ __('cashmanager::cash_manager.session_confirmed_badge') }}</span>
                    @endif
                </div>
            </div>
            <div class="text-muted small mt-1">
                <i class="ti-user mr-1"></i>{{ $cs['operator_name'] }}
                &nbsp;·&nbsp;
                <i class="ti-time mr-1"></i>
                <span title="{{ $cs['closed_at']?->format('Y-m-d H:i:s') }}">
                    {{ __('cashmanager::cash_manager.closed_at') }}: {{ $cs['closed_at']?->diffForHumans() ?? '—' }}
                </span>
            </div>
        </div>

        {{-- Columna: Resumen financiero --}}
        <div class="{{ $showConfirm ? 'col-md-5' : 'col-md-6' }}">
            <table class="table table-sm table-borderless mb-0">
                <caption class="sr-only">Resumen financiero de la sesión</caption>
                <tbody>
                    <tr>
                        <th scope="row" class="text-muted font-weight-normal pl-0">{{ __('cashmanager::cash_manager.opening_base') }}</th>
                        <td class="text-right font-weight-bold">$ {{ number_format($cs['opening_base'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="text-muted font-weight-normal pl-0">{{ __('cashmanager::cash_manager.total_counted') }}</th>
                        <td class="text-right font-weight-bold">$ {{ number_format($cs['total_physical'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th scope="row" class="text-muted font-weight-normal pl-0">{{ __('cashmanager::cash_manager.total_declared') }}</th>
                        <td class="text-right font-weight-bold">$ {{ number_format($cs['total_declared'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-top">
                        <th scope="row" class="pl-0"><strong>{{ __('cashmanager::cash_manager.cash_to_deliver') }}</strong></th>
                        <td class="text-right font-weight-bold"
                            style="color:{{ $cs['discrepancy_amount'] != 0 ? '#dc3545' : '#28a745' }}; font-size:{{ $compact ? '14px' : '15px' }};">
                            $ {{ number_format($cs['total_physical'] - $cs['opening_base'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @if($cs['discrepancy_amount'] != 0)
                    <tr>
                        <th scope="row" class="pl-0 text-danger">{{ __('cashmanager::cash_manager.difference') }}</th>
                        <td class="text-right font-weight-bold text-danger">
                            $ {{ number_format($cs['discrepancy_amount'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Columna: Medios de pago + novedades --}}
        <div class="{{ $showConfirm ? 'col-md-4' : 'col-md-6' }}">
            @if($cs['payments']->isNotEmpty())
            <p class="text-muted small mb-1 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                {{ __('cashmanager::cash_manager.payment_methods_declared') }}
            </p>
            @foreach($cs['payments'] as $p)
            <div class="d-flex justify-content-between small mb-1">
                <span class="text-muted">{{ $p['name'] }}</span>
                <strong>$ {{ number_format($p['amount'], 0, ',', '.') }}</strong>
            </div>
            @endforeach
            @endif

            @if($cs['discrepancies']->isNotEmpty())
            <div class="mt-2">
                <p class="text-muted small mb-1 font-weight-bold text-uppercase" style="letter-spacing:.5px;">
                    {{ __('cashmanager::cash_manager.discrepancy_type') }}
                </p>
                @foreach($cs['discrepancies'] as $d)
                <div class="small text-danger mb-1">
                    <strong>{{ $d['type'] }}</strong>: $ {{ number_format($d['amount'], 0, ',', '.') }}
                </div>
                @if($d['justification'])
                <p class="small text-muted mb-1">
                    <i class="ti-comment mr-1"></i>{{ $d['justification'] }}
                </p>
                @endif
                @if($d['notes'])
                <p class="small text-muted mb-0">
                    <i class="ti-notepad mr-1"></i>{{ $d['notes'] }}
                </p>
                @endif
                @endforeach
            </div>
            @endif
        </div>

        {{-- Columna: Acción confirmar --}}
        @if($showConfirm)
        <div class="col-md-3 d-flex align-items-center justify-content-end mt-3 mt-md-0">
            <button class="btn-toolkit btn-primary btn-review-confirm"
                    data-session-id="{{ $cs['session_id'] }}"
                    data-box-name="{{ $cs['box_name'] }}"
                    data-operator="{{ $cs['operator_name'] }}"
                    data-amount="$ {{ number_format($cs['total_physical'] - $cs['opening_base'], 0, ',', '.') }}">
                <i class="ti-check mr-1"></i>{{ __('cashmanager::cash_manager.confirm_receipt') }}
            </button>
        </div>
        @endif

    </div>
</div>
