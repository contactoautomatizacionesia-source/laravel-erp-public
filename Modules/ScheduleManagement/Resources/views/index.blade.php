@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-lg-12 mb-3">
                <div class="white_box_30px box_shadow_white">
                    <div class="main-title mb-3 d-md-flex justify-content-between">
                        <div class="d-md-flex align-items-center">
                            <x-backEnd.back-button :text="false" />
                            <h3 class="mb-0">
                                @if (isset($role_to_assign))
                                @lang('role_work_schedule.assign_schedule_to'): <span class="color-orange-toolkit">{{ $role_to_assign->name }}</span>
                                @else
                                @lang('hr.schedule_setup')
                                @endif
                            </h3>
                        </div>
                        @if (isset($role_to_assign))
                        <a href="{{ route('role_work_schedule.index') }}" class="btn-toolkit btn-secondary btn-icon btn-sm">
                             @lang('role_work_schedule.back_to_schedule_setup')<i class="ti-arrow-top-right"></i>
                        </a>
                        @endif
                    </div>

                    <form id="scheduleForm">
                        @csrf

                        @if (isset($role_to_assign))
                        <input type="hidden" name="role_id" id="current_role_id" value="{{ $role_to_assign->id }}">
                        <input type="hidden" name="role_name" id="current_role_name" value="{{ $role_to_assign->name }}">
                        @endif

                        @php
                        $categories = \Modules\ScheduleManagement\Entities\RoleWorkSchedule::getCategories();
                        @endphp

                        @foreach ($categories as $key => $label)
                        {{-- LÓGICA 1: Si es HOLIDAY y NO estamos asignando (estamos creando horarios globales), OCULTARLO --}}
                        @if ($key == 'HOLIDAY' && !isset($role_to_assign))
                        @continue
                        @endif

                        <div class="schedule-section" data-day-type="{{ $key }}">
                            <h3 class="text-black fs-18 border-bottom pb-2">{{ $label }}</h3>

                            {{-- LÓGICA 2: DISEÑO ESPECIAL PARA HOLIDAY (Solo Switch) --}}
                            @if ($key == 'HOLIDAY')
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <div class="d-flex align-items-center p-3">
                                        <label class="sch_m_switch mr-3">
                                            {{-- Cambiamos el name a 'ignore_holiday' para que NO se meta en el array de 'schedules' del backend --}}
                                            <input type="checkbox" name="holiday_dummy_input" value="1" id="holiday_access_switch"
                                                {{ isset($role_to_assign) && $role_to_assign->holiday_allowed == 1 ? 'checked' : '' }}>
                                            <span class="sch_m_slider round"></span>
                                        </label>
                                        <span class="font-weight-bold text-muted">@lang('role_work_schedule.holiday_access')</span>
                                    </div>
                                </div>
                            </div>

                            {{-- LÓGICA 3: DISEÑO ESTÁNDAR PARA EL RESTO (Tabla con horas) --}}
                            @else
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <div class="table-responsive ign-scrollbar">
                                        <table class="table text-center">
                                            <thead>
                                                <tr>
                                                    @if (isset($role_to_assign))
                                                    <th>@lang('common.select')</th>
                                                    @endif
                                                    <th>@lang('hr.start_time')</th>
                                                    <th>@lang('hr.end_time')</th>

                                                    @if (!isset($role_to_assign))
                                                    <th>@lang('common.status')</th>
                                                    <th>@lang('common.actions')</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody class="schedule_table_body" id="tbody_{{ $key }}">
                                                {{-- Se llena con JS --}}
                                            </tbody>
                                        </table>

                                        @if (!isset($role_to_assign))
                                        <div class="text-center my-3">
                                            <button type="button"
                                                class="btn-toolkit btn-ghost btn-icon add_row_btn"
                                                data-target="#tbody_{{ $key }}">
                                                <i class="ti-plus"></i> @lang('role_work_schedule.add_range')
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach

                        <div class="row justify-content-center mt-5">
                            <button type="button" id="save_changes_btn" class="btn-toolkit btn-primary btn-icon">
                                <i class="ti-check"></i> {{ __('common.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@include('backEnd.partials.delete_modal', ['item_name' => __('hr.schedule')])
@include('schedulemanagement::components._templates')
@endsection

@push('scripts')
<script>
    (function($) {
        "use strict";

        const allSchedules = @json($RoleWorkScheduleList ?? []);
        const isAssignmentMode = @json(isset($role_to_assign));

        let initialHolidayAllowed = null;
        let initialSchedulesState = {}; // Snapshot inicial
        let _rowToDelete = null;

        $(document).ready(function() {
            // Capturamos el estado inicial al cargar la página
            if (isAssignmentMode) {
                initialHolidayAllowed = $('#holiday_access_switch').is(':checked') ? 1 : 0;

                // Snapshot de radios seleccionados
                $('.sch_m_assignment_radio:checked').each(function() {
                    initialSchedulesState[$(this).attr('name')] = $(this).val();
                });
            }

            initSchedules();

            // Función para detectar cambios en Modo Asignación
            function hasAssignmentChanges() {
                let currentHoliday = $('#holiday_access_switch').is(':checked') ? 1 : 0;
                if (currentHoliday !== initialHolidayAllowed) return true;

                let currentSchedules = {};
                $('.sch_m_assignment_radio:checked').each(function() {
                    currentSchedules[$(this).attr('name')] = $(this).val();
                });

                // Comparamos cantidad de categorías seleccionadas
                let initialKeys = Object.keys(initialSchedulesState);
                let currentKeys = Object.keys(currentSchedules);

                if (initialKeys.length !== currentKeys.length) return true;

                // Comparamos valores específicos
                for (let key of initialKeys) {
                    if (initialSchedulesState[key] !== currentSchedules[key]) return true;
                }

                return false;
            }

            function initSchedules() {
                $('.schedule-section').each(function() {
                    let dayType = $(this).data('day-type');

                    // --- CASO ESPECIAL: HOLIDAY ---
                    if (dayType === 'HOLIDAY') {
                        return;
                    }

                    // --- CASO ESTÁNDAR ---
                    let targetBody = `#tbody_${dayType}`;
                    let schedules = allSchedules.filter(s => s.day_type == dayType);

                    if (isAssignmentMode) {
                        renderAssignmentRows(schedules, targetBody, dayType);
                    } else {
                        renderManagementRows(schedules, targetBody, dayType);
                    }
                });
            }

            function renderAssignmentRows(schedules, targetBody, dayType) {
                if (schedules.length === 0) {
                    $(targetBody).append(`<tr><td colspan="3" class="text-center text-muted">"{{ __('role_work_schedule.there_not_available_times_this_category') }}"</td></tr>`);
                    return;
                }

                schedules.forEach(schedule => {
                    if (schedule.is_active != 1) return;

                    let template = document.getElementById('assignmentRowTemplate').content.cloneNode(true);
                    let row = $(template).find('tr');

                    let radio = row.find('.sch_m_assignment_radio');
                    radio.attr('name', `schedules[${dayType}]`);
                    radio.val(schedule.id);

                    // LÓGICA DE PRE-SELECCIÓN
                    if (schedule.is_assigned) {
                        radio.prop('checked', true);
                        // Marcamos en data que está chequeado para permitir deselección luego
                        radio.data('was-checked', true);
                        row.addClass('sch_m_selected_row');
                    } else {
                        radio.data('was-checked', false);
                    }

                    // Obtenemos la hora, la recortamos a HH:mm y la formateamos a 12h
                    let start = schedule.start_time ? formatTimeTo12h(schedule.start_time.substring(0, 5)) : '';
                    let end = schedule.end_time ? formatTimeTo12h(schedule.end_time.substring(0, 5)) : '';

                    row.find('.start_time_text').text(start);
                    row.find('.end_time_text').text(end);

                    $(targetBody).append(row);
                });
            }

            function renderManagementRows(schedules, targetBody, dayType) {
                if (schedules.length === 0) {
                    addEmptyManagementRow(targetBody, dayType);
                    return;
                }

                schedules.forEach(schedule => {
                    let template = document.getElementById('managementRowTemplate').content.cloneNode(true);
                    let row = $(template.querySelector('tr'));
                    row.removeClass('new-row');

                    row.find('.schedule_id_input').val(schedule.id);
                    row.find('.day_type_input').val(schedule.day_type);

                    let start = schedule.start_time ? schedule.start_time.substring(0, 5) : '';
                    let end = schedule.end_time ? schedule.end_time.substring(0, 5) : '';

                    row.find('.start_time').val(start);
                    row.find('.end_time').val(end);

                    let isActive = (schedule.is_active == 1);
                    row.find('.status_switch').prop('checked', isActive);

                    if (!isActive) row.addClass('sch_m_disabled_row');

                    let deleteUrl = "{{ url('/schedule/destroy') }}/" + schedule.id;
                    row.find('.sch_m_delete_row').attr('data-value', deleteUrl);

                    $(targetBody).append(row);
                });
            }

            function addEmptyManagementRow(targetBody, dayType) {
                let template = document.getElementById('managementRowTemplate').content.cloneNode(true);
                $(template).find('.day_type_input').val(dayType);
                $(targetBody).append(template);
            }

            // --- EVENTOS ---

            $('.add_row_btn').on('click', function() {
                let targetBody = $(this).data('target');
                let dayType = $(this).closest('.schedule-section').data('day-type');
                addEmptyManagementRow(targetBody, dayType);
            });

            // ============================================================
            // NUEVA LÓGICA DE DESELECCIÓN (TOGGLE) PARA RADIOS
            // ============================================================
            $(document).on('click', '.sch_m_assignment_radio', function(e) {
                let $radio = $(this);
                let previousState = $radio.data('was-checked');

                if (previousState === true) {
                    // Si ya estaba seleccionado, lo DESELECCIONAMOS
                    $radio.prop('checked', false);
                    $radio.data('was-checked', false);
                    $radio.closest('tr').removeClass('sch_m_selected_row');
                } else {
                    // Si no estaba seleccionado, lo SELECCIONAMOS
                    $radio.prop('checked', true);
                    $radio.data('was-checked', true);
                    $radio.closest('tr').addClass('sch_m_selected_row');

                    // Importante: Debemos "limpiar" el estado de los otros radios del mismo grupo
                    // para que sepan que ya no están seleccionados
                    let groupName = $radio.attr('name');
                    $('input[name="' + groupName + '"]').not($radio).each(function() {
                        $(this).data('was-checked', false);
                        $(this).closest('tr').removeClass('sch_m_selected_row');
                    });
                }
            });

            $(document).on('change', '.status_switch', function() {
                let row = $(this).closest('tr');
                let isChecked = $(this).is(':checked');
                if (isChecked) {
                    row.removeClass('sch_m_disabled_row');
                } else {
                    row.addClass('sch_m_disabled_row');
                }
            });

            // Eliminación
            $(document).on('click', '.sch_m_delete_row', function(event) {
                let row = $(this).closest('tr');
                let id = row.find('.schedule_id_input').val();
                if (id) {
                    event.preventDefault();
                    let route = $(this).data('value');
                    _rowToDelete = row;
                    confirm_modal(route);
                } else {
                    row.remove();
                }
            });

            $(document).on('click', '#delete_link', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        toastr.success(res.message || 'Horario eliminado');
                        if (_rowToDelete) _rowToDelete.remove();
                        $('#confirm-delete').modal('hide');
                        _rowToDelete = null;
                    },
                    error: function(xhr) {
                        console.log(xhr)
                        let errorMessage = "{{ __('common.error_message') }}";
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        toastr.error(errorMessage);
                        $('#confirm-delete').modal('hide');
                    }
                });
            });

            // GUARDAR CAMBIOS (Lógica Paralela)
            $('#save_changes_btn').on('click', function(e) {
                e.preventDefault();

                if (isAssignmentMode) {
                    // VALIDACIÓN DE CAMBIOS ASIGNACIÓN
                    if (!hasAssignmentChanges()) {
                        toastr.info("{{ __('role_work_schedule.no_changes_detected') }}");
                        return;
                    }

                    $('#pre-loader').removeClass('d-none');
                    // --- MODO ASIGNACIÓN ---
                    let promises = [];

                    // 1. Promesa Horarios (FIX: Manejo de deselección)
                    let form = $('#scheduleForm');

                    // A. Obtenemos los datos como array para manipularlos
                    let formDataArray = form.serializeArray();

                    // B. Inyectamos valores vacíos para los días deseleccionados
                    $('.schedule-section').each(function() {
                        let dayType = $(this).data('day-type');
                        if (dayType === 'HOLIDAY') return; // Ignoramos festivos

                        // Verificamos si este día está presente en los datos serializados
                        let exists = formDataArray.some(item => item.name === `schedules[${dayType}]`);

                        // Si NO existe (porque está deseleccionado), lo agregamos vacío
                        if (!exists) {
                            formDataArray.push({
                                name: `schedules[${dayType}]`,
                                value: ''
                            });
                        }
                    });

                    promises.push($.ajax({
                        url: "{{ route('role_work_schedule.assign') }}",
                        type: "POST",
                        data: $.param(formDataArray)
                    }));

                    // 2. Promesa Festivos
                    let holidayAllowed = $('#holiday_access_switch').is(':checked') ? 1 : 0;

                    // SOLO agregamos la promesa si el valor cambió respecto al inicial
                    if (holidayAllowed !== initialHolidayAllowed) {
                        let roleId = $('#current_role_id').val();
                        let updateUrl = "{{ route('permission.roles.updateHolidayAllowed', ':id') }}";
                        updateUrl = updateUrl.replace(':id', roleId);

                        promises.push($.ajax({
                            url: updateUrl,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                holiday_allowed: holidayAllowed
                            }
                        }));
                    }

                    Promise.all(promises)
                        .then((results) => {
                            toastr.success("{{ __('role_work_schedule.updated') }}");
                            setTimeout(() => location.reload(), 1000);
                        })
                        .catch((err) => {
                            let msg = "{{ __('role_work_schedule.errors.error_message') }}";
                            if (err.responseJSON && err.responseJSON.message) {
                                msg = err.responseJSON.message;
                            }
                            toastr.error(msg);
                        })
                        .finally(() => {
                            $('#pre-loader').addClass('d-none');
                        });

                } else {
                    // --- MODO GESTIÓN GLOBAL ---
                    let promises = [];

                    $('.schedule_row').each(function() {
                        let row = $(this);

                        // PROTECCIÓN CONTRA HOLIDAYS (Fix del error ID 1)
                        if (row.closest('.schedule-section').data('day-type') === 'HOLIDAY') return;

                        let id = row.find('.schedule_id_input').val();
                        let day_type = row.find('.day_type_input').val();
                        let start_time = row.find('.start_time').val();
                        let end_time = row.find('.end_time').val();
                        let is_active = row.find('.status_switch').is(':checked') ? 1 : 0;

                        if (start_time && end_time) {
                            let data = {
                                _token: "{{ csrf_token() }}",
                                day_type: day_type,
                                start_time: start_time,
                                end_time: end_time,
                                is_active: is_active
                            };
                            if (id) {
                                promises.push($.ajax({
                                    url: "{{ route('role_work_schedule.update', '') }}/" + id,
                                    type: 'PUT',
                                    data: data
                                }));
                            } else {
                                promises.push($.ajax({
                                    url: "{{ route('role_work_schedule.store') }}",
                                    type: 'POST',
                                    data: data
                                }));
                            }
                        }
                    });

                    if (promises.length === 0) {
                        $('#pre-loader').addClass('d-none');
                        toastr.info('No hay cambios para guardar');
                        return;
                    }

                    Promise.all(promises).then(() => {
                        $('#pre-loader').addClass('d-none');
                        toastr.success("{{ __('role_work_schedule.updated_global_schedules') }}");
                        // setTimeout(() => location.reload(), 1000);
                    }).catch(() => {
                        $('#pre-loader').addClass('d-none');
                        toastr.error("{{ __('role_work_shedule.errors.error_message') }}");
                    });
                }
            });

            // Validaciones de hora bidireccionales
            $(document).on('change', '.end_time, .start_time', function() {
                let row = $(this).closest('tr');
                let start = row.find('.start_time').val();
                let end = row.find('.end_time').val();

                if (start && end && end <= start) {
                    // Usamos la variable de lenguaje de Laravel concatenada para JS
                    toastr.error("{{ __('role_work_schedule.errors.end_time_must_be_after_start_time') }}");

                    // Limpiamos el campo que acaba de cambiar para forzar la corrección
                    $(this).val('');
                }
            });

        });

        // Función para convertir formato 24h (14:00) a 12h (2:00 PM)
        function formatTimeTo12h(timeStr) {
            if (!timeStr) return '';
            let [hours, minutes] = timeStr.split(':');
            let period = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12; // Convierte 0 a 12 para medianoche
            return `${hours}:${minutes} ${period}`;
        }
    })(jQuery);
</script>
@endpush
