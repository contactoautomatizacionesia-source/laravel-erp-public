@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">

        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    {{-- Sin acción global; cada caja tiene su propio botón --}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('cashmanager::cash_manager.assignments_title') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid de Cajas --}}
        <div class="row mt-3" id="boxes-grid">
            @forelse($boxes as $box)
            <div class="col-md-4 mb-4" id="box-card-{{ $box['id'] }}">
                <div class="white_box h-100 border p-4 shadow-sm">

                    {{-- Cabecera: tipo + código + badge --}}
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <span class="badge_5 mr-1" style="font-size:10px;">{{ $box['type_label'] }}</span>
                            <strong class="text-black">{{ $box['code'] }}</strong>
                        </div>
                        @if($box['session_status'] === 'PENDING_RECEIPT')
                            <span class="badge_3">{{ __('cashmanager::cash_manager.session_pending_receipt_badge') }}</span>
                        @elseif($box['assigned_user'])
                            <span class="badge_1">{{ __('cashmanager::cash_manager.box_occupied') }}</span>
                        @else
                            <span class="badge_2">{{ __('cashmanager::cash_manager.box_free') }}</span>
                        @endif
                    </div>

                    {{-- Caja superior (si aplica) --}}
                    @if($box['parent_name'])
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="ti-arrow-up mr-1"></i>{{ $box['parent_name'] }}
                        </small>
                    </div>
                    @endif

                    <h5 class="text-black mb-3">{{ $box['name'] }}</h5>

                    {{-- Estado operativo --}}
                    <div class="text-center py-3">
                        @if($box['assigned_user'])
                            <i class="ti-user text-success mb-2" style="font-size:3.5rem;display:block;"></i>
                            <p class="mb-0 font-weight-bold text-black">{{ $box['assigned_user']['name'] }}</p>
                            <small class="text-muted">
                                {{ __('cashmanager::cash_manager.assigned_since') }} {{ $box['assigned_user']['since'] }}
                            </small>
                            @if($box['has_incidents'])
                                <span class="badge_3 mt-1 d-inline-block" style="font-size:10px;">
                                    <i class="ti-alert mr-1"></i>Con novedades
                                </span>
                            @endif
                        @else
                            <i class="ti-wallet text-muted mb-2" style="font-size:3.5rem;display:block;"></i>
                            <small class="text-muted">{{ __('cashmanager::cash_manager.box_waiting_operator') }}</small>
                        @endif
                    </div>

                    {{-- Base inicial --}}
                    <p class="text-center text-muted small mt-2 mb-3">
                        {{ __('cashmanager::cash_manager.base_assigned') }}:
                        <strong>$ {{ number_format($box['base'], 0, ',', '.') }}</strong>
                    </p>

                    {{-- Acciones según tipo y estado --}}
                    <div class="d-flex flex-column gap-2">

                        {{-- Sesión pendiente de recibir: solo informativo, la gestión es en /operations --}}
                        @if($box['session_status'] === 'PENDING_RECEIPT')
                            <div class="text-center text-muted small py-2">
                                <i class="ti-time mr-1"></i>
                                {{ __('cashmanager::cash_manager.pending_receipt_manage_in_operations') }}
                            </div>

                        {{-- PRINCIPAL/VAULT activa con sesión OPEN: solo revocar (gestión de cierres en /operations) --}}
                        @elseif(in_array($box['type'], ['PRINCIPAL', 'VAULT']) && $box['assigned_user'] && $box['session_status'] === 'OPEN')
                            <div class="text-center text-muted small py-1">
                                <i class="ti-info-alt mr-1"></i>
                                {{ __('cashmanager::cash_manager.review_manage_in_operations') }}
                            </div>
                            <button class="btn-toolkit btn-danger w-100 btn-revoke"
                                    data-id="{{ $box['assignment_id'] }}"
                                    data-box="{{ $box['name'] }}">
                                <i class="ti-close mr-1"></i>{{ __('cashmanager::cash_manager.revoke_assignment') }}
                            </button>

                        {{-- AUXILIARY activa con sesión OPEN: solo revocar --}}
                        @elseif($box['assigned_user'] && $box['session_status'] === 'OPEN')
                            <button class="btn-toolkit btn-danger w-100 btn-revoke"
                                    data-id="{{ $box['assignment_id'] }}"
                                    data-box="{{ $box['name'] }}">
                                <i class="ti-close mr-1"></i>{{ __('cashmanager::cash_manager.revoke_assignment') }}
                            </button>

                        {{-- Caja libre: asignar operador --}}
                        @elseif($box['status'] === 'AVAILABLE')
                            <button class="btn-toolkit btn-primary w-100"
                                    data-toggle="modal"
                                    data-target="#assignModal-{{ $box['id'] }}">
                                <i class="ti-plus mr-1"></i>{{ __('cashmanager::cash_manager.assign_operator') }}
                            </button>

                        {{-- Caja en otro estado (MAINTENANCE, INACTIVE) --}}
                        @else
                            <button class="btn-toolkit btn-secondary-outline w-100" disabled>
                                <i class="ti-lock mr-1"></i>
                                {{ __('cashmanager::cash_manager.box_status_' . strtolower($box['status'])) }}
                            </button>
                        @endif

                    </div>

                </div>
            </div>
            @empty
            <div class="col-12">
                <p class="text-muted text-center py-5">{{ __('cashmanager::cash_manager.no_boxes_configured') }}</p>
            </div>
            @endforelse
        </div>

    </div>
