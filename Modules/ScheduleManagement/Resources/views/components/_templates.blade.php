{{-- TEMPLATE: Fila para Gestión (Inputs Editables) --}}
<template id="managementRowTemplate">
    <tr class="schedule_row management-row">
        <td>
            <div class="primary_input">
                <input type="time" class="primary_input_field form-control start_time" required>
                <input type="hidden" class="day_type_input">
                <input type="hidden" class="schedule_id_input" value="">
            </div>
        </td>
        <td>
            <div class="primary_input">
                <input type="time" class="primary_input_field form-control end_time" required>
            </div>
        </td>
        <td>
            {{-- Usamos las nuevas clases sch_m_switch y sch_m_slider --}}
            <label class="sch_m_switch">
                <input type="checkbox" class="status_switch" checked value="1">
                <span class="sch_m_slider round"></span>
                <span class="hidden">select</span>
            </label>
        </td>
        <td>
            {{-- Usamos la nueva clase sch_m_delete_row --}}
            <a href="javascript:void(0)" class="sch_m_delete_row">
                <i class="ti-trash"></i>
            </a>
        </td>
    </tr>
</template>

{{-- TEMPLATE: Fila para Asignación (Solo Lectura + Radio) --}}
<template id="assignmentRowTemplate">
    <tr class="schedule_row sch_m_assignment_row">
        <td class="">
            {{-- Usamos la nueva clase sch_m_assignment_radio --}}
            <input type="radio" class="sch_m_assignment_radio" value="">
        </td>
        <td>
            <span class="start_time_text pl-3 font-weight-bold"></span>
        </td>
        <td>
            <span class="end_time_text pl-3 font-weight-bold"></span>
        </td>
    </tr>
</template>
