@section('styles')

@endsection

<div class="modal fade" id="caseFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content h-100">
            <div class="modal-header">
                <h5 class="modal-title" id="caseFormModalLabel">{{ __('sanctions.create_new_case') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close "></i>
                </button>
            </div>
            <form id="caseForm" enctype="multipart/form-data">
                @csrf
                {{-- Modal content --}}
                <div class="modal-body py-2 px-md-4 px-2">

                    {{-- ==========================================
                        TAB Navigation panel
                    =========================================== --}}
                    <div class="form-card">
                        <div class="flex flex-wrap gap-2">
                            <div role="tab" aria-selected="false" data-step="0"
                                class="form-step flex-1 min-w-[80px] bg-transparent border-0 py-1 pb-1 text-center text-secondary transition-colors">
                                <span class="d-block fw-medium">{{ __('sanctions.identification') }}</span>
                            </div>
                            <div role="tab" aria-selected="false" data-step="1"
                                class="form-step flex-1 min-w-[80px] bg-transparent border-0  py-1 pb-1 text-center text-secondary transition-colors">
                                <span class="d-block fw-medium">{{ __('sanctions.facts') }}</span>
                            </div>
                            <div role="tab" aria-selected="false" data-step="2"
                                class="form-step flex-1 min-w-[80px] bg-transparent border-0 py-1 pb-1 text-center text-secondary transition-colors">
                                <span class="d-block fw-medium">{{ __('sanctions.classification') }}</span>
                            </div>
                            <div role="tab" aria-selected="false" data-step="3"
                                class="form-step flex-1 min-w-[80px] bg-transparent border-0 py-1 pb-1 text-center text-secondary transition-colors">
                                <span class="d-block fw-medium">{{ __('sanctions.evidence') }}</span>
                            </div>
                            <div role="tab" aria-selected="false" data-step="4"
                                class="form-step flex-1 min-w-[80px] bg-transparent border-0 py-1 pb-1 text-center text-secondary transition-colors">
                                <span class="d-block fw-medium">{{ __('sanctions.confirmation') }}</span>
                            </div>
                        </div>
                        <div class="mt-0">
                            <div class="progress" style="height: 2px;">
                                <div id="caseFormProgressBar" class="progress-bar bg-success" role="progressbar"
                                    style="width: 20%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="20">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB #1 IDENTIFICATION --}}
                    <div class="form-card" id="tab-step-0">
                        <h3><span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold mr-2"
                                style="width: 32px; height: 32px; background-color: #f48c6b22;">
                                1
                            </span>{{ __('sanctions.eui_identification_investigated') }}</h3>
                        <div class="border-bottom mb-3"></div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="investigated_eui_code" class="primary_input_label">
                                    {{ __('sanctions.investigated_eui_code') }} <span class="text-black">*</span>
                                </label>
                                <div class="d-flex gap-2">
                                    <input type="text" class="primary_input_field" name="investigated_eui_code"
                                        id="investigated_eui_code" required placeholder="COL-9415">
                                    <button class="primary_input_btn" type="button" id="searchUserBtn">
                                        <i class="ti-search"></i>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    </button>
                                </div>

                                {{-- Alerta dinamica --}}
                                <div id="userAlertContainer" class="mt-2">
                                    <div class="alert alert-primary d-flex align-items-center p-1" role="alert"
                                        style="
                                            border: none;
                                            border-left: 4px solid #0d6efd;
                                            border-radius: 0.5rem;
                                    ">
                                        <i class="ti-info-alt fs-3 me-3 mt-1"></i>
                                        <div>
                                            <small class="d-block"> {{ __('sanctions.eui_identification_helper') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="investigation_start_date" class="primary_input_label">
                                    {{ __('sanctions.investigation_start_date') }} <span class="text-black"> *</span>
                                </label>
                                <input type="date" class="primary_input_field" name="investigation_start_date"
                                    id="investigation_start_date" required>
                                <small class="text-muted small"> {{ __('sanctions.investigation_start_date_helper') }}
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="case_instructor" class="primary_input_label">
                                    {{ __('sanctions.case_instructor') }}<span class="text-black"> *</span>
                                </label>
                                <select class="nice-select-ajax primary_input_select" name="case_instructor"
                                    id="case_instructor" required>
                                    <option value="">{{ __('sanctions.select_instructor') }}</option>
                                    @foreach ($instructors as $instructor)
                                        <option value="{{ $instructor->id }}">
                                            {{ trim($instructor->first_name . ' ' . $instructor->last_name) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted small"> {{ __('sanctions.select_instructor_helper') }}
                                </small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="case_complaint_source" class="primary_input_label">
                                    {{ __('sanctions.case_complaint_source') }}<span class="text-black"> *</span>
                                </label>
                                <select class="nice-select-ajax primary_input_select" name="case_complaint_source"
                                    id="case_complaint_source" required>
                                    {{-- #TODO Get complaint sources from backend. BACKEND NOT IMPLEMENTED YET --}}
                                    <option value="">{{ __('sanctions.select_complaint_source') }}</option>
                                    @foreach ($complaintSources as $source)
                                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted small"> {{ __('sanctions.select_complaint_source_helper') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- TAB #2 FACTS --}}
                    <div class="form-card" id="tab-step-1">
                        <h3><span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold mr-2"
                                style="width: 32px; height: 32px; background-color: #f48c6b22;">
                                2
                            </span>{{ __('sanctions.facts_description') }}</h3>
                        <div class="border-bottom mb-3"></div>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <x-admin.textarea-counter name="facts_description" :label="__('sanctions.facts_description') . ' *'"
                                    id="facts_description" :placeholder="__('sanctions.facts_description_placeholder')" :min="10" :max="256"
                                    :rows="5" />
                                <div class="alert alert-primary d-flex align-items-center p-1 mt-2" role="alert"
                                    style="
                                        border: none;
                                        border-left: 4px solid #0d6efd;
                                        border-radius: 0.5rem;
                                ">
                                    <i class="ti-info-alt fs-5 me-3 mt-1"></i>
                                    <div>
                                        <small class="d-block"> {{ __('sanctions.alert_notification_info') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="incident_date" class="primary_input_label">
                                    {{ __('sanctions.incident_date') }}<span class="text-black"> *</span>
                                </label>
                                <input type="date" class="primary_input_field" name="incident_date"
                                    id="incident_date">
                                <small class="text-muted small"> {{ __('sanctions.incident_date_helper') }} </small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="sanction_additional_reference" class="primary_input_label">
                                    {{ __('sanctions.additional_reference') }} <span class="text-black"> *</span>
                                </label>
                                <input type="text" class="primary_input_field"
                                    name="sanction_additional_reference" id="sanction_additional_reference"
                                    placeholder="{{ __('sanctions.additional_reference_placeholder') }}" required>
                                <small class="text-muted small"> {{ __('sanctions.additional_reference_helper') }}
                                </small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label class="primary_input_label" for="sanction_policie_broken">
                                    {{ __('sanctions.policie_broken') }}<span class="text-black"> *</span>
                                </label>
                                <input type="text" class="primary_input_field" name="sanction_policie_broken"
                                    id="sanction_policie_broken"
                                    placeholder="{{ __('sanctions.policie_broken_placeholder') }}">
                                <small class="text-muted small"> {{ __('sanctions.policie_broken_helper') }} </small>
                            </div>
                        </div>
                    </div>

                    {{-- TAB #3 CLASSIFICATION --}}
                    <div class="form-card" id="tab-step-2">
                        <h3><span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold mr-2"
                                style="width: 32px; height: 32px; background-color: #f48c6b22;">
                                3
                            </span>{{ __('sanctions.classification_and_mitigation') }}</h3>
                        <div class="border-bottom mb-3"></div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="offence_scale_id" class="primary_input_label">
                                    {{ __('sanctions.offence_scale') }}<span class="text-black"> *</span>
                                </label>
                                <select class="nice-select-ajax primary_input_select" name="offence_scale_id"
                                    id="offence_scale_id" required>
                                    {{-- #TODO Get offence scales from backend --}}
                                    <option value="">{{ __('sanctions.select_offence_scale') }}</option>
                                    @foreach ($offenseTypes as $offense)
                                        <option value="{{ $offense->id }}">{{ $offense->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="sanction_scale" id="sanction_scale" value="">
                                <small class="text-muted small"> {{ __('sanctions.select_offence_scale_helper') }}
                                </small>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="accumulated_sanctions" class="primary_input_label">
                                    {{ __('sanctions.accumulated_sanctions') }}
                                </label>
                                <input type="text" class="primary_input_field" name="accumulated_sanctions"
                                    id="accumulated_sanctions" value="—" disabled>
                                <div class="alert alert-primary d-flex align-items-center mt-2 p-1" role="alert"
                                    style="
                                        border: none;
                                        border-left: 4px solid #0d6efd;
                                        border-radius: 0.5rem;
                                ">
                                    <i class="ti-info-alt fs-5 me-3 mt-1"></i>
                                    <div>
                                        <small class="d-block"> {{ __('sanctions.accumulated_sanctions_alert') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-bottom mb-3"></div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <h2>{{ __('sanctions.mitigations') }}</h2>
                            </div>
                            @foreach ($mitigatingFactors as $factor)
                                <div class="col-md-12 mb-2">
                                    <label class="d-flex align-items-start p-3 border rounded w-100 mb-0">
                                        <input type="checkbox" name="mitigating_circumstances[]"
                                            value="{{ $factor->id }}" class="me-3 mt-1 flex-shrink-0"
                                            data-description="{{ $factor->description }}">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark">{{ __('sanctions.mitigation_factor.' . $factor->code) }}</div>
                                            <div class="text-muted small">{{ __('sanctions.mitigation_factor.' . $factor->code . '_description') }}</div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- TAB #4 EVIDENCE --}}
                    <div class="form-card" id="tab-step-3">
                        <h3><span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold mr-2"
                                style="width: 32px; height: 32px; background-color: #f48c6b22;">
                                4
                            </span>{{ __('sanctions.evidence') }}</h3>
                        <div class="border-bottom mb-3"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="evidences"
                                    class="border rounded p-4 text-center bg-light position-relative d-block cursor-pointer"
                                    style="border-style: dashed;">

                                    <input type="file" name="evidences[]" id="evidences" class="d-none" multiple>

                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <i class="ti-clip fs-2 mb-2"></i>
                                        <div class="fw-semibold">{{ __('sanctions.evidences_load') }}</div>
                                        <small
                                            class="text-muted">{{ __('sanctions.evidences_allowed_files') }}</small>
                                    </div>
                                </label>
                                <div id="evidences_preview" class="mt-2"></div>
                                <small class="text-muted">{{ __('sanctions.evidences_load_helper') }}</small>
                            </div>
                        </div>
                        <div class="border-bottom mb-3"></div>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <x-admin.textarea-counter name="observations" :label="__('sanctions.observations')" id="observations"
                                    :placeholder="__('sanctions.observations_placeholder')" :min="0" :max="128" :rows="4" />
                            </div>
                        </div>
                    </div>

                    {{-- TAB #5 CONFIRMATION --}}
                    <div class="form-card" id="tab-step-4">
                        <h3><span
                                class="d-inline-flex align-items-center justify-content-center rounded-circle fw-bold mr-2"
                                style="width: 32px; height: 32px; background-color: #f48c6b22;">
                                5
                            </span>{{ __('sanctions.confirmation') }}</h3>
                        <div class="border-bottom mb-3"></div>

                        {{-- Identificación --}}
                        <div class="border rounded p-3 bg-white mb-3">
                            <div class="mb-3">
                                <h3>{{ __('sanctions.identification') }}</h3>
                                <div class="border-bottom mb-3"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.investigated_eui_code') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_eui_code">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.investigation_start_date') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_start_date">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.case_complaint_source') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_complaint_source">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.case_instructor') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_instructor">—</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hechos --}}
                        <div class="border rounded p-3 bg-white mb-3">
                            <div class="mb-3">
                                <h3>{{ __('sanctions.facts') }}</h3>
                                <div class="border-bottom mb-3"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.facts_description') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_facts">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.incident_date') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_incident_date">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.additional_reference') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_reference">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.policie_broken') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_policy">—</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Clasificación --}}
                        <div class="border rounded p-3 bg-white mb-3">
                            <div class="mb-3">
                                <h3>{{ __('sanctions.classification') }}</h3>
                                <div class="border-bottom mb-3"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.offence_scale') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_offence_scale">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.accumulated_sanctions') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_accumulated_sanctions">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.mitigations') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <ul id="conf_mitigations" class="mb-0 pl-3">—</ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Evidencia --}}
                        <div class="border rounded p-3 bg-white mb-3">
                            <div class="mb-3">
                                <h3>{{ __('sanctions.evidence') }}</h3>
                                <div class="border-bottom mb-3"></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.attached_files') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_files">—</strong>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="text-muted">
                                        <span>{{ __('sanctions.observations') }}</span>
                                    </div>
                                    <div class="text-dark">
                                        <strong id="conf_observations">—</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Modal footer --}}
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" id="btn_prev_step"
                        style="display:none;">
                        {{ __('sanctions.back') ?? 'Atrás' }}
                    </button>
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                        {{ __('sanctions.cancel') }}
                    </button>
                    <button type="button" class="btn-toolkit btn-primary" id="btn_next_step">
                        {{ __('sanctions.next') }}
                    </button>
                    <button type="submit" class="btn-toolkit btn-primary" id="btn_submit_case"
                        style="display:none;">
                        {{ __('sanctions.create_case') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function($) {
            $(document).ready(function() {

                var currentStep = 0;
                var totalSteps = 5;
                let user = null;

                // ─── Tab navigation ───────────────────────────────────────────────
                function showStep(step) {

                    const $steps = $('.form-step');
                    $('[id^="tab-step-"]').hide();
                    $('#tab-step-' + step).show();
                    const progress = Math.round(((step + 1) / totalSteps) * 100);

                    // ------------------------------------------------------- Actualizar clases de los steps -------------------------------------- //
                    $steps.each(function() {
                        const $step = $(this);
                        const numStep = parseInt($step.data('step'));

                        // ---------------------------------------------------- Limpieza de clases -------------------------------------------------- //
                        $step
                            .removeClass('text-success text-secondary')
                            .attr('aria-selected', 'false');

                        // ----------------------------------------------------- Asignacion de clases para steps completos ----------------------------- //
                        if (numStep <= step) {
                            $step
                                .addClass('text-success')
                                .attr('aria-selected', numStep === step ? 'true' : 'false');
                        } else {
                            // Si el botón está adelante del step actual, se marca como incompleto (gris)
                            $step
                                .addClass('text-secondary');
                        }
                    });

                    // ------------------------------------------------------- Actualizar botones del footer ----------------------------------------- //
                    $('#btn_prev_step').toggle(step > 0);
                    if (step === totalSteps - 1) {
                        $('#btn_next_step').hide();
                        $('#btn_submit_case').show();
                    } else {
                        $('#btn_next_step').show();
                        $('#btn_submit_case').hide();
                    }

                    // --------------------------------------------- Actualizar barra de progreso ----------------------------------------------------- //
                    $('#caseFormProgressBar')
                        .css('width', progress + '%')
                        .attr('aria-valuenow', progress);

                    if (step === totalSteps - 1) {
                        fillConfirmation();
                    }
                }

                // Clic en el botón de busqueda de usuario por EUI.
                $('#searchUserBtn').on('click', function() {

                    let btn = $(this);
                    let eui = $('#investigated_eui_code').val().trim();

                    if (!eui) {
                        eui = '';
                        toastr.warning('{{ __('sanctions.eui_code_required') }}');
                        renderAlert(null, eui);
                        return;
                    }

                    // #TODO: Implementar llamada AJAX para obtener el EUI
                    $.ajax({
                        url: '{{ route('sanctions.search_user') }}',
                        method: 'GET',
                        data: {
                            eui_code: eui
                        },
                        beforeSend: function() {
                            btn.prop('disabled', true);
                            btn.find('i').addClass('d-none');
                            btn.find('.spinner-border').removeClass('d-none');
                        },
                        success: function(response) {
                            if (response.success) {
                                user = response.data;
                                loadSanctionsByUser(user.sanctions_count);
                            } else {
                                user = null;
                            }
                            renderAlert(user, eui);
                        },
                        error: function() {
                            user = null;
                            renderAlert(user, eui);
                        },
                        complete: function() {
                            console.log('completed...')
                            btn.prop('disabled', false);
                            btn.find('i').removeClass('d-none');
                            btn.find('.spinner-border').addClass('d-none');
                        }
                    });
                });

                // Función para renderizar la alerta de usuario encontrado o no encontrado
                function renderAlert(user, eui) {
                    let html = '';

                    if (user) {
                        // ✅ Usuario encontrado
                        html = `
                            <div class="alert d-flex align-items-center p-1"
                                style="
                                    border: none;
                                    border-left: 4px solid #198754;
                                    border-radius: 0.5rem;
                                    background-color: #e9f7ef;
                                ">

                                <i class="ti-user fs-5 me-3"></i>

                                <div>
                                    <small class="d-block">
                                        <strong>${user.name}</strong><br>
                                        ${user.eui_code} - ${user.plan} - ${user.sanctions_count} {{ __('sanctions.accumulated_sanctions_by_user') }}
                                    </small>
                                </div>
                            </div>`;
                    } else {
                        // ❌ Usuario no encontrado
                        html = `
                            <div class="alert d-flex align-items-center p-1"
                                style="
                                    border: none;
                                    border-left: 4px solid #dc3545;
                                    border-radius: 0.5rem;
                                    background-color: #f8d7da;
                                ">

                                <i class="ti-alert fs-5 me-3 text-danger"></i>

                                <div>
                                    <small class="d-block text-danger">
                                        {{ __('sanctions.user_not_found') }} <strong>${eui}</strong>
                                    </small>
                                </div>
                            </div>`;
                    }

                    $('#userAlertContainer').html(html);
                }

                function loadSanctionsByUser(sanctions) {
                    $('#accumulated_sanctions').val(sanctions);
                }

                // Botón Siguiente
                $('#btn_next_step').on('click', function() {
                    if (!validateStep(currentStep)) return;
                    currentStep++;
                    if (currentStep >= totalSteps) currentStep = totalSteps - 1;
                    showStep(currentStep);
                });

                // Botón Atrás
                $('#btn_prev_step').on('click', function() {
                    currentStep--;
                    if (currentStep < 0) currentStep = 0;
                    showStep(currentStep);
                });

                // ─── Submit AJAX ──────────────────────────────────────────────────
                $('#caseForm').on('submit', function(e) {
                    e.preventDefault();

                    var formData = new FormData(this);
                    var $btn = $('#btn_submit_case');

                    $btn.prop('disabled', true).text('{{ __('sanctions.saving') ?? 'Guardando...' }}');

                    $.ajax({
                        url: '{{ route('sanctions.cases.store') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                toastr.success(res.message ||
                                    '{{ __('sanctions.case_created_successfully') }}');
                                $('#caseFormModal').modal('hide');
                                resetCaseForm();
                                if (window.casesTable) {
                                    window.casesTable.ajax.reload(null, false);
                                }
                            } else {
                                toastr.error(res.error || 'Error al crear el caso.');
                            }
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.error ?
                                xhr.responseJSON.error :
                                'Error inesperado. Intenta de nuevo.';
                            toastr.error(msg);
                        },
                        complete: function() {
                            $btn.prop('disabled', false).text(
                                '{{ __('sanctions.create_case') }}');
                        }
                    });
                });

                // ─── Validación por paso ──────────────────────────────────────────
                function validateStep(step) {
                    var ok = true;
                    if (step === 0) {
                        if (!$('#investigated_eui_code').val().trim()) {
                            toastr.warning('{{ __('sanctions.eui_code_required') }}');
                            ok = false;
                        }
                        if (!user) {
                            toastr.warning('{{ __('sanctions.investigated_user_required') }}');
                            ok = false;
                        }
                        if (!$('#investigation_start_date').val()) {
                            toastr.warning('{{ __('sanctions.investigation_start_date_required') }}');
                            ok = false;
                        }
                        if (!$('#case_instructor').val()) {
                            toastr.warning('{{ __('sanctions.instructor_required') }}');
                            ok = false;
                        }
                        if (!$('#case_complaint_source').val()) {
                            toastr.warning('{{ __('sanctions.complaint_source_required') }}');
                            ok = false;
                        }
                    } else if (step === 1) {
                        if (!$('#facts_description').val().trim()) {
                            toastr.warning('{{ __('sanctions.facts_description_required') }}');
                            ok = false;
                        }
                        if (!$('#incident_date').val()) {
                            toastr.warning('{{ __('sanctions.incident_date_required') }}');
                            ok = false;
                        }
                        if (!$('#sanction_policie_broken').val().trim()) {
                            toastr.warning('{{ __('sanctions.policy_broken_required') }}');
                            ok = false;
                        }
                        if (!$('#sanction_additional_reference').val().trim()) {
                            toastr.warning('{{ __('sanctions.additional_reference_required') }}');
                            ok = false;
                        }
                    } else if (step === 2) {
                        if (!$('#offence_scale_id').val()) {
                            toastr.warning('{{ __('sanctions.offence_scale_required') }}');
                            ok = false;
                        }
                    }
                    return ok;
                }

                // ─── Rellenar tab de confirmación ─────────────────────────────────
                function fillConfirmation() {
                    $('#conf_eui_code').text($('#investigated_eui_code').val() || '—');
                    $('#conf_start_date').text($('#investigation_start_date').val() || '—');
                    $('#conf_complaint_source').text(
                        $('#case_complaint_source option:selected').text() || '—'
                    );
                    $('#conf_instructor').text(
                        $('#case_instructor option:selected').text() || '—'
                    );
                    $('#conf_facts').text($('#facts_description').val() || '—');
                    $('#conf_incident_date').text($('#incident_date').val() || '—');
                    $('#conf_reference').text($('#sanction_additional_reference').val() || '—');
                    $('#conf_policy').text($('#sanction_policie_broken').val() || '—');
                    $('#conf_offence_scale').text(
                        $('#offence_scale_id option:selected').text() || '—'
                    );
                    $('#conf_accumulated_sanctions').text($('#accumulated_sanctions').val() || '—');
                    $('#conf_observations').text($('#observations').val() || '—');

                    // Atenuantes
                    var $list = $('#conf_mitigations').empty();
                    $('input[name="mitigating_circumstances[]"]:checked').each(function() {
                        $list.append('<li>' + $(this).data('description') + '</li>');
                    });
                    if ($list.children().length === 0) {
                        $list.append('<li>Ninguno</li>');
                    }

                    // Archivos
                    var files = document.getElementById('evidences').files;
                    if (files.length > 0) {
                        var names = [];
                        for (var i = 0; i < files.length; i++) names.push(files[i].name);
                        $('#conf_files').text(names.join(', '));
                    } else {
                        $('#conf_files').text('Sin archivos adjuntos');
                    }
                }

                // ─── Preview de archivos adjuntos ────────────────────────────────
                $('#evidences').on('change', function() {
                    var $preview = $('#evidences_preview').empty();
                    var files = this.files;
                    for (var i = 0; i < files.length; i++) {
                        $preview.append(
                            '<span class="badge badge-secondary mr-1 mb-1">' +
                            '<i class="ti-clip mr-1"></i>' + files[i].name + '</span>'
                        );
                    }
                });

                // ─── Reset al cerrar modal ────────────────────────────────────────
                $('#caseFormModal').on('hidden.bs.modal', function() {
                    resetCaseForm();
                });

                const defaultUserAlertHtml = `
                    <div class="alert alert-primary d-flex align-items-center p-1" role="alert"
                        style="
                            border: none;
                            border-left: 4px solid #0d6efd;
                            border-radius: 0.5rem;
                    ">
                        <i class="ti-info-alt fs-3 me-3 mt-1"></i>
                        <div>
                            <small class="d-block">{{ __('sanctions.eui_identification_helper') }}</small>
                        </div>
                    </div>`;

                function resetCaseForm() {
                    document.getElementById('caseForm').reset();
                    const userAlertContainer = document.getElementById('userAlertContainer');
                    if (userAlertContainer) {
                        userAlertContainer.innerHTML = defaultUserAlertHtml;
                    }
                    $('#evidences_preview').empty();
                    currentStep = 0;
                    showStep(0);
                    user = null;
                }

                // Inicial
                showStep(0);
            });
        })(jQuery);
    </script>
@endpush