</section>

{{-- MODALES de asignación — uno por caja libre ──────────────────────────── --}}
@foreach($boxes as $box)
@if(!$box['assigned_user'] && $box['status'] === 'AVAILABLE')
<dialog class="modal fade" id="assignModal-{{ $box['id'] }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('cashmanager::cash_manager.assign_modal_title', ['box' => $box['name']]) }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-card">
                    <div class="alert alert-warning mb-3">
                        <i class="ti-info-alt mr-2"></i>
                        {!! __('cashmanager::cash_manager.delivery_warning', [
                            'amount' => '<strong>$ ' . number_format($box['base'], 0, ',', '.') . '</strong>'
                        ]) !!}
                    </div>
                    <label class="primary_input_label" for="user-select-{{ $box['id'] }}">
                        {{ __('cashmanager::cash_manager.select_cashier') }} <span class="text-danger">*</span>
                    </label>
                    <select id="user-select-{{ $box['id'] }}"
                            class="primary_input_select cashier-select"
                            data-box-id="{{ $box['id'] }}"
                            required>
                        <option value="">{{ __('cashmanager::cash_manager.select_user_placeholder') }}</option>
                        @foreach($cashiers as $user)
                            <option value="{{ $user->id }}">
                                {{ trim($user->first_name . ' ' . $user->last_name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <button type="button"
                        class="btn-toolkit btn-primary btn-confirm-assign"
                        data-box-id="{{ $box['id'] }}"
                        data-modal="#assignModal-{{ $box['id'] }}">
                    <i class="ti-check mr-1"></i>{{ __('cashmanager::cash_manager.confirm_delivery') }}
                </button>
            </div>
        </div>
    </div>
</dialog>
@endif
@endforeach
@endsection

@push('scripts')
<script>
(function ($) {
    "use strict";

    const CSRF = "{{ csrf_token() }}";

    // ── Asignar operador ─────────────────────────────────────────────────────
    $(document).on('click', '.btn-confirm-assign', function () {
        const boxId  = $(this).data('box-id');
        const userId = $('#user-select-' + boxId).val();
        const $modal = $($(this).data('modal'));
        const $btn   = $(this).prop('disabled', true);

        if (!userId) {
            toastr.warning("{{ __('cashmanager::cash_manager.select_user_placeholder') }}");
            $btn.prop('disabled', false);
            return;
        }

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url:  "{{ route('cash_manager.assignments.store') }}",
            type: 'POST',
            data: { _token: CSRF, cash_box_id: boxId, user_id: userId },
            success: function (res) {
                toastr.success(res.message);
                $modal.modal('hide');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
            },
            complete: function () {
                $('#pre-loader').addClass('d-none');
                $btn.prop('disabled', false);
            },
        });
    });

    // ── Revocar asignación ───────────────────────────────────────────────────
    $(document).on('click', '.btn-revoke', function () {
        const id      = $(this).data('id');
        const boxName = $(this).data('box');
        const $btn    = $(this);

        if (!confirm("{{ __('cashmanager::cash_manager.revoke_confirm') }}\n" + boxName)) return;

        $btn.prop('disabled', true);
        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url:  "{{ url('cashmanager/assignments') }}/" + id + "/revoke",
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

})(jQuery);
</script>
@endpush
