@extends('backEnd.master')

@section('mainContent')
<x-admin.section >
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link action" href="#" id="btn_create_benefit">
                                    <i class="ti-plus mr-2"></i> {{ __('common.new_benefit') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mb_xs_15px mb_sm_20px">{{ __('common.benefits_catalog') }}</h3>
                        <x-admin.help id="benefits_help">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <p class="text-black">Modulo para la gestion de los beneficios</p>
                                </div>
                                <div class="col-12">
                                    <ul>
                                        <li>
                                            <p class="text-black">| <strong>{{ __('common.category') }}:</strong> Indica a que tipo pertenece e beneficio.</p>
                                        </li>
                                        <li>
                                            <p class="text-black">| <strong>{{ __('common.Subplans') }}:</strong> Indica el numero de niveles que tiene el beneficio.</p>
                                        </li>
                                        <li>
                                            <p class="text-black">| <strong>{{ __('common.cumulative') }}:</strong> Indica si un beneficio puede acumularse con otro.</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </x-admin.help>
                    </div>
                </div>

                <div class="QA_section QA_section_heading_custom check_box_table">
                    <div class="QA_table">
                        <div class="table-responsive ign-scrollbar">
                            <table id="benefitsTable" class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>{{ __('common.code') }}</th>
                                        <th>{{ __('common.title') }}</th>
                                        <th>{{ __('common.category') }}</th>
                                        <th>{{ __('common.Subplans') }}</th>
                                        <th>{{ __('common.cumulative') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('common.updated') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</x-admin.section>

@include('plans::benefits.components.form_modal', ['categories' => $categories])
@include('plans::benefits.components.show_modal')
@include('backEnd.partials.delete_modal', ['item_name' => __('common.benefit')])


@endsection

@push('scripts')
@include('components.admin.multiselect-engine')
@include('plans::components.utils')
<script type="text/javascript">
(function ($) {
    "use strict";
    $('#open-help').on('click', function () {
        $('#help_modal').modal('show');
    })
    var getTranslatedValue  = window.plansUtils.getTranslatedValue;
    var resolveAnswerDisplay = window.plansUtils.resolveAnswerDisplay;

    const benefitCategoryDescriptions = {
        @foreach($categories as $cat)
        {{ $cat->id }}: @json($cat->description),
        @endforeach
    };

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    let table;
    let loadingEditBenefit = false;

    $(document).ready(function () {

        // ── DataTable ──────────────────────────────────────────
        let ajaxUrl = "{{ route('plans.benefits.get-data') }}";
        var columnData = [
                { data: 'DT_RowIndex', name: 'id', searchable: false, orderable: false },
                { data: 'code', name: 'code' },
                { data: 'title', name: 'title' },
                { data: 'category_badge', name: 'category_badge', orderable: false, searchable: false },
                { data: 'plans_badge', name: 'plans_badge', orderable: false, searchable: false },
                { data: 'cumulative_badge', name: 'cumulative_badge', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status_badge', searchable: false, orderable: false },
                { data: 'updated_at_formatted', name: 'updated_at', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ];
        table = initGlobalDataTable('#benefitsTable', ajaxUrl , columnData);

        // ── Crear ──────────────────────────────────────────────
        $('#btn_create_benefit').on('click', function (e) {
            e.preventDefault();
            resetBenefitForm();
            $('#benefitFormModalLabel').text('{{ __('common.new_benefit') }}');
            
            // Generate code automatically
            $('#b_code').val('{{ __('common.loading') }}...').prop('readonly', true);
            $.get("{{ route('plans.benefits.next-code') }}", function(res) {
                $('#b_code').val(res.code);
            }).fail(function() {
                $('#b_code').val('').prop('readonly', false);
            });

            $('#benefitFormModal').modal('show');
        });

        // ── Guardar / Actualizar ───────────────────────────────
        $('#benefitForm').on('submit', function (e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');

            let id  = $('#benefit_id').val();
            let url = id
                ? `/plans/benefits/${id}/update`
                : "{{ route('plans.benefits.store') }}";

            // Convert multiselect native <select> to a single JSON hidden input before serializing
            $('#benefit-dynamic-form .ign-multiselect-wrapper').each(function () {
                let msName = $(this).data('name');
                let $sel   = $(this).find('select');
                let vals   = this._ignMs ? this._ignMs.getSelected() : $sel.val() || [];
                $sel.prop('disabled', true);
                $(this).append($('<input type="hidden">').attr('name', msName).val(vals.length ? JSON.stringify(vals) : ''));
            });

            let formData = $(this).serialize();

            // Restore selects and remove temp hidden inputs
            $('#benefit-dynamic-form .ign-multiselect-wrapper').each(function () {
                $(this).find('select').prop('disabled', false);
                $(this).find('input[type="hidden"][name^="answers"]').remove();
            });

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message, "{{ __('common.success') }}");
                        $('#benefitFormModal').modal('hide');
                        table.ajax.reload();
                    }
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.error || "{{ __('common.server_error') }}", "{{ __('common.error') }}");
                },
                complete: function () { $('#pre-loader').addClass('d-none'); }
            });
        });
        
        // ── Editar ─────────────────────────────────────────────
        $(document).on('click', '.edit_benefit', function (e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            let id = $(this).data('value');

            resetBenefitForm();
            $('#b_code').prop('readonly', false); // Allow edit

            $.get(`/plans/benefits/${id}/edit`, function (benefit) {
                $('#benefit_id').val(benefit.id);
                $('#b_code').val(benefit.code);
                $("#b_title").val(getTranslatedValue(benefit.title, ''));
                $('#b_description').val(getTranslatedValue(benefit.description, ''));
                $('#b_is_cumulative').prop('checked', !!benefit.is_cumulative);
                $('#b_is_active').prop('checked', !!benefit.is_active);
                loadingEditBenefit = true;
                $('#benefit_category_id').val(benefit.benefit_category_id).trigger('change');
                loadingEditBenefit = false;

                loadBenefitFormStructure(benefit.benefit_category_id, function () {
                    populateBenefitAnswers(benefit.form_answers);
                });

                $('#benefitFormModalLabel').text('{{ __('common.edit_benefit') }}');
                $('#benefitFormModal').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });

        // ── Ver detalles ───────────────────────────────────────
        $(document).on('click', '.show_benefit', function (e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            $.get(`/plans/benefits/${$(this).data('value')}/show`, function (benefit) {
                renderBenefitShowModal(benefit);
                $('#benefitShowModal').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });

        // ── Eliminar ───────────────────────────────────────────
        $(document).on('click', '.delete_benefit', function (e) {
            e.preventDefault();
            confirm_modal(`/plans/benefits/${$(this).data('value')}/destroy`);
        });

        $(document).on('click', '#delete_link', function (e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            $.ajax({
                url: $(this).attr('href'),
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    toastr.success(res.message || "{{ __('common.deleted_successfully') }}", "{{ __('common.success') }}");
                    $('#confirm-delete').modal('hide');
                    table.ajax.reload();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.error || "{{ __('common.error') }}", "{{ __('common.error') }}");
                    $('#confirm-delete').modal('hide');
                },
                complete: function () { $('#pre-loader').addClass('d-none'); }
            });
        });

        // ── Cambio de categoría → cargar formulario dinámico + descripción ──
        $('#benefit_category_id').on('change', function () {
            const catId = $(this).val();
            const desc = benefitCategoryDescriptions[catId];
            const text = desc ? getTranslatedValue(desc, '') : '';
            if (text) {
                $('#benefit-category-description-text').text(text);
                $('#benefit-category-description').removeClass('d-none');
            } else {
                $('#benefit-category-description').addClass('d-none');
            }
            if (!loadingEditBenefit) {
                loadBenefitFormStructure(catId);
            }
        });

        // ── Wizard ────────────────────────────────────────────
        $('#b-btn-next').on('click', function () {
            let valid = true;
            $('#b-step-1 :input[required]').each(function () {
                if (!this.checkValidity()) { this.reportValidity(); valid = false; return false; }
            });
            if (!valid) return;
            goBenefitStep(2);
        });

        $('#b-btn-prev').on('click', function () { goBenefitStep(1); });

    }); // end ready

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    function goBenefitStep(step) {
        $('.b-wizard-step').addClass('d-none');
        $('#b-step-' + step).removeClass('d-none');
        $('.b-step-header').removeClass('active');
        $(`.b-step-header[data-step="${step}"]`).addClass('active');

        if (step === 1) {
            $('#b-btn-prev').addClass('d-none');
            $('#b-btn-next').removeClass('d-none');
            $('#b-btn-save').addClass('d-none');
        } else {
            $('#b-btn-prev').removeClass('d-none');
            $('#b-btn-next').addClass('d-none');
            $('#b-btn-save').removeClass('d-none');
        }
    }

    function resetBenefitForm() {
        $('#benefitForm')[0].reset();
        $('#benefit_id').val('');
        $('#benefit-dynamic-form').html(
            '<div class="text-center text-muted py-4"><i class="ti-info-alt mr-1"></i> {{ __('common.select_category_hint') }}</div>'
        );
        $('#benefit-category-description').addClass('d-none');
        $('#benefit-category-description-text').text('');
        goBenefitStep(1);
    }

    function loadBenefitFormStructure(categoryId, afterLoad) {
        if (!categoryId) {
            $('#benefit-dynamic-form').html(
                '<div class="text-center text-muted py-4"><i class="ti-info-alt mr-1"></i> {{ __('common.select_category_hint') }}</div>'
            );
            return;
        }
        $('#benefit-dynamic-form').html('<div class="text-center py-4"><i class="ti-reload spin"></i> Cargando...</div>');

        $.get(`/plans/benefits/form-structure/${categoryId}`, function (data) {
            if (!data.has_form || !data.sections.length) {
                $('#benefit-dynamic-form').html(
                    '<div class="alert alert-info"><i class="ti-check-box mr-1"></i> {{ __('common.benefit_no_additional_params') }}</div>'
                );
            } else {
                renderBenefitDynamicForm(data.sections);
            }
            if (typeof afterLoad === 'function') afterLoad();
        });
    }

    let _benefitSectionsCache = {};

    function renderBenefitDynamicForm(sections) {
        _benefitSectionsCache = {};
        sections.forEach(s => { _benefitSectionsCache[s.id] = s; });

        let html = '';
        sections.forEach(function (section) {
            html += `<div class="form-card" data-section-id="${section.id}" data-repeatable="${section.is_repeatable ? 1 : 0}">`;
            html += `<div class="">`;
            html += `<h3>${section.section_label}</h3>`;
            if (section.is_repeatable) {
                html += ` <span class="badge_5 mb-2">{{ __('common.repeatable_section') }}</span>`;
            }
            html += `</div><div class="">`;

            if (section.is_repeatable) {
                html += `<div class="b-repeatable-rows" data-section-id="${section.id}">`;
                html += buildBenefitRepeatableRow(section, 0);
                html += `</div>`;
                html += `<button type="button" class="btn-toolkit btn-outline btn-b-add-repeat" data-section-id="${section.id}">
                    <i class="ti-plus mr-1"></i> {{ __('common.add_combination') }}
                </button>`;
            } else {
                html += `<div class="row">`;
                section.fields.forEach(function (field) {
                    html += buildBenefitFieldHtml(field, null);
                });
                html += `</div>`;
            }

            html += `</div></div>`;
        });
        $('#benefit-dynamic-form').html(html);
        if (typeof applyCurrencyMask === 'function') applyCurrencyMask(document.getElementById('benefit-dynamic-form'));

        // Init multiselect widgets rendered dynamically
        $('#benefit-dynamic-form .ign-multiselect-wrapper').each(function () {
            if (this._ignMs || !window.ignMultiselect) return;
            var opts = [];
            $(this).find('.ign-ms-option').each(function () {
                opts.push({ value: String($(this).data('value')), label: $(this).find('.ign-ms-option-label').text().trim() });
            });
            window.ignMultiselect.init(this, opts, []);
        });

        $(document).off('click', '.btn-b-add-repeat').on('click', '.btn-b-add-repeat', function () {
            let sectionId  = $(this).data('section-id');
            let container  = $(`.b-repeatable-rows[data-section-id="${sectionId}"]`);
            let nextIndex  = container.find('.b-repeat-row').length;
            let sectionData = _benefitSectionsCache[sectionId];
            if (!sectionData) return;
            container.append(buildBenefitRepeatableRow(sectionData, nextIndex));
            if (typeof applyCurrencyMask === 'function') applyCurrencyMask(document.getElementById('benefit-dynamic-form'));
            container.find('.ign-multiselect-wrapper').each(function () {
                if (this._ignMs || !window.ignMultiselect) return;
                var opts = [];
                $(this).find('.ign-ms-option').each(function () {
                    opts.push({ value: String($(this).data('value')), label: $(this).find('.ign-ms-option-label').text().trim() });
                });
                window.ignMultiselect.init(this, opts, []);
            });
        });

        $(document).off('click', '.btn-b-remove-repeat').on('click', '.btn-b-remove-repeat', function () {
            let row     = $(this).closest('.b-repeat-row');
            let wrapper = row.closest('.b-repeatable-rows');
            if (wrapper.find('.b-repeat-row').length > 1) row.remove();
        });
    }

    function buildBenefitRepeatableRow(section, index) {
        let html = `<div class="b-repeat-row border rounded p-3 mb-2 bg-white position-relative">`;
        html += `<button type="button" class=" btn-b-remove-repeat btn-toolkit btn-sm btn-danger ml-auto d-block" ><i class="ti-trash" style="pointer-events:none;"></i></button>`;
        html += `<div class="row">`;
        section.fields.forEach(function (field) {
            html += buildBenefitFieldHtml(field, index);
        });
        html += `</div></div>`;
        return html;
    }

    function buildBenefitFieldHtml(field, repeatIndex) {
        let name     = repeatIndex !== null ? `answers[${field.id}][${repeatIndex}]` : `answers[${field.id}]`;
        let required = field.is_required ? 'required' : '';
        let helpText = field.help_text
            ? `<small class="form-text text-muted">${field.help_text}</small>` : '';
        let html = `<div class="col-md-6 form-group">`;
        html += `<label class="primary_input_label">${field.field_label}${field.is_required ? ' <span class="text-danger">*</span>' : ''}</label>`;

        if (field.field_type === 'number') {
            html += `<input type="number" class="primary_input_field" name="${name}" step="0.01" min="0" ${required}>`;
        } else if (field.field_type === 'currency') {
            html += `<input type="text" class="primary_input_field currency-mask" name="${name}" placeholder="$ 0" autocomplete="off" ${required}>`;
        } else if (field.field_type === 'select') {
            let opts = field.validation_rules ? field.validation_rules.options : null;
            if (opts === 'METHOD[fetchPermissions]') {
                html += `<input type="text" class="primary_input_field" name="${name}" placeholder="Ej: invite_users" ${required}>`;
            } else if (Array.isArray(opts)) {
                html += `<select class="primary_input_select" name="${name}" ${required}><option value="">{{ __('common.Select...') }}</option>`;
                opts.forEach(function (o) {
                    let val, label;
                    if (o && typeof o === 'object' && o.id !== undefined) {
                        // New format: option from form_options table
                        val   = o.id;
                        label = getTranslatedValue(o.option_label, o.option_key);
                    } else {
                        // Legacy inline format: {"en":"Fixed","es":"Fijo"}
                        val   = (o && typeof o === 'object') ? (o['en'] || Object.values(o)[0]) : o;
                        label = getTranslatedValue(o, String(o));
                    }
                    html += `<option value="${val}">${label}</option>`;
                });
                html += `</select>`;
            } else {
                html += `<select class="primary_input_select" name="${name}" ${required}><option value="">{{ __('common.Select...') }}</option></select>`;
            }
        } else if (field.field_type === 'multiselect') {
            let opts = (field.validation_rules && Array.isArray(field.validation_rules.options))
                ? field.validation_rules.options : [];
            let msOpts = opts.map(function(o) {
                let val, label;
                if (o && typeof o === 'object' && o.id !== undefined) {
                    // New format: option from form_options table
                    val   = o.id;
                    label = getTranslatedValue(o.option_label, o.option_key);
                } else {
                    // Legacy inline format
                    val   = (o && typeof o === 'object') ? (o['en'] || Object.values(o)[0]) : o;
                    label = getTranslatedValue(o, String(o));
                }
                return { value: val, label: label };
            });
            let msId = 'ms_' + name.replace(/[\[\]]/g, '_');
            let msOptsHtml = msOpts.map(o =>
                `<button type="button" class="ign-ms-option" data-value="${o.value}"><span class="ign-ms-check"><i class="ti-check"></i></span><span class="ign-ms-option-label">${o.label}</span></button>`
            ).join('');
            let msSelectOpts = msOpts.map(o => `<option value="${o.value}">${o.label}</option>`).join('');
            html += `<div class="ign-multiselect-wrapper"
                    data-id="${msId}"
                    data-name="${name}"
                    data-required="${field.is_required ? 'true' : 'false'}"
                    data-placeholder="{{ __('common.select_options') }}">
                <select name="${name}[]" multiple ${field.is_required ? 'required' : ''} style="position:absolute;opacity:0;pointer-events:none;height:0;width:0;">${msSelectOpts}</select>
                <div class="ign-ms-control${field.is_required ? ' ign-ms-required' : ''}" id="${msId}" tabindex="0">
                    <div class="ign-ms-chips-area">
                        <div class="ign-ms-chips" id="${msId}_chips"></div>
                        <input type="text" class="ign-ms-search" id="${msId}_search" autocomplete="off" placeholder="{{ __('common.select_options') }}">
                    </div>
                    <span class="ign-ms-arrow" aria-hidden="true"><i class="ti-angle-down"></i></span>
                </div>
                <div class="ign-ms-dropdown" id="${msId}_dropdown" style="max-height:220px;display:none;">
                    <div class="ign-ms-options" id="${msId}_options">
                        ${msOptsHtml}
                        <span class="ign-ms-empty" style="display:none;">{{ __('common.no_results') }}</span>
                    </div>
                </div>
            </div>`;
        } else {
            html += `<input type="text" class="primary_input_field" name="${name}" ${required}>`;
        }

        html += helpText + `</div>`;
        return html;
    }

    function collectBenefitFormData() {
        let data = {
            _token:              csrfToken,
            code:                $('#b_code').val(),
            title:               $('#b_title').val(),
            description:         $('#b_description').val(),
            benefit_category_id: $('#benefit_category_id').val(),
            is_cumulative:       $('#b_is_cumulative').is(':checked') ? 1 : 0,
            is_active:           $('#b_is_active').is(':checked') ? 1 : 0,
            answers:             {},
        };

        // Collect multiselect answers first (via ignMultiselect API)
        let bMsCollected = {};
        $('#benefit-dynamic-form .ign-multiselect-wrapper').each(function () {
            let msName  = $(this).data('name');
            let msMatch = msName.match(/answers\[(\d+)\](?:\[(\d+)\])?/);
            if (!msMatch) return;
            let fieldId   = msMatch[1];
            let repeatIdx = msMatch[2] !== undefined ? msMatch[2] : null;
            let vals = this._ignMs ? this._ignMs.getSelected() : [];
            let serialized = vals.length ? JSON.stringify(vals) : '';
            if (repeatIdx !== null) {
                if (!data.answers[fieldId]) data.answers[fieldId] = {};
                data.answers[fieldId][repeatIdx] = serialized;
            } else {
                data.answers[fieldId] = serialized;
            }
            bMsCollected[msName] = true;
        });

        $('[name^="answers["]').not('.ign-multiselect-wrapper select').each(function () {
            let name  = $(this).attr('name');
            if (bMsCollected[name]) return;
            let match = name.match(/answers\[(\d+)\](?:\[(\d+)\])?/);
            if (!match) return;
            let fieldId   = match[1];
            let repeatIdx = match[2] !== undefined ? match[2] : null;
            let val       = $(this).is(':checkbox') ? ($(this).is(':checked') ? '1' : '0') : $(this).val();

            if (repeatIdx !== null) {
                if (!data.answers[fieldId]) data.answers[fieldId] = {};
                data.answers[fieldId][repeatIdx] = val;
            } else {
                data.answers[fieldId] = val;
            }
        });

        return data;
    }

    function populateBenefitAnswers(formAnswers) {
        if (!formAnswers || !formAnswers.length) return;

        // Step 1 — create missing repeatable rows before populating
        formAnswers.forEach(function (ans) {
            if (ans.repeat_index === null || ans.repeat_index === undefined) return;
            let repeatIdx = parseInt(ans.repeat_index, 10);
            Object.values(_benefitSectionsCache).forEach(function (section) {
                if (!section.is_repeatable) return;
                if (!section.fields.some(function (f) { return f.id == ans.form_field_id; })) return;
                let container = $(`.b-repeatable-rows[data-section-id="${section.id}"]`);
                while (container.find('.b-repeat-row').length <= repeatIdx) {
                    let idx    = container.find('.b-repeat-row').length;
                    let newRow = $(buildBenefitRepeatableRow(section, idx));
                    container.append(newRow);
                    newRow.find('.ign-multiselect-wrapper').each(function () {
                        if (this._ignMs || !window.ignMultiselect) return;
                        var opts = [];
                        $(this).find('.ign-ms-option').each(function () {
                            opts.push({ value: String($(this).data('value')), label: $(this).find('.ign-ms-option-label').text().trim() });
                        });
                        window.ignMultiselect.init(this, opts, []);
                    });
                }
            });
        });

        // Step 2 — group answers by fieldId to handle legacy multiselect rows (repeat_index used as array index)
        let grouped = {};
        formAnswers.forEach(function (ans) {
            let key = ans.form_field_id;
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(ans);
        });

        Object.keys(grouped).forEach(function (fieldId) {
            let answers = grouped[fieldId];

            // Check if this field belongs to a repeatable section
            let isRepeatableField = Object.values(_benefitSectionsCache).some(function (section) {
                return section.is_repeatable && section.fields.some(function (f) { return f.id == fieldId; });
            });

            // Check if there's a non-repeatable multiselect wrapper for this field
            let msWrapperNonRepeat = $(`#benefit-dynamic-form .ign-multiselect-wrapper[data-name="answers[${fieldId}]"]`);

            if (!isRepeatableField && msWrapperNonRepeat.length && msWrapperNonRepeat[0]._ignMs) {
                // Non-repeatable multiselect: merge all answer values (handles legacy rows stored with repeat_index)
                let allVals = [];
                answers.forEach(function (ans) {
                    let val = ans.answer;
                    try { let p = JSON.parse(val); allVals = allVals.concat(Array.isArray(p) ? p : [val]); }
                    catch(e) { if (val) allVals.push(val); }
                });
                msWrapperNonRepeat[0]._ignMs.setSelected(allVals);
            } else {
                // Regular field or repeatable section field
                answers.forEach(function (ans) {
                    let repeatIdx = ans.repeat_index;
                    let val       = ans.answer;

                    if (repeatIdx !== null && repeatIdx !== undefined) {
                        let msWrapper = $(`#benefit-dynamic-form .ign-multiselect-wrapper[data-name="answers[${fieldId}][${repeatIdx}]"]`);
                        let $input    = $(`#benefit-dynamic-form [name="answers[${fieldId}][${repeatIdx}]"]`).not('.ign-multiselect-wrapper select');
                        if (msWrapper.length && msWrapper[0]._ignMs) {
                            let parsed = []; try { parsed = JSON.parse(val); } catch(e) { if (val) parsed = [val]; }
                            msWrapper[0]._ignMs.setSelected(parsed);
                        } else if ($input.length) {
                            if ($input.is(':checkbox')) { $input.prop('checked', val === '1' || val === 1); } else { $input.val(val); }
                        }
                    } else {
                        let $input = $(`#benefit-dynamic-form [name="answers[${fieldId}]"]`).not('.ign-multiselect-wrapper select');
                        if ($input.length) {
                            if ($input.is(':checkbox')) { $input.prop('checked', val === '1' || val === 1); } else { $input.val(val); }
                        }
                    }
                });
            }
        });
    }

    function renderBenefitShowModal(benefit) {
        $('#show_b_code').text(benefit.code || '—');
        $('#show_b_title').text(getTranslatedValue(benefit.title, ''));
        $('#show_b_active').html(benefit.is_active
            ? '<span class="badge_1">{{ __('common.active') }}</span>'
            : '<span class="badge_2">{{ __('common.inactive') }}</span>');
        $('#show_b_category').text(benefit.category ? getTranslatedValue(benefit.category.name, '') : '—');
        $('#show_b_type_label').text(benefit.category && benefit.category.type ? getTranslatedValue(benefit.category.type.label, '') : '—');
        $('#show_b_cumulative').html(benefit.is_cumulative
            ? '<span class="badge_1">{{ __('common.yes') }}</span>'
            : '<span class="badge_2">{{ __('common.no') }}</span>');
        $('#show_b_plans_count').text(benefit.plan_children_count ?? '—');
        $('#show_b_description').text(getTranslatedValue(benefit.description, '') || '—');

        let answersHtml = '';
        if (benefit.form_answers && benefit.form_answers.length) {
            // Group answers by section
            let sections = {};
            benefit.form_answers.forEach(function (a) {
                let sectionId   = a.field && a.field.section ? a.field.section.id : '__none__';
                let sectionLabel = a.field && a.field.section ? getTranslatedValue(a.field.section.section_label, '') : null;
                let isRepeatable = a.field && a.field.section ? !!a.field.section.is_repeatable : false;
                if (!sections[sectionId]) {
                    sections[sectionId] = { label: sectionLabel, is_repeatable: isRepeatable, answers: [] };
                }
                sections[sectionId].answers.push(a);
            });

            function formatAnswerValue(a) {
                if (a.field && a.field.field_type === 'multiselect') {
                    let vals = [];
                    try { vals = JSON.parse(a.answer); } catch(e) { if (a.answer) vals = [a.answer]; }
                    return vals.map(function(v) { return resolveAnswerDisplay(v, a.field); }).join(', ') || '—';
                }
                return resolveAnswerDisplay(a.answer, a.field);
            }

            Object.values(sections).forEach(function (section) {
                if (section.is_repeatable) {
                    // Group by repeat_index
                    let combinations = {};
                    section.answers.forEach(function (a) {
                        let idx = a.repeat_index !== null && a.repeat_index !== undefined ? a.repeat_index : 0;
                        if (!combinations[idx]) combinations[idx] = [];
                        combinations[idx].push(a);
                    });

                    let sectionTitle = section.label ? `<li class="list-group-item px-0 pb-0 border-0"><span class="text-muted font-weight-bold">${section.label}</span></li>` : '';
                    answersHtml += sectionTitle;

                    Object.keys(combinations).sort(function(a,b){ return a-b; }).forEach(function (idx) {
                        let combNum = parseInt(idx) + 1;
                        answersHtml += `<li class="list-group-item px-0 py-2">`;
                        answersHtml += `<div class="border rounded p-2 bg-light">`;
                        answersHtml += `<div class="text-muted small font-weight-bold mb-2">{{ __('common.combination') }} #${combNum}</div>`;
                        answersHtml += `<ul class="list-group list-group-flush">`;
                        combinations[idx].forEach(function (a) {
                            let label = a.field ? getTranslatedValue(a.field.field_label, '') : `Campo ${a.form_field_id}`;
                            answersHtml += `<li class="list-group-item d-flex justify-content-between px-0 py-1 border-0">
                                <span class="text-muted">${label}</span><span class="text-black">${formatAnswerValue(a)}</span></li>`;
                        });
                        answersHtml += `</ul></div></li>`;
                    });
                } else {
                    // Non-repeatable: flat list, optionally with section title
                    if (section.label) {
                        answersHtml += `<li class="list-group-item px-0 pb-0 border-0"><span class="text-muted font-weight-bold">${section.label}</span></li>`;
                    }
                    section.answers.forEach(function (a) {
                        let label = a.field ? getTranslatedValue(a.field.field_label, '') : `Campo ${a.form_field_id}`;
                        answersHtml += `<li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">${label}</span><span class="text-black">${formatAnswerValue(a)}</span></li>`;
                    });
                }
            });
        }
        $('#show_b_answers').html(answersHtml ||
            '<li class="list-group-item text-muted">{{ __('common.benefit_no_params') }}</li>');
    }

})(jQuery);
</script>
@endpush
