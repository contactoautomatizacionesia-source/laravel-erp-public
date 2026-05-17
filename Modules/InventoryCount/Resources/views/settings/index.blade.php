@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="">
    <div class="row">
        <div class="col-md-12 mb-10">

            <div class="box_header_right">
                <div class="pos_tab_btn justify-content-end">
                    <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                        @if(permissionCheck('inventory_count.settings.store'))
                        <li class="nav-item">
                            <a class="nav-link action" href="#" id="btnNewSetting"><i class="ti-plus mr-2"></i>{{ __('inventorycount::messages.new_settings') }}</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="box_header common_table_header ">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('inventorycount::menu.settings') }}</h3>
                </div>
            </div>
            <x-admin.table-container>
                <table id="settingsTable" class="table table-hover display text-center">
                    <thead>
                        <tr>
                            <th>{{ __('common.sl') }}</th>
                            <th>{{ __('inventorycount::messages.cost_center') }}</th>
                            <th>{{ __('inventorycount::messages.count_role') }}</th>
                            <th>{{ __('inventorycount::messages.max_attempts') }}</th>
                            <th>{{ __('inventorycount::messages.allow_history_view') }}</th>
                            <th>{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                </table>
            </x-admin.table-container>
        </div>
    </div>
</x-admin.section>

{{-- Modal Crear / Editar --}}
<dialog id="settingModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="settingModalTitle">{{ __('inventorycount::messages.new_settings') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <form id="settingsForm">
                    @csrf
                    <input type="hidden" name="cost_center_id" id="modalCostCenterId">

                    <div class="form-card">
                        <h3>{{__('common.information')}}</h3>
                        {{-- Selector de centro (solo al crear) --}}
                        <div class="form-group" id="costCenterGroup">
                            <label class="primary_input_label" for="costCenterSelect">{{ __('inventorycount::messages.select_cost_center') }} <span class="text-danger">*</span></label>
                            <select name="cost_center_id" id="costCenterSelect" class="primary_input_select" required>
                                <option value="">{{ __('common.select') }}...</option>
                                @foreach($costCenters as $cc)
                                <option value="{{ $cc->id }}">{{ $cc->name }} ({{ $cc->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nombre del centro (solo al editar, read-only) --}}
                        <div class="form-group d-none" id="costCenterNameGroup">
                            <label class="primary_input_label" for="costCenterNameDisplay">{{ __('inventorycount::messages.cost_center') }}</label>
                            <input type="text" id="costCenterNameDisplay" class="primary_input_field" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="primary_input_label" for="countRoleId">{{ __('inventorycount::messages.count_role') }} <span class="text-danger">*</span></label>
                                    <select name="count_role_id" id="countRoleId" class="primary_input_select" required>
                                        <option value="">{{ __('common.select') }}...</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="primary_input_help">{{ __('inventorycount::messages.count_role_hint') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="primary_input_label" for="maxAttempts">{{ __('inventorycount::messages.max_attempts') }}</label>
                                    <input type="number" name="max_attempts" id="maxAttempts" class="primary_input_field" min="0" max="255" value="0">
                                    <span class="primary_input_help" id="maxAttemptsHint">{{ __('inventorycount::messages.max_attempts_hint_zero') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="notifyUsersGroup">
                                <div class="form-group">
                                    <label class="primary_input_label" for="notifyUserIds">
                                        {{ __('inventorycount::messages.notify_users') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="notify_user_ids[]" id="notifyUserIds" class="primary_input_select" multiple>
                                        @foreach($adminUsers as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->first_name }} {{ $admin->last_name }} ({{ $admin->username }})</option>
                                        @endforeach
                                    </select>
                                    <span class="primary_input_help">{{ __('inventorycount::messages.notify_users_hint') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <x-backEnd.switch-toggle
                                    name="allow_history_view"
                                    id="allowHistoryView"
                                    :label="__('inventorycount::messages.allow_history_view')"
                                    :hint="__('inventorycount::messages.allow_history_view_hint')"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn-toolkit btn-primary btn-icon" id="saveSettingBtn">
                    <i class="ti-save mr-1"></i> {{ __('common.save') }}
                </button>
            </div>
        </div>
    </div>
</dialog>
{{-- </div> --}}

{{-- Modal confirmación history view --}}
<dialog id="historyViewConfirmModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger-toolkit">
                <h5 class="modal-title">{{ __('common.confirm') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <p>{{ __('inventorycount::messages.history_view_confirm') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" id="cancelHistoryView">{{ __('common.cancel') }}</button>
                <button type="button" class="btn-toolkit btn-danger" id="confirmHistoryView">{{ __('common.confirm') }}</button>
            </div>
        </div>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const ajaxUrl = '{{ route("inventory_count.settings.data") }}';

    initGlobalDataTable('#settingsTable', ajaxUrl, [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'cost_center_name', defaultContent: '' },
        { data: 'role_name', defaultContent: '' },
        { data: 'max_attempts_label', orderable: false, searchable: false },
        { data: 'allow_history_label', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false },
    ]);

    // ── Helpers del modal ──────────────────────────────────────────────────

    function resetModal() {
        $('#settingsForm')[0].reset();
        $('#modalCostCenterId').val('');
        $('#costCenterSelect').val('').prop('disabled', false);
        $('#costCenterGroup').removeClass('d-none');
        $('#costCenterNameGroup').addClass('d-none');
        $('#notifyUsersGroup').addClass('d-none');
        $('#maxAttemptsHint').text('{{ __("inventorycount::messages.max_attempts_hint_zero") }}');
        $('#notifyUserIds').val(null);
        allowHistoryPrev = false;
    }

    function toggleNotify() {
        const val = parseInt($('#maxAttempts').val()) || 0;
        if (val > 0) {
            $('#notifyUsersGroup').removeClass('d-none');
            $('#maxAttemptsHint').text('{{ __("inventorycount::messages.max_attempts_hint_limited") }}'.replace(':n', val));
        } else {
            $('#notifyUsersGroup').addClass('d-none');
            $('#maxAttemptsHint').text('{{ __("inventorycount::messages.max_attempts_hint_zero") }}');
        }
    }

    $('#maxAttempts').on('input change', toggleNotify);

    // ── Abrir modal: Nuevo ─────────────────────────────────────────────────

    $('#btnNewSetting').on('click', function () {
        resetModal();
        $('#settingModalTitle').text('{{ __("inventorycount::messages.new_settings") }}');
        $('#settingModal').modal('show');
    });

    // ── Abrir modal: Editar ────────────────────────────────────────────────

    $(document).on('click', '.edit-setting-btn', function () {
        $('#pre-loader').removeClass('d-none');
        const costCenterId = $(this).data('cost-center-id');
        resetModal();
        $('#settingModalTitle').text('{{ __("inventorycount::messages.edit_settings") }}');

        $.get('{{ route("inventory_count.settings.index") }}/' + costCenterId + '/edit', function (res) {
            if (!res.success) { toastr.error('{{ __("common.error") }}'); return; }

            const s = res.setting;

            // Ocultar selector, mostrar nombre
            $('#costCenterGroup').addClass('d-none');
            $('#costCenterSelect').prop('disabled', true);
            $('#costCenterNameGroup').removeClass('d-none');
            $('#costCenterNameDisplay').val(s ? s.cost_center_name : '');
            $('#modalCostCenterId').val(costCenterId);

            if (s) {
                $('#countRoleId').val(s.count_role_id);
                $('#maxAttempts').val(s.max_attempts);
                toggleNotify();
                if (s.notify_user_ids && s.notify_user_ids.length) {
                    $('#notifyUserIds').val(s.notify_user_ids);
                }
                $('#allowHistoryView').prop('checked', s.allow_history_view == 1);
                allowHistoryPrev = s.allow_history_view == 1;
            }

            $('#settingModal').modal('show');
            $('#pre-loader').addClass('d-none');
        }).fail(function () {
             $('#pre-loader').addClass('d-none');
             toastr.error('{{ __("common.error") }}');
            });
    });

    // ── Guardar ────────────────────────────────────────────────────────────

    $('#saveSettingBtn').on('click', function () {
        const maxAtt = parseInt($('#maxAttempts').val()) || 0;
        const notifyVals = $('#notifyUserIds').val();
        if (maxAtt > 0 && (!notifyVals || notifyVals.length === 0)) {
            toastr.warning('{{ __("inventorycount::messages.notify_required_when_limited") }}');
            return;
        }

        // Sincronizar hidden con select (solo crear)
        if (!$('#costCenterGroup').hasClass('d-none')) {
            $('#modalCostCenterId').val($('#costCenterSelect').val());
        }

        if (!$('#modalCostCenterId').val()) {
            toastr.warning('{{ __("inventorycount::messages.select_cost_center") }}');
            return;
        }

        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ route("inventory_count.settings.store") }}',
            method: 'POST',
            data: $('#settingsForm').serialize(),
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#settingModal').modal('hide');
                    $('#settingsTable').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(res.message);
                }
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, (k, v) => toastr.error(v[0]));
                } else {
                    toastr.error('{{ __("common.error") }}');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ti-save mr-1"></i> {{ __("common.save") }}');
            }
        });
    });

    // ── Confirmación allow_history_view ───────────────────────────────────

    let allowHistoryPrev = false;
    $('#allowHistoryView').on('change', function () {
        if ($(this).is(':checked') && !allowHistoryPrev) {
            $('#historyViewConfirmModal').modal('show');
        } else {
            allowHistoryPrev = false;
        }
    });
    $('#cancelHistoryView').on('click', function () {
        $('#allowHistoryView').prop('checked', false);
        $('#historyViewConfirmModal').modal('hide');
    });
    $('#confirmHistoryView').on('click', function () {
        allowHistoryPrev = true;
        $('#historyViewConfirmModal').modal('hide');
    });

});
</script>
@endpush
