@extends('backEnd.master')

@push('styles')
<style>
/* ── Dependency chain (show modal) ─────────────────────── */
.dep-chain {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.dep-chain__card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
}
.dep-chain__code {
    font-size: .75rem;
    font-weight: 700;
    color: #6c757d;
    white-space: nowrap;
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 4px;
}
.dep-chain__title {
    font-size: .875rem;
    color: #212529;
}
.dep-chain__connector {
    display: flex;
    align-items: center;
    padding: 4px 12px;
}
.dep-chain__operator {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .05em;
    padding: 2px 8px;
    border-radius: 10px;
    text-transform: uppercase;
}
.dep-chain__operator--or {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffc107;
}
.dep-chain__operator--and {
    background: #cff4fc;
    color: #055160;
    border: 1px solid #0dcaf0;
}
</style>
@endpush

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link action" href="#" id="btn_create_rule">
                                    <i class="ti-plus mr-2"></i> {{ __('common.new_rule') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('common.rules_catalog') }}</h3>
                    </div>
                </div>

                <div class="QA_section QA_section_heading_custom check_box_table">
                    <div class="QA_table">
                        <div class="table-responsiv">
                            <table id="rulesTable" class="table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>{{ __('common.code') }}</th>
                                        <th>{{ __('common.title') }}</th>
                                        <th>{{ __('common.category') }}</th>
                                        <th>{{ __('common.subplans') }}</th>
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
    </div>
</section>

@include('plans::rules.components.form_modal', ['categories' => $categories])
@include('plans::rules.components.show_modal')
@include('backEnd.partials.delete_modal', ['item_name' => __('common.rule')])

@endsection

