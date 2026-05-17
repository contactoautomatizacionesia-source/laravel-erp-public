@extends('backEnd.master')

@section('mainContent')
<x-admin.section class="ign-customer-list">
    <div class="row">
        <div class="col-12">
            <div class="box_header common_table_header">
                <div class="main-title">
                    <h3 class="mb-0">{{ __('cycleclosure::menu.settings') }}</h3>
                </div>
            </div>

            
                <div class=" py-3">

                    {{-- Config activa actual --}}
                    @if($activeSetting)
                    <div class="alert alert-success mb-4">
                        <i class="ti-check mr-1"></i>
                        <strong>{{ __('cycleclosure::messages.settings_active') }}:</strong>
                        {{ __('cycleclosure::messages.period_' . $activeSetting->period_type) }}
                        @if($activeSetting->execution_day)
                            — {{ __('cycleclosure::messages.day') }}
                            <strong>{{ $activeSetting->execution_day }}</strong>
                        @endif
                        — {{ __('cycleclosure::messages.executor_label') }}: <strong>{{ optional($activeSetting->executor)->name ?? '—' }}</strong>
                        — {{ __('cycleclosure::messages.double_approval_label') }}: <strong>{{ optional($activeSetting->approver)->name ?? '—' }}</strong>
                        <span class="text-muted small ml-2">({{ $activeSetting->created_at->format('d/m/Y H:i') }})</span>
                    </div>
                    @endif

                    {{-- ─── Sección 1: Parámetros ────────────────────────────── --}}
                    @if(permissionCheck('cycle_closure.settings.store'))
                    <form action="{{ route('cycle_closure.settings.store') }}" method="POST" id="settingsForm">
                        @csrf
                        <input type="hidden" name="confirm" id="settingsConfirm" value="0">

                        <div class="form-card">
                            <h3>{{ __('cycleclosure::menu.settings') }}</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="primary_input_label" for="periodType">
                                            {{ __('cycleclosure::messages.period_type_label') }} <span class="text-danger">*</span>
                                        </label>
                                        <select name="period_type" id="periodType" class="primary_input_select" required>
                                            <option value="">{{ __('common.select') }}...</option>
                                            <option value="daily"   {{ old('period_type') === 'daily'   ? 'selected' : '' }}>{{ __('cycleclosure::messages.period_daily') }}</option>
                                            <option value="monthly" {{ old('period_type', 'monthly') === 'monthly' ? 'selected' : '' }}>{{ __('cycleclosure::messages.period_monthly') }}</option>
                                            <option value="annual"  {{ old('period_type') === 'annual'  ? 'selected' : '' }}>{{ __('cycleclosure::messages.period_annual') }}</option>
                                        </select>
                                        @error('period_type')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3" id="executionDayGroup" style="display:none;">
                                    <div class="form-group">
                                        <label class="primary_input_label" for="executionDay">
                                            {{ __('cycleclosure::messages.execution_day_label') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="execution_day" id="executionDay"
                                            class="primary_input_field" min="1" max="31"
                                            value="{{ old('execution_day', $activeSetting?->execution_day) }}">
                                        <small class="text-muted" id="day31Note" style="display:none;">
                                            {{ __('cycleclosure::messages.day_31_note') }}
                                        </small>
                                        @error('execution_day')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                        <div class="form-card">
                            <h3>{{ __('cycleclosure::menu.responsibles') }}</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="primary_input_label" for="executorUserId">
                                            {{ __('cycleclosure::messages.executor_label') }} <span class="text-danger">*</span>
                                        </label>
                                        <select name="executor_user_id" id="executorUserId" class="primary_input_select" required>
                                            <option value="">{{ __('common.select') }}...</option>
                                            @foreach($executors as $exec)
                                            <option value="{{ $exec->id }}"
                                                {{ old('executor_user_id', $activeSetting?->executor_user_id) == $exec->id ? 'selected' : '' }}>
                                                {{ $exec->name }} ({{ $exec->email }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <span class="primary_input_help">{{ __('cycleclosure::messages.executor_help') }}</span>
                                        @error('executor_user_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="primary_input_label" for="approverUserId">
                                            {{ __('cycleclosure::messages.double_approval_label') }} <span class="text-danger">*</span>
                                        </label>
                                        <select name="approver_user_id" id="approverUserId" class="primary_input_select" required>
                                            <option value="">{{ __('common.select') }}...</option>
                                            @foreach($approvers as $approver)
                                            <option value="{{ $approver->id }}"
                                                {{ old('approver_user_id', $activeSetting?->approver_user_id) == $approver->id ? 'selected' : '' }}>
                                                {{ $approver->name }} ({{ $approver->email }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <span class="primary_input_help">{{ __('cycleclosure::messages.approver_help') }}</span>
                                        @error('approver_user_id')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        

                        <div class="form-card">
                            <h3>{{ __('cycleclosure::menu.server') }}</h3>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="primary_input_label" for="cronCommand">{{ __('cycleclosure::messages.cron_command_label') }}</label>
                                        @php
                                            $cronWorkdir = (string) config('cycleclosure.cron_workdir');
                                            $cronPhp     = (string) config('cycleclosure.cron_php');
                                            $cronOutput  = (string) config('cycleclosure.cron_output');
                                            $cronCommand = 'cd "' . $cronWorkdir . '" && ' . $cronPhp . ' artisan schedule:run >> ' . $cronOutput . ' 2>&1';
                                        @endphp
                                        <div class="input-group">
                                            <input type="text" class="primary_input_field font-monospace flex-1 mr-2" readonly
                                                id="cronCommand"
                                                value="{{ $cronCommand }}">
                                            <div class="input-group-append">
                                                <button class="btn-toolkit btn-secondary-outline btn-sm btn-icon" type="button" id="btnCopyCron"
                                                        title="{{ __('cycleclosure::messages.copy') }}"
                                                        aria-label="{{ __('cycleclosure::messages.copy') }}">
                                                    <i class="ti-files"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ─── Sección 2: Banner de advertencia ─────────────── --}}
                        <div class="alert border mb-4 mt-2 rounded"
                             style="background:#fffbea; border-color:#ffefaa  !important; border-left: 4px solid #eea356  !important;">
                            <div class="d-flex align-items-start">
                                <span style="font-size:1.8rem; line-height:1; margin-right:12px;">⚠️</span>
                                <div>
                                    <h6 class="font-weight-bold fs-18 mb-1 text-black">{{ __('cycleclosure::messages.settings_warning_title') }}</h6>
                                    <p class="mb-1 ">
                                        {{ __('cycleclosure::messages.settings_warning_body') }}
                                    </p>
                                    <p class="mb-0 ">
                                        <strong>{{ __('cycleclosure::messages.next_closure_scheduled_for') }}</strong>
                                        <span id="nextClosurePreview" class="font-weight-bold text-danger">—</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        

                        {{-- ─── Botón Guardar ─────────────────────────────────── --}}
                        <div class="text-center mt-3 mb-10">
                            <button type="button" class="btn-toolkit btn-danger" id="btnSaveSettings">
                                <i class="ti-alert mr-1"></i> {{ __('common.save') }}
                            </button>
                        </div>
                    </form>
                    @endif

                    {{-- ─── Historial de configuraciones ──────────────────────── --}}
                    <div class="form-card">
                        <h3 class="">{{ __('cycleclosure::messages.settings_history_title') }}</h3>
                        <x-admin.table-container>
                            <div class="table-responsive ign-scrollbar">
                                <table id="settingsHistoryTable" class="table dataTable table-hover display text-center w-100">
                                    <thead class="thead-light">
                                        <tr>
                                        <th>{{ __('cycleclosure::messages.period_type_label') }}</th>
                                        <th>{{ __('cycleclosure::messages.execution_day_label') }}</th>
                                        <th>{{ __('cycleclosure::messages.executor_label') }}</th>
                                        <th>{{ __('cycleclosure::messages.double_approval_label') }}</th>
                                        <th>{{ __('cycleclosure::messages.settings_configured_by') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('common.date') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </x-admin.table-container>
                    </div>

                </div>
            
        </div>
    </div>
</x-admin.section>

{{-- Modal confirmación guardar config --}}
<dialog id="saveSettingConfirmModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="ti-alert mr-1"></i> {{ __('cycleclosure::messages.confirm_settings_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                <p>{{ __('cycleclosure::messages.confirm_settings_executor_prefix') }}
                    <strong id="executorNameInModal">—</strong>.
                </p>
                <p>{{ __('cycleclosure::messages.confirm_settings_body_prefix') }}
                    <strong id="approverNameInModal">—</strong>
                    {{ __('cycleclosure::messages.confirm_settings_body_suffix') }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('common.cancel') }}
                </button>
                <button type="button" class="btn-toolkit btn-primary" id="btnConfirmSave">
                    <i class="ti-check mr-1"></i> {{ __('cycleclosure::messages.confirm_and_save') }}
                </button>
            </div>
        </div>
    </div>
</dialog>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const i18n = {
        cronCopied: @json(__('cycleclosure::messages.cron_copied')),
        cronCopyFailed: @json(__('cycleclosure::messages.cron_copy_failed')),
        selectExecutor: @json(__('cycleclosure::messages.select_executor_warning')),
        selectApprover: @json(__('cycleclosure::messages.select_approver_warning')),
    };

    function toast(type, message) {
        if (window.toastr && typeof toastr[type] === 'function') {
            toastr[type](message);
            return;
        }
        if (type === 'error') {
            console.error(message);
        } else {
            console.log(message);
        }
    }

    function fallbackCopyFromInput(input) {
        if (!input || typeof document.execCommand !== 'function') {
            toast('error', i18n.cronCopyFailed);
            return;
        }
        input.focus();
        input.select();
        input.setSelectionRange(0, input.value.length);
        const ok = document.execCommand('copy');
        input.setSelectionRange(0, 0);
        if (ok) {
            toast('success', i18n.cronCopied);
        } else {
            toast('error', i18n.cronCopyFailed);
        }
    }

    // ── Mostrar/ocultar día de ejecución ───────────────────────────────────
    function toggleExecutionDay() {
        const period = $('#periodType').val();
        if (period === 'monthly') {
            $('#executionDayGroup').slideDown(200);
        } else {
            $('#executionDayGroup').slideUp(200);
            $('#executionDay').val('');
            $('#day31Note').hide();
        }
        updateNextClosure();
    }

    $('#periodType').on('change', toggleExecutionDay);
    toggleExecutionDay();

    // ── Nota para día 31 ──────────────────────────────────────────────────
    $('#executionDay').on('input', function () {
        const val = parseInt($(this).val());
        $('#day31Note').toggle(val === 31);
        updateNextClosure();
    });

    // ── Preview próximo cierre ─────────────────────────────────────────────
    function updateNextClosure() {
        const period = $('#periodType').val();
        const day    = parseInt($('#executionDay').val()) || 1;

        if (!period) { $('#nextClosurePreview').text('—'); return; }

        const today = new Date();
        let nextDate;

        if (period === 'monthly') {
            const year  = today.getFullYear();
            const month = today.getMonth();
            const daysInMonth  = new Date(year, month + 1, 0).getDate();
            const actualDay    = Math.min(day, daysInMonth);
            const candidate    = new Date(year, month, actualDay);

            if (candidate <= today) {
                const nextMonth     = month + 1;
                const daysInNext    = new Date(year, nextMonth + 1, 0).getDate();
                const nextActualDay = Math.min(day, daysInNext);
                nextDate = new Date(year, nextMonth, nextActualDay);
            } else {
                nextDate = candidate;
            }
        } else if (period === 'daily') {
            nextDate = new Date(today);
            nextDate.setDate(nextDate.getDate() + 1);
        } else if (period === 'annual') {
            nextDate = new Date(today.getFullYear() + 1, 0, 1);
        }

        if (nextDate) {
            const d = String(nextDate.getDate()).padStart(2, '0');
            const m = String(nextDate.getMonth() + 1).padStart(2, '0');
            const y = nextDate.getFullYear();
            $('#nextClosurePreview').text(d + ' / ' + m + ' / ' + y);
        } else {
            $('#nextClosurePreview').text('—');
        }
    }

    updateNextClosure();

    // ── Copiar comando cron ────────────────────────────────────────────────
    $('#btnCopyCron').on('click', function () {
        const input = document.getElementById('cronCommand');
        const txt = input ? input.value : '';
        if (!txt) { toast('error', i18n.cronCopyFailed); return; }

        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(txt).then(function () {
                toast('success', i18n.cronCopied);
            }).catch(function () {
                fallbackCopyFromInput(input);
            });
            return;
        }
        fallbackCopyFromInput(input);
    });

    // ── Guardar: abrir modal de confirmación ───────────────────────────────
    $('#btnSaveSettings').on('click', function () {
        $('#settingsConfirm').val('0');
        if (!$('#executorUserId').val()) {
            toast('warning', i18n.selectExecutor);
            return;
        }
        if (!$('#approverUserId').val()) {
            toast('warning', i18n.selectApprover);
            return;
        }
        $('#executorNameInModal').text($('#executorUserId option:selected').text());
        $('#approverNameInModal').text($('#approverUserId option:selected').text());
        $('#saveSettingConfirmModal').modal('show');
    });

    // ── Confirmar y enviar form ────────────────────────────────────────────
    $('#btnConfirmSave').on('click', function () {
        $('#saveSettingConfirmModal').modal('hide');
        $('#settingsConfirm').val('1');
        $('#settingsForm').submit();
    });

    $('#settingsForm').on('submit', function (e) {
        if ($('#settingsConfirm').val() === '1') {
            return;
        }

        e.preventDefault();
        $('#btnSaveSettings').trigger('click');
    });

    // ── Historial de configuraciones ──────────────────────────────────────
    const historyAjaxUrl = '{{ route('cycle_closure.settings.data') }}';

    initGlobalDataTable('#settingsHistoryTable', historyAjaxUrl, [
        { data: 'period_type_label',   name: 'period_type' },
        { data: 'execution_day_col',   name: 'execution_day', orderable: false, searchable: false },
        { data: 'executor_name',       name: 'executor_user_id', orderable: false, searchable: false },
        { data: 'approver_name',       name: 'approver_user_id', orderable: false, searchable: false },
        { data: 'configured_by_name',  name: 'configured_by', orderable: false, searchable: false },
        { data: 'status_badge',        name: 'status', orderable: false, searchable: false },
        { data: 'saved_at',            name: 'created_at', orderable: true, searchable: false },
    ], {
        order: [[6, 'desc']],
    });

});
</script>
@endpush
