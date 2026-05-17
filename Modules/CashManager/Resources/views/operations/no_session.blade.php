@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">

        <div class="row">
            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">
                            {{ __('cashmanager::cash_manager.operations_title') }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sin caja activa --}}
        <div class="row justify-content-center mt-4">
            <div class="col-md-6 text-center">
                <i class="ti-lock text-muted mb-4" style="font-size:72px; display:block;"></i>
                <h4 class="text-black mb-2">{{ __('cashmanager::cash_manager.no_session_title') }}</h4>
                <p class="text-muted mb-0">{{ __('cashmanager::cash_manager.no_session_message') }}</p>
            </div>
        </div>

        {{-- Historial de cierres anteriores ────────────────────────────────── --}}
        @if(!empty($history) && $history->isNotEmpty())
        <div class="row mt-5">
            <div class="col-12">
                <h5 class="text-muted mb-3">
                    <i class="ti-agenda mr-1"></i>{{ __('cashmanager::cash_manager.history_title') }}
                    @if(!empty($box))
                        <span class="text-muted small font-weight-normal ml-2">— {{ $box->name }}</span>
                    @endif
                </h5>

                @foreach($history as $h)
                @php $hid = 'haux-' . substr($h['session_id'], 0, 8); @endphp
                <div class="mb-3 shadow-sm" style="border-left: 3px solid {{ $h['has_incidents'] ? '#ffc107' : '#28a745' }}; border-radius:4px; background:#fff;">
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

                    <div id="{{ $hid }}" style="display:none;">
                        <div class="border-top p-3">
                            <div class="row">
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

                                @if(!empty($h['denominations']) && $h['denominations']->isNotEmpty())
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

                                <div class="col-md-4">
                                    @if(!empty($h['payments']) && $h['payments']->isNotEmpty())
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

                                    @if(!empty($h['discrepancies']) && $h['discrepancies']->isNotEmpty())
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
@endsection

@push('scripts')
<script>
(function ($) {
    "use strict";

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