@push('scripts')
@include('components.admin.multiselect-engine')
@include('plans::components.utils')
<script type="text/javascript">
(function ($) {
    "use strict";
    var getTranslatedValue   = window.plansUtils.getTranslatedValue;
    var resolveAnswerDisplay = window.plansUtils.resolveAnswerDisplay;

    const ruleCategoryDescriptions = {
        @foreach($categories as $cat)
        {{ $cat->id }}: @json($cat->description),
        @endforeach
    };

    const ruleCategoryKeys = {
        @foreach($categories as $cat)
        {{ $cat->id }}: @json($cat->key),
        @endforeach
    };

    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    let table;
    let loadingEditRule = false;

    $(document).ready(function () {

        // ── DataTable ──────────────────────────────────────────
        let ajaxUrl = "{{ route('plans.rules.get-data') }}";
        var columnData = [
                { data: 'DT_RowIndex', name: 'id', searchable: false, orderable: false },
                { data: 'code', name: 'code' },
                { data: 'title', name: 'title' },
                { data: 'category_badge', name: 'category_badge', orderable: false, searchable: false },
                { data: 'plans_badge', name: 'plans_badge', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status_badge', searchable: false, orderable: false },
                { data: 'updated_at_formatted', name: 'updated_at', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ];
        table = initGlobalDataTable('#rulesTable', ajaxUrl, columnData);

        // ── Crear ──────────────────────────────────────────────
        $('#btn_create_rule').on('click', function (e) {
            e.preventDefault();
            resetRuleForm();
            $('#ruleFormModalLabel').text('{{ __('common.new_rule') }}');
            
            // Generate code automatically
            $('#code').val('{{ __('common.loading') }}...').prop('readonly', true);
            $.get("{{ route('plans.rules.next-code') }}", function(res) {
                $('#code').val(res.code);
            }).fail(function() {
                $('#code').val('').prop('readonly', false);
            });

            $('#ruleFormModal').modal('show');
        });

        // ── Guardar / Actualizar ───────────────────────────────
        $('#ruleForm').on('submit', function (e) {
            e.preventDefault();

            // Validación MAINTENANCE: mínimo 2 dependencias con regla seleccionada
            let depRows = $('#dependencies-list .dep-row');
            if (depRows.length) {
                let filledDeps = depRows.filter(function () {
                    return $(this).find('.dep-rule-select').val() !== '';
                }).length;
                if (filledDeps < 2) {
                    toastr.error("{{ __('common.maintenance_min_two_rules') }}", "{{ __('common.error') }}");
                    return;
                }
            }

            let selectedCategoryKey = ruleCategoryKeys[$('#rule_category_id').val()];
            if ((selectedCategoryKey === 'POINTS_THRESHOLD' || selectedCategoryKey === 'POINTS_RANGE') && !hasAtLeastOnePointSourceSelected()) {
                toastr.error('Debes seleccionar al menos una fuente de puntos.', "{{ __('common.error') }}");
                return;
            }

            $('#pre-loader').removeClass('d-none');

            let id  = $('#rule_id').val();
            let url = id
                ? `/plans/rules/${id}/update`
                : "{{ route('plans.rules.store') }}";

            // Convert multiselect native <select> to a single JSON hidden input before serializing
            $('#dynamic-form-container .ign-multiselect-wrapper').each(function () {
                let msName = $(this).data('name');
                let $sel   = $(this).find('select');
                let vals   = this._ignMs ? this._ignMs.getSelected() : $sel.val() || [];
                $sel.prop('disabled', true);
                $(this).append($('<input type="hidden">').attr('name', msName).val(vals.length ? JSON.stringify(vals) : ''));
            });

            let formData = $(this).serialize();

            $('#dynamic-form-container .ign-multiselect-wrapper').each(function () {
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
                        _depRulesCache = null;
                        _depRulesCachePromise = null;
                        $('#ruleFormModal').modal('hide');
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
        $(document).on('click', '.edit_rule', function (e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            let id = $(this).data('value');

            resetRuleForm();
            $('#code').prop('readonly', false); // Allow edit

            $.get(`/plans/rules/${id}/edit`, function (rule) {
                $('#rule_id').val(rule.id);
                $('#code').val(rule.code);
                $('#title').val(getTranslatedValue(rule.title, ''));
                $('#description').val(getTranslatedValue(rule.title, ''));
                $('#is_active').prop('checked', !!rule.is_active);
                loadingEditRule = true;
                $('#rule_category_id').val(rule.rule_category_id).trigger('change');
                loadingEditRule = false;

                // Load form structure then populate answers
                loadFormStructure(rule.rule_category_id, function () {
                    populateAnswers(rule.form_answers, rule.dependencies);
                });

                $('#ruleFormModalLabel').text('{{ __('common.edit_rule') }}');
                $('#ruleFormModal').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });

        // ── Ver detalles ───────────────────────────────────────
        $(document).on('click', '.show_rule', function (e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            $.get(`/plans/rules/${$(this).data('value')}/show`, function (rule) {
                renderShowModal(rule);
                $('#ruleShowModal').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });

        // ── Eliminar ───────────────────────────────────────────
        $(document).on('click', '.delete_rule', function (e) {
            e.preventDefault();
            confirm_modal(`/plans/rules/${$(this).data('value')}/destroy`);
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
        $('#rule_category_id').on('change', function () {
            const catId = $(this).val();
            const desc = ruleCategoryDescriptions[catId];
            const text = desc ? getTranslatedValue(desc, '') : '';
            if (text) {
                $('#rule-category-description-text').text(text);
                $('#rule-category-description').removeClass('d-none');
            } else {
                $('#rule-category-description').addClass('d-none');
            }
            if (!loadingEditRule) {
                loadFormStructure(catId);
            }
        });

        // ── Wizard paso siguiente / anterior ──────────────────
        $('#rule-btn-next').on('click', function () {
            let valid = true;
            $('#rule-step-1 :input[required]').each(function () {
                if (!this.checkValidity()) { this.reportValidity(); valid = false; return false; }
            });
            if (!valid) return;
            goToStep(2);
        });

        $('#rule-btn-prev').on('click', function () { goToStep(1); });

    }); // end ready

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    let planChildrenCache = null;

    function fetchPlanChildren(callback) {
        if (planChildrenCache) { callback(planChildrenCache); return; }
        $.get("{{ route('plans.rules.plan-children-list') }}", function (data) {
            planChildrenCache = data;
            callback(data);
        });
    }

    function goToStep(step) {
        $('.rule-wizard-step').addClass('d-none');
        $('#rule-step-' + step).removeClass('d-none');
        $('.rule-step-header').removeClass('active');
        $(`.rule-step-header[data-step="${step}"]`).addClass('active');

        if (step === 1) {
            $('#rule-btn-prev').addClass('d-none');
            $('#rule-btn-next').removeClass('d-none');
            $('#rule-btn-save').addClass('d-none');
        } else {
            $('#rule-btn-prev').removeClass('d-none');
            $('#rule-btn-next').addClass('d-none');
            $('#rule-btn-save').removeClass('d-none');
        }
    }

    function resetRuleForm() {
        $('#ruleForm')[0].reset();
        $('#rule_id').val('');
        $('#dynamic-form-container').html('<div class="text-center text-muted py-4"><i class="ti-info-alt mr-1"></i> {{ __('common.select_category_hint') }}</div>');
        $('#rule-category-description').addClass('d-none');
        $('#rule-category-description-text').text('');
        goToStep(1);
    }

    function loadFormStructure(categoryId, afterLoad) {
        if (!categoryId) {
            $('#dynamic-form-container').html('<div class="text-center text-muted py-4"><i class="ti-info-alt mr-1"></i> {{ __('common.select_category_hint') }}</div>');
            return;
        }
        $('#dynamic-form-container').html('<div class="text-center py-4"><i class="ti-reload spin"></i> {{ __('common.loading_form') }}</div>');

        $.get(`/plans/rules/form-structure/${categoryId}`, function (data) {
            if (data.is_maintenance) {
                renderMaintenanceForm();
            } else if (!data.sections.length) {
                $('#dynamic-form-container').html(
                    '<div class="alert alert-info"><i class="ti-check-box mr-1"></i> {{ __('common.rule_no_additional_params') }}</div>'
                );
            } else {
                renderDynamicForm(data.sections);
            }
            if (typeof afterLoad === 'function') afterLoad();
        });
    }

    function renderDynamicForm(sections) {
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
                html += `<div class="repeatable-rows" data-section-id="${section.id}">`;
                html += buildRepeatableRow(section, 0);
                html += `</div>`;
                html += `<button type="button" class="btn-toolkit btn-outline btn-add-repeat" data-section-id="${section.id}" >
                    <i class="ti-plus mr-1"></i> {{ __('common.add_combination') }}
                </button>`;
            } else {
                html += `<div class="row">`;
                section.fields.forEach(function (field) {
                    html += buildFieldHtml(field, null, section.fields.length);
                });
                html += `</div>`;
            }

            html += `</div></div>`;
        });

        $('#dynamic-form-container').html(html);

        // Init any select2 or dynamic select fields
        initDynamicSelects();
        if (typeof applyCurrencyMask === 'function') applyCurrencyMask(document.getElementById('dynamic-form-container'));

        // Add-row handler for repeatable sections

        $(document).off('click', '.btn-add-repeat').on('click', '.btn-add-repeat', function () {
            let sectionId = $(this).data('section-id');
            let container = $(`.repeatable-rows[data-section-id="${sectionId}"]`);
            let nextIndex  = container.find('.repeat-row').length;
            let sectionData = getSectionData(sectionId);
            if (!sectionData) return;
            container.append(buildRepeatableRow(sectionData, nextIndex));
            initDynamicSelects();
            if (typeof applyCurrencyMask === 'function') applyCurrencyMask(document.getElementById('dynamic-form-container'));
        });

        // Remove-row
        $(document).off('click', '.btn-remove-repeat').on('click', '.btn-remove-repeat', function () {
            let row     = $(this).closest('.repeat-row');
            let wrapper = row.closest('.repeatable-rows');
            if (wrapper.find('.repeat-row').length > 1) row.remove();
        });
    }

    // Store sections for repeatable row creation
    let _sectionsCache = {};
    function getSectionData(sectionId) { return _sectionsCache[sectionId] || null; }

    // Override renderDynamicForm to cache sections
    let _origRender = renderDynamicForm;
    renderDynamicForm = function (sections) {
        _sectionsCache = {};
        sections.forEach(s => { _sectionsCache[s.id] = s; });
        _origRender(sections);
    };

    function buildRepeatableRow(section, index) {
        let html = `<div class="repeat-row border rounded p-3 mb-2 bg-white position-relative">`;
        html += `<button type="button" class="btn-toolkit btn-sm btn-danger btn-remove-repeat ml-auto d-block" ><i class="ti-trash" style="pointer-events:none;"></i></button>`;
        html += `<div class="row">`;
        section.fields.forEach(function (field) {
            html += buildFieldHtml(field, index, section.fields.length);
        });
        html += `</div></div>`;
        return html;
    }

    function buildFieldHtml(field, repeatIndex, totalFields) {
        let colClass = (totalFields === 3) ? 'col-md-4' : 'col-md-6';
        let name = repeatIndex !== null
            ? `answers[${field.id}][${repeatIndex}]`
            : `answers[${field.id}]`;
        let required = field.is_required ? 'required' : '';
        let helpText = field.help_text ? `<small class="form-text text-muted">${field.help_text}</small>` : '';
        let html = `<div class="${colClass} form-group">`;
        html += `<label class="primary_input_label">${field.field_label}${field.is_required ? ' <span class="text-danger">*</span>' : ''}</label>`;

        if (field.field_type === 'number') {
            let min = (field.validation_rules && field.validation_rules.min !== undefined) ? `min="${field.validation_rules.min}"` : '';
            let step = (field.validation_rules && field.validation_rules.decimals) ? 'step="0.01"' : 'step="1"';
            html += `<input type="number" class="primary_input_field" name="${name}" ${min} ${step} ${required}>`;
        } else if (field.field_type === 'currency') {
            html += `<input type="text" class="primary_input_field currency-mask" name="${name}" placeholder="$ 0" autocomplete="off" ${required}>`;
        } else if (field.field_type === 'boolean') {
            html += `<div class="form-check mt-2">
                <input type="checkbox" class="form-check-input" name="${name}" id="field_${field.id}_${repeatIndex ?? 0}" value="1">
                <label class="form-check-label" for="field_${field.id}_${repeatIndex ?? 0}">Sí</label>
            </div>`;
        } else if (field.field_type === 'select') {
            let opts = field.validation_rules ? field.validation_rules.options : null;
            if (opts === 'METHOD[fetchPlanChildren]') {
                html += `<select class="primary_input_select dynamic-select" name="${name}" data-method="fetchPlanChildren" ${required}><option value="">{{ __('common.Loading...') }}</option></select>`;
            } else if (Array.isArray(opts)) {
                html += `<select class="primary_input_select" name="${name}" ${required}><option value="">{{ __('common.Select...') }}</option>`;
                opts.forEach(o => {
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

    function initDynamicSelects() {
        // Populate METHOD[fetchPlanChildren] selects that haven't been loaded yet
        $('.dynamic-select[data-method="fetchPlanChildren"]').each(function () {
            if ($(this).data('loaded')) return;
            let sel = $(this);
            fetchPlanChildren(function (items) {
                let opts = '<option value="">{{ __('common.Select...') }}</option>';
                items.forEach(function (item) {
                    opts += `<option value="${item.id}">${item.text}</option>`;
                });
                sel.html(opts).data('loaded', true);
                if (sel.attr('data-selected-value')) {
                    sel.val(sel.attr('data-selected-value'));
                }
            });
        });

        // Init multiselect widgets rendered dynamically (opciones ya cargadas en el DOM)
        $('#dynamic-form-container .ign-multiselect-wrapper').each(function () {
            if (this._ignMs) return;
            if (!window.ignMultiselect) return;
            var opts = [];
            $(this).find('.ign-ms-option').each(function () {
                opts.push({ value: String($(this).data('value')), label: $(this).find('.ign-ms-option-label').text().trim() });
            });
            window.ignMultiselect.init(this, opts, []);
        });
    }

    // ── MAINTENANCE: dependency builder ───────────────────────
    function renderMaintenanceForm() {
        let html = `
        <div class="alert alert-info mb-3">
            <i class="ti-info-alt mr-1"></i>
            {{ __('common.maintenance_category_info') }}
        </div>
        <div id="dependencies-list"></div>
        <button type="button" class="btn-toolkit btn-outline" id="btn-add-dependency" >
            <i class="ti-plus mr-1"></i> {{ __('common.add_dependent_rule') }}
        </button>`;
        $('#dynamic-form-container').html(html);

        $('#btn-add-dependency').on('click', function () {
            addDependencyRow();
        });

        // Load first row
        addDependencyRow();
    }

    let _depRulesCache = null;
    let _depRulesCachePromise = null;

    function fetchDepRules() {
        if (_depRulesCache) return $.Deferred().resolve(_depRulesCache).promise();
        if (_depRulesCachePromise) return _depRulesCachePromise;
        _depRulesCachePromise = $.get("{{ route('plans.rules.get-list') }}").done(function (rules) {
            _depRulesCache = rules;
        });
        return _depRulesCachePromise;
    }

    function buildDepOptions(rules, selectedId) {
        let opts = '<option value="">{{ __('common.select_rule') }}</option>';
        rules.forEach(function (r) {
            let sel = (selectedId && r.id == selectedId) ? 'selected' : '';
            opts += `<option value="${r.id}" ${sel}>${r.text}</option>`;
        });
        return opts;
    }

    function updateDepRemoveButtons() {
        let rows = $('#dependencies-list .dep-row');
        let canRemove = rows.length > 2;
        rows.find('.btn-remove-dep').toggle(canRemove);
    }

    function addDependencyRow(childRuleId, operator) {
        let index = $('#dependencies-list .dep-row').length;
        let html = `
        <div class="dep-row d-flex align-items-center justify-content-between mb-2 border rounded px-2 py-3 bg-white" data-index="${index}">
            <div class="flex-1">
                <span class="text-muted mr-2 dep-index-label">#${index + 1}</span>
                <select class="primary_input_select mr-2 dep-rule-select" name="dependencies[${index}][child_rule_id]" style="max-width:320px;" required disabled>
                    <option value="">{{ __('common.loading') }}...</option>
                </select>
                <select class="primary_input_select mr-2" name="dependencies[${index}][operator]" style="max-width:100px;">
                    <option value="OR" ${operator === 'OR' ? 'selected' : ''}>OR</option>
                    <option value="AND" ${operator === 'AND' ? 'selected' : ''}>AND</option>
                </select>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-dep" title="Quitar"><i class="ti-trash"></i></button>
        </div>`;
        $('#dependencies-list').append(html);

        let lastSel = $('#dependencies-list .dep-row:last .dep-rule-select');
        fetchDepRules().done(function (rules) {
            lastSel.html(buildDepOptions(rules, childRuleId)).prop('disabled', false);
        });

        updateDepRemoveButtons();
    }

    $(document).on('click', '.btn-remove-dep', function () {
        let list = $('#dependencies-list');
        if (list.find('.dep-row').length > 2) {
            $(this).closest('.dep-row').remove();
            list.find('.dep-row').each(function (i) {
                $(this).attr('data-index', i).find('.dep-index-label').text(`#${i + 1}`);
                $(this).find('select').each(function () {
                    let n = $(this).attr('name').replace(/dependencies\[\d+\]/, `dependencies[${i}]`);
                    $(this).attr('name', n);
                });
            });
            updateDepRemoveButtons();
        }
    });

    // ── Collect form data (form-encoded, compatible con $.ajaxSetup) ──
    function collectRuleFormData() {
        let data = {
            _token:           csrfToken,
            code:             $('#code').val(),
            title:            $('#title').val(),
            description:      $('#description').val(),
            rule_category_id: $('#rule_category_id').val(),
            is_active:        $('#is_active').is(':checked') ? 1 : 0,
            answers:          {},
            dependencies:     [],
        };

        // Collect multiselect answers first (via ignMultiselect API)
        let msCollected = {};
        $('#dynamic-form-container .ign-multiselect-wrapper').each(function () {
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
            msCollected[msName] = true;
        });

        // Collect remaining answers — mantiene el manejo manual de checkboxes (unchecked = '0')
        $('[name^="answers["]').not('.ign-multiselect-wrapper select').each(function () {
            let name = $(this).attr('name');
            if (msCollected[name]) return; // ya recolectado como multiselect
            let match = name.match(/answers\[(\d+)\](?:\[(\d+)\])?/);
            if (!match) return;
            let fieldId   = match[1];
            let repeatIdx = match[2] !== undefined ? match[2] : null;
            let val = $(this).is(':checkbox') ? ($(this).is(':checked') ? '1' : '0') : $(this).val();

            if (repeatIdx !== null) {
                if (!data.answers[fieldId]) data.answers[fieldId] = {};
                data.answers[fieldId][repeatIdx] = val;
            } else {
                data.answers[fieldId] = val;
            }
        });

        // Collect dependencies
        $('#dependencies-list .dep-row').each(function () {
            let childRuleId = $(this).find('[name*="child_rule_id"]').val();
            let operator    = $(this).find('[name*="operator"]').val();
            if (childRuleId) {
                data.dependencies.push({ child_rule_id: childRuleId, operator: operator });
            }
        });

        // jQuery serializa este objeto como application/x-www-form-urlencoded
        // con notación de brackets: answers[1]=2.5&dependencies[0][child_rule_id]=X
        return data;
    }

    // ── Populate answers on edit ───────────────────────────────
    function populateAnswers(formAnswers, dependencies) {
        // Populate dependencies first (independent of form_answers — e.g. MAINTENANCE has no answers)
        if (dependencies && dependencies.length) {
            $('#dependencies-list').empty();
            dependencies.forEach(function (dep) {
                addDependencyRow(dep.child_rule_id, dep.operator);
            });
        }

        if (!formAnswers || !formAnswers.length) return;

        // Expand repeatable rows to match the saved repeat_index values
        formAnswers.forEach(function (ans) {
            if (ans.repeat_index === null || ans.repeat_index === undefined) return;
            let repeatIdx = parseInt(ans.repeat_index, 10);
            Object.values(_sectionsCache).forEach(function (section) {
                if (!section.is_repeatable) return;
                let hasField = section.fields.some(function (f) { return f.id == ans.form_field_id; });
                if (!hasField) return;
                let container = $(`.repeatable-rows[data-section-id="${section.id}"]`);
                while (container.find('.repeat-row').length <= repeatIdx) {
                    let nextIndex = container.find('.repeat-row').length;
                    let newRow = $(buildRepeatableRow(section, nextIndex));
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

        // Group answers by fieldId to handle legacy multiselect rows (stored with repeat_index as array index)
        let grouped = {};
        formAnswers.forEach(function (ans) {
            let key = ans.form_field_id;
            if (!grouped[key]) grouped[key] = [];
            grouped[key].push(ans);
        });

        Object.keys(grouped).forEach(function (fieldId) {
            let answers = grouped[fieldId];

            let isRepeatableField = Object.values(_sectionsCache).some(function (section) {
                return section.is_repeatable && section.fields.some(function (f) { return f.id == fieldId; });
            });

            let msWrapperNonRepeat = $(`#dynamic-form-container .ign-multiselect-wrapper[data-name="answers[${fieldId}]"]`);

            if (!isRepeatableField && msWrapperNonRepeat.length && msWrapperNonRepeat[0]._ignMs) {
                let allVals = [];
                answers.forEach(function (ans) {
                    let val = ans.answer;
                    try { let p = JSON.parse(val); allVals = allVals.concat(Array.isArray(p) ? p : [val]); }
                    catch(e) { if (val) allVals.push(val); }
                });
                msWrapperNonRepeat[0]._ignMs.setSelected(allVals);
            } else {
                answers.forEach(function (ans) {
                    let repeatIdx = ans.repeat_index;
                    let val       = ans.answer;

                    if (repeatIdx !== null && repeatIdx !== undefined) {
                        let msWrapper = $(`#dynamic-form-container .ign-multiselect-wrapper[data-name="answers[${fieldId}][${repeatIdx}]"]`);
                        let $input    = $(`#dynamic-form-container [name="answers[${fieldId}][${repeatIdx}]"]`).not('.ign-multiselect-wrapper select');
                        if (msWrapper.length && msWrapper[0]._ignMs) {
                            let parsed = []; try { parsed = JSON.parse(val); } catch(e) { if (val) parsed = [val]; }
                            msWrapper[0]._ignMs.setSelected(parsed);
                        } else {
                            setInputValue($input, val);
                        }
                    } else {
                        let $input = $(`#dynamic-form-container [name="answers[${fieldId}]"]`).not('.ign-multiselect-wrapper select');
                        if ($input.length) setInputValue($input, val);
                    }
                });
            }
        });

    }

    function setInputValue(input, val) {
        if (!input.length) return;
        if (input.is(':checkbox')) {
            input.prop('checked', val === '1' || val === 1 || val === true);
        } else {
            input.val(val);
            if (input.is('select')) {
                input.attr('data-selected-value', val);
            }
        }
    }

    // ── Show modal ─────────────────────────────────────────────
    function hasAtLeastOnePointSourceSelected() {
        let sourceFieldIds = [];

        Object.values(_sectionsCache).forEach(function (section) {
            section.fields.forEach(function (field) {
                if (field.field_key === 'INCLUDE_PERSONAL' || field.field_key === 'INCLUDE_CHILDREN') {
                    sourceFieldIds.push(field.id);
                }
            });
        });

        if (!sourceFieldIds.length) {
            return false;
        }

        return sourceFieldIds.some(function (fieldId) {
            return $(`#dynamic-form-container input[name="answers[${fieldId}]"]`).is(':checked');
        });
    }

    function renderShowModal(rule) {
        $('#show_rule_code').text(rule.code);
        $('#show_rule_title').text(getTranslatedValue(rule.title, ''));
        $('#show_rule_category').text(rule.category ? getTranslatedValue(rule.category.name, '') : '—');
        $('#show_rule_type').text(rule.category && rule.category.type ? getTranslatedValue(rule.category.type.label, '') : '—');
        $('#show_rule_plans_count').text(rule.plan_children_count ?? '—');
        $('#show_rule_active').html(rule.is_active
            ? '<span class="badge badge-success">{{ __('common.active') }}</span>'
            : '<span class="badge badge-danger">{{ __('common.inactive') }}</span>');
        $('#show_rule_description').text(getTranslatedValue(rule.description, '') || '—');

        // Check if any answer uses METHOD[fetchPlanChildren] and cache isn't loaded yet
        let needsPlanChildren = rule.form_answers && rule.form_answers.some(function (a) {
            return a.field && a.field.validation_rules && a.field?.validation_rules?.options === 'METHOD[fetchPlanChildren]';
        });

        if (needsPlanChildren && !planChildrenCache) {
            fetchPlanChildren(function () { buildShowAnswers(rule); });
        } else {
            buildShowAnswers(rule);
        }
    }

    function resolvePlanChildName(id) {
        if (!planChildrenCache) return id;
        let item = planChildrenCache.find(function (c) { return String(c.id) === String(id); });
        return item ? item.text : id;
    }

    function buildShowAnswers(rule) {
        let answersHtml = '';

        if (rule.form_answers && rule.form_answers.length) {
            // Group answers by section
            let sections = {};
            let sectionOrder = [];
            rule.form_answers.forEach(function (a) {
                let sectionId    = a.field && a.field.section ? a.field.section.id : '__none__';
                let sectionLabel = a.field && a.field.section ? getTranslatedValue(a.field.section.section_label, '') : null;
                let isRepeatable = a.field && a.field.section ? !!a.field.section.is_repeatable : false;
                if (!sections[sectionId]) {
                    sections[sectionId] = { label: sectionLabel, is_repeatable: isRepeatable, answers: [] };
                    sectionOrder.push(sectionId);
                }
                sections[sectionId].answers.push(a);
            });

            function formatRuleAnswerValue(a) {
                let opts = a.field && a.field.validation_rules ? a.field.validation_rules.options : null;
                if (a.field && a.field.field_type === 'multiselect') {
                    let vals = [];
                    try { vals = JSON.parse(a.answer); } catch(e) { if (a.answer) vals = [a.answer]; }
                    return vals.map(function(v) { return resolveAnswerDisplay(v, a.field); }).join(', ') || '—';
                } else if (opts === 'METHOD[fetchPlanChildren]') {
                    return resolvePlanChildName(a.answer);
                }
                return resolveAnswerDisplay(a.answer, a.field);
            }

            sectionOrder.forEach(function (sectionId) {
                let section = sections[sectionId];

                if (section.is_repeatable) {
                    // Group by repeat_index
                    let combinations = {};
                    section.answers.forEach(function (a) {
                        let idx = a.repeat_index !== null && a.repeat_index !== undefined ? a.repeat_index : 0;
                        if (!combinations[idx]) combinations[idx] = [];
                        combinations[idx].push(a);
                    });

                    if (section.label) {
                        answersHtml += `<li class="list-group-item px-0 pb-0 border-0"><span class="text-muted font-weight-bold">${section.label}</span></li>`;
                    }

                    Object.keys(combinations).sort(function(a,b){ return a-b; }).forEach(function (idx) {
                        let combNum = parseInt(idx) + 1;
                        answersHtml += `<li class="list-group-item px-0 py-2">`;
                        answersHtml += `<div class="border rounded p-2 bg-light">`;
                        answersHtml += `<div class="text-muted small font-weight-bold mb-2">{{ __('common.combination') }} #${combNum}</div>`;
                        answersHtml += `<ul class="list-group list-group-flush">`;
                        combinations[idx].forEach(function (a) {
                            let label = a.field ? getTranslatedValue(a.field.field_label, '') : `Campo ${a.form_field_id}`;
                            answersHtml += `<li class="list-group-item d-flex justify-content-between px-0 py-1 border-0">
                                <span class="text-muted">${label}</span><strong>${formatRuleAnswerValue(a)}</strong></li>`;
                        });
                        answersHtml += `</ul></div></li>`;
                    });
                } else {
                    if (section.label) {
                        answersHtml += `<li class="list-group-item px-0 pb-0 border-0"><span class="text-muted font-weight-bold">${section.label}</span></li>`;
                    }
                    section.answers.forEach(function (a) {
                        let label = a.field ? getTranslatedValue(a.field.field_label, '') : `Campo ${a.form_field_id}`;
                        answersHtml += `<li class="list-group-item d-flex justify-content-between">
                            <span>${label}</span><strong>${formatRuleAnswerValue(a)}</strong></li>`;
                    });
                }
            });
        }

        if (rule.dependencies && rule.dependencies.length) {
            answersHtml += `<li class="list-group-item px-0 pt-2 pb-0 border-0">
                <span class="text-muted font-weight-bold d-block mb-2">{{ __('common.dependencies') }}</span>
                <div class="dep-chain">`;
            rule.dependencies.forEach(function (dep, i) {
                let code  = dep.child_rule ? dep.child_rule.code : '?';
                let title = dep.child_rule ? getTranslatedValue(dep.child_rule.title, '') : `ID: ${dep.child_rule_id}`;
                if (i > 0) {
                    answersHtml += `<div class="dep-chain__connector">
                        <span class="dep-chain__operator dep-chain__operator--${dep.operator.toLowerCase()}">${dep.operator}</span>
                    </div>`;
                }
                answersHtml += `<div class="dep-chain__card">
                    <span class="dep-chain__code">[${code}]</span>
                    <span class="dep-chain__title">${title}</span>
                </div>`;
            });
            answersHtml += `</div></li>`;
        }

        $('#show_rule_answers').html(answersHtml || '<li class="list-group-item text-muted">{{ __('common.no_form_data') }}</li>');
    }

})(jQuery);
</script>
@endpush
