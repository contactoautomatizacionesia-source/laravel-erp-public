@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">

        {{-- Botones de acción (arriba, por patrón del proyecto) --}}
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end" role="tablist">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2" id="settings_nav">

                            <li class="nav-item action_btn" id="action_new_denomination">
                                <a class="nav-link action" href="javascript:void(0)" data-toggle="modal" data-target="#modalNewDenomination">
                                    <i class="ti-plus mr-1"></i>{{ __('cashmanager::cash_manager.new_denomination') }}
                                </a>
                            </li>

                            <li class="nav-item action_btn d-none" id="action_new_box">
                                <a class="nav-link action" href="javascript:void(0)" data-toggle="modal" data-target="#modalNewBox">
                                    <i class="ti-plus mr-1"></i>{{ __('cashmanager::cash_manager.new_box') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link active show" href="#tab-denominations" role="tab" data-toggle="tab" aria-selected="true">
                                    {{ __('cashmanager::cash_manager.tab_denominations') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#tab-structure" role="tab" data-toggle="tab" aria-selected="false">
                                    {{ __('cashmanager::cash_manager.tab_structure') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#tab-roles" role="tab" data-toggle="tab" aria-selected="false">
                                    {{ __('cashmanager::cash_manager.tab_roles') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Título --}}
        <div class="row">
            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('cashmanager::cash_manager.settings_title') }}</h3>
                    </div>
                </div>

                <div class="tab-content pt-4" id="settingsTabContent">

                    {{-- TAB 1: DENOMINACIONES ──────────────────────────────── --}}
                    <div class="tab-pane fade show active" id="tab-denominations" role="tabpanel">

                        <div class="QA_section QA_section_heading_custom check_box_table">
                            <div class="QA_table">
                                <table class="table Crm_table_active3" id="denominationsTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('cashmanager::cash_manager.denomination_country') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.value') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.type') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.status') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($denominations as $den)
                                        <tr id="den-row-{{ $den['id'] }}">
                                            <td>{{ $den['country'] }}</td>
                                            <td><strong>$ {{ number_format($den['value'], 0, ',', '.') }}</strong></td>
                                            <td>
                                                <span class="{{ $den['type'] === 'BILLETE' ? 'badge_5' : 'badge_3' }}">
                                                    {{ $den['type_label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <label class="switch_toggle">
                                                    <span class="sr-only">Toggle status</span>
                                                    <input type="checkbox"
                                                           id="den-toggle-{{ $den['id'] }}"
                                                           class="denomination_status"
                                                           data-id="{{ $den['id'] }}"
                                                           aria-label="Toggle status"
                                                           {{ $den['is_active'] ? 'checked' : '' }}>
                                                    <div class="slider round"></div>
                                                </label>
                                            </td>
                                            <td>
                                                <button class="btn-toolkit btn-danger btn-sm btn-delete-denomination"
                                                        data-id="{{ $den['id'] }}"
                                                        title="{{ __('common.delete') }}">
                                                    <i class="ti-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: ESTRUCTURA DE CAJAS ────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-structure" role="tabpanel">

                        <div class="QA_section QA_section_heading_custom check_box_table">
                            <div class="QA_table">
                                <table class="table Crm_table_active3">
                                    <thead>
                                        <tr>
                                            <th>{{ __('cashmanager::cash_manager.unique_code') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.box_name') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.cost_center') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.hierarchy_type') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.box_parent') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.initial_base') }}</th>
                                            <th>{{ __('cashmanager::cash_manager.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($cashBoxes as $box)
                                        <tr>
                                            <td><strong>{{ $box['code'] }}</strong></td>
                                            <td>{{ $box['name'] }}</td>
                                            <td>{{ $box['cc_name'] }}</td>
                                            <td>
                                                <span class="{{ $box['type'] === 'VAULT' ? 'badge_2' : ($box['type'] === 'PRINCIPAL' ? 'badge_1' : 'badge_5') }}">
                                                    {{ $box['type_label'] }}
                                                </span>
                                            </td>
                                            <td class="text-muted">{{ $box['parent_name'] }}</td>
                                            <td>$ {{ number_format($box['base'], 0, ',', '.') }}</td>
                                            <td>
                                                <span class="{{ $box['status'] === 'AVAILABLE' ? 'badge_1' : ($box['status'] === 'OPEN' ? 'badge_5' : 'badge_3') }}">
                                                    {{ __('cashmanager::cash_manager.box_status_' . strtolower($box['status'])) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: ROLES DE OPERADOR ──────────────────────────── --}}
                    <div class="tab-pane fade" id="tab-roles" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <form id="formOperatorRoles">
                                    @csrf
                                    <p class="text-muted mb-4">{{ __('cashmanager::cash_manager.roles_description') }}</p>

                                    <div class="row">
                                        @foreach($allRoles as $role)
                                        <div class="col-md-4 mb-3">
                                            <div class="primary_input d-flex align-items-center gap-2">
                                                <input type="checkbox"
                                                       id="role-{{ $role->id }}"
                                                       name="operator_role_ids[]"
                                                       value="{{ $role->id }}"
                                                       class="mr-2"
                                                       {{ in_array($role->id, $operatorRoleIds) ? 'checked' : '' }}>
                                                <label class="primary_input_label mb-0" for="role-{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn-toolkit btn-primary">
                                            <i class="ti-save mr-1"></i>{{ __('cashmanager::cash_manager.save_roles') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-content --}}
            </div>
        </div>
    </div>
</section>

{{-- MODAL: Nueva Denominación ──────────────────────────────────────────────── --}}
<dialog class="modal fade" id="modalNewDenomination" tabindex="-1">
    <div class="modal-dialog">
        <form id="formNewDenomination">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cashmanager::cash_manager.new_denomination') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-card">
                        <div class="row">
                            <div class="col-12">
                                <label class="primary_input_label" for="den_country_id">{{ __('cashmanager::cash_manager.denomination_country') }} <span class="text-danger">*</span></label>
                                <select name="country_id" id="den_country_id" class="primary_input_select" required>
                                    <option value="">—</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mt-15">
                                <label class="primary_input_label" for="den_type">{{ __('cashmanager::cash_manager.type') }} <span class="text-danger">*</span></label>
                                <select name="type" id="den_type" class="primary_input_select" required>
                                    <option value="BILLETE">{{ __('cashmanager::cash_manager.type_bill') }}</option>
                                    <option value="MONEDA">{{ __('cashmanager::cash_manager.type_coin') }}</option>
                                </select>
                            </div>
                            <div class="col-12 mt-15">
                                <label class="primary_input_label" for="den_value">{{ __('cashmanager::cash_manager.value') }} <small class="text-muted">{{ __('cashmanager::cash_manager.value_example') }}</small> <span class="text-danger">*</span></label>
                                <input type="number" name="value" id="den_value" class="primary_input_field" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn-toolkit btn-primary">{{ __('common.save') }}</button>
                </div>
            </div>
        </form>
    </div>
</dialog>

{{-- MODAL: Nueva Caja ──────────────────────────────────────────────────────── --}}
<dialog class="modal fade" id="modalNewBox" tabindex="-1">
    <div class="modal-dialog">
        <form id="formNewBox">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('cashmanager::cash_manager.register_new_box') }}</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-card">
                        <div class="row">

                            {{-- Centro de Costo (solo visible cuando ya existe el VAULT) --}}
                            <div class="col-12 {{ $vaultExists ? '' : 'd-none' }}" id="box-cc-field">
                                <label class="primary_input_label" for="box_cost_center_id">
                                    {{ __('cashmanager::cash_manager.cost_center') }} <span class="text-danger">*</span>
                                </label>
                                <select name="cost_center_id" id="box_cost_center_id"
                                        class="primary_input_select"
                                        {{ $vaultExists ? 'required' : '' }}>
                                    <option value="">—</option>
                                    @foreach($costCenters as $cc)
                                        <option value="{{ $cc->id }}">{{ $cc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tipo determinado automáticamente (solo informativo) --}}
                            <div class="col-12 mt-15" id="box-type-info" style="display:none;">
                                <span class="d-block text-muted small mb-1">
                                    {{ __('cashmanager::cash_manager.box_type_auto') }}
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <span id="box-type-badge" class="badge_5" style="font-size:13px;"></span>
                                    <small id="box-type-hint" class="text-muted"></small>
                                </div>
                                {{-- Caja superior (informativa cuando aplica) --}}
                                <div id="box-parent-info" class="mt-2 d-none">
                                    <small class="text-muted">
                                        <i class="ti-arrow-up mr-1"></i>
                                        {{ __('cashmanager::cash_manager.box_parent') }}:
                                        <strong id="box-parent-name"></strong>
                                    </small>
                                </div>
                            </div>

                            {{-- Cargando tipo... --}}
                            <div class="col-12 mt-15 text-center text-muted small" id="box-type-loading" style="display:none;">
                                <i class="ti-reload mr-1"></i> Determinando tipo...
                            </div>

                            {{-- Campos de la caja (ocultos hasta seleccionar CC) --}}
                            <div id="box-fields" style="display:none;" class="col-12">
                                <div class="row">
                                    <div class="col-12 mt-15">
                                        <label class="primary_input_label" for="box_name">
                                            {{ __('cashmanager::cash_manager.box_name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="name" id="box_name" class="primary_input_field" required>
                                    </div>
                                    <div class="col-md-6 mt-15">
                                        <label class="primary_input_label" for="box_base">
                                            {{ __('cashmanager::cash_manager.initial_base') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="base_amount" id="box_base"
                                               class="primary_input_field" value="100000" min="0" required>
                                    </div>
                                    <div class="col-md-6 mt-15">
                                        <label class="primary_input_label" for="box_threshold">
                                            {{ __('cashmanager::cash_manager.alert_threshold') }}
                                            <small class="text-muted">({{ __('common.optional') }})</small>
                                        </label>
                                        <input type="number" name="alert_threshold" id="box_threshold"
                                               class="primary_input_field" min="0">
                                    </div>
                                </div>
                            </div>

                            {{-- Hint inicial --}}
                            <div class="col-12 mt-15 text-center text-muted small" id="box-select-cc-hint">
                                <i class="ti-info-alt mr-1"></i>
                                {{ __('cashmanager::cash_manager.select_cc_first') }}
                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" id="btn-save-box" class="btn-toolkit btn-primary" disabled>
                        {{ __('common.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
(function ($) {
    "use strict";

    const CSRF = "{{ csrf_token() }}";

    // ── Intercambiar botones de acción según tab activo ──────────────────────
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        $('#action_new_denomination').toggleClass('d-none', target !== '#tab-denominations');
        $('#action_new_box').toggleClass('d-none', target !== '#tab-structure');
    });

    // ── Toggle estado denominación ───────────────────────────────────────────
    $(document).on('change', '.denomination_status', function () {
        const id       = $(this).data('id');
        const isActive = $(this).is(':checked') ? 1 : 0;
        const $toggle  = this;

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url: "{{ route('cash_manager.settings.update_denomination_status') }}",
            type: 'POST',
            data: { _token: CSRF, id, is_active: isActive },
            success: function (res) { toastr.success(res.message); },
            error: function () {
                toastr.error("{{ __('common.something_wrong') }}");
                $($toggle).prop('checked', !isActive);
            },
            complete: function () { $('#pre-loader').addClass('d-none'); },
        });
    });

    // ── Eliminar denominación ────────────────────────────────────────────────
    $(document).on('click', '.btn-delete-denomination', function () {
        const id  = $(this).data('id');
        const row = $('#den-row-' + id);

        if (!confirm("{{ __('common.are_you_sure') }}")) return;

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url: "{{ url('cashmanager/settings/denominations') }}/" + id,
            type: 'DELETE',
            data: { _token: CSRF },
            success: function (res) {
                toastr.success(res.message);
                row.fadeOut(300, function () { $(this).remove(); });
            },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
            },
            complete: function () { $('#pre-loader').addClass('d-none'); },
        });
    });

    // ── Guardar nueva denominación ───────────────────────────────────────────
    $('#formNewDenomination').on('submit', function (e) {
        e.preventDefault();
        const $btn = $(this).find('[type=submit]').prop('disabled', true);

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url: "{{ route('cash_manager.settings.store_denomination') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message);
                $('#modalNewDenomination').modal('hide');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    Object.values(errors).flat().forEach(m => toastr.error(m));
                } else {
                    toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
                }
            },
            complete: function () {
                $('#pre-loader').addClass('d-none');
                $btn.prop('disabled', false);
            },
        });
    });

    const vaultExists = {{ $vaultExists ? 'true' : 'false' }};

    // ── Al abrir el modal: si no hay VAULT, resolver tipo automáticamente ───
    $('#modalNewBox').on('show.bs.modal', function () {
        if (!vaultExists) {
            $('#box-type-loading').show();
            $('#box-select-cc-hint').hide();
            $.ajax({
                url:  "{{ route('cash_manager.settings.next_box_type') }}",
                type: 'GET',
                success: function (res) { applyBoxType(res); },
                error:   function ()    { $('#box-select-cc-hint').show(); },
                complete: function ()   { $('#box-type-loading').hide(); },
            });
        }
    });

    const boxTypeHints = {
        'VAULT':     "{{ __('cashmanager::cash_manager.box_type_hint_vault') }}",
        'PRINCIPAL': "{{ __('cashmanager::cash_manager.box_type_hint_principal') }}",
        'AUXILIARY': "{{ __('cashmanager::cash_manager.box_type_hint_auxiliary') }}",
    };

    function applyBoxType(res) {
        const badgeClass = res.type === 'VAULT' ? 'badge_2' : (res.type === 'PRINCIPAL' ? 'badge_1' : 'badge_5');
        $('#box-type-badge').removeClass('badge_1 badge_2 badge_5').addClass(badgeClass).text(res.type_label);
        $('#box-type-hint').text(boxTypeHints[res.type] ?? '');

        if (res.parent_id) {
            $('#box-parent-name').text(res.parent_name);
            $('#box-parent-info').removeClass('d-none');
        } else {
            $('#box-parent-info').addClass('d-none');
        }

        $('#box-type-info').show();
        $('#box-fields').show();
        $('#btn-save-box').prop('disabled', false);
    }

    // ── Seleccionar CC → determinar tipo de caja automáticamente ────────────
    $('#box_cost_center_id').on('change', function () {
        const ccId = $(this).val();

        $('#box-type-info').hide();
        $('#box-fields').hide();
        $('#btn-save-box').prop('disabled', true);
        $('#box-select-cc-hint').hide();

        if (!ccId) {
            $('#box-select-cc-hint').show();
            return;
        }

        $('#box-type-loading').show();
        $.ajax({
            url:  "{{ route('cash_manager.settings.next_box_type') }}",
            type: 'GET',
            data: { cost_center_id: ccId },
            success: function (res) { applyBoxType(res); },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
                $('#box-select-cc-hint').show();
            },
            complete: function () { $('#box-type-loading').hide(); },
        });
    });

    // Resetear modal al cerrarlo
    $('#modalNewBox').on('hidden.bs.modal', function () {
        $('#formNewBox')[0].reset();
        $('#box-type-info').hide();
        $('#box-fields').hide();
        $('#box-type-loading').hide();
        $('#box-parent-info').addClass('d-none');
        $('#box-select-cc-hint').show();
        $('#btn-save-box').prop('disabled', true);
    });

    // ── Guardar nueva caja ───────────────────────────────────────────────────
    $('#formNewBox').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#btn-save-box').prop('disabled', true);

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url:  "{{ route('cash_manager.settings.store_box') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                toastr.success(res.message);
                $('#modalNewBox').modal('hide');
                setTimeout(() => location.reload(), 800);
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

    // ── Guardar roles de operador ────────────────────────────────────────────
    $('#formOperatorRoles').on('submit', function (e) {
        e.preventDefault();
        const $btn = $(this).find('[type=submit]').prop('disabled', true);

        // Recoger checkboxes marcados como array
        const roles = [];
        $(this).find('input[name="operator_role_ids[]"]:checked').each(function () {
            roles.push($(this).val());
        });

        if (roles.length === 0) {
            toastr.warning("{{ __('cashmanager::cash_manager.roles_min_one') }}");
            $btn.prop('disabled', false);
            return;
        }

        $('#pre-loader').removeClass('d-none');
        $.ajax({
            url: "{{ route('cash_manager.settings.save_operator_roles') }}",
            type: 'POST',
            data: { _token: CSRF, operator_role_ids: roles },
            success: function (res) { toastr.success(res.message); },
            error: function (xhr) {
                toastr.error(xhr.responseJSON?.message ?? "{{ __('common.something_wrong') }}");
            },
            complete: function () {
                $('#pre-loader').addClass('d-none');
                $btn.prop('disabled', false);
            },
        });
    });

})(jQuery);
</script>
@endpush
