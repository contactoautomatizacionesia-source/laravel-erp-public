@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-md-flex justify-content-between">
                    <div class="main-title d-md-flex align-items-start">
                        <x-backEnd.back-button :text="false" />
                        <div>
                            <h3 class="mb-0 px-0">{{ __('common.subplans_of', ['name' => $plan->title]) }}</h3>
                            <p class="  text-muted mb-0 align-self-center ">{{ $plan->description ?? __('common.no_description') }}</p>
                            <span class="badge badge-secondary px-2 py-1">{{ $plan->scale }}{{ $plan->cycle_type ? ' · ' . $plan->cycle_type : '' }}{{ $plan->custom_days ? ' · ' . $plan->custom_days . ' días' : '' }}</span>
                        </div>
                    </div>
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0 mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('plans.index') }}">{{ __('common.Plans') }}</a></li>
                                <li class="breadcrumb-item active">{{ $plan->title }} — {{ __('common.Subplans') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link action" href="#" id="btn_create_child">
                                    <i class="ti-plus mr-2"></i> {{ __('common.new_subplan') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <p class="text-muted mb-0 align-self-center">{{ __('common.subplan_description') }}</p>
            </div>

            <div class="col-xl-12">
                <div class="QA_section QA_section_heading_custom check_box_table">
                    <div class="QA_table">
                        <div class="table-responsive ign-scrollbar">
                            <table id="childrenTable" class="table">
                                <thead>
                                    <tr>
                                        <th style="width:50px"><i class="ti-menu"></i></th>
                                        <th>{{ __('common.plan_order') }}</th>
                                        <th>{{ __('common.title') }}</th>
                                        <th>{{ __('common.description') }}</th>
                                        <th>{{ __('common.rules_catalog') }}</th>
                                        <th>{{ __('common.benefits_catalog') }}</th>
                                        <th>{{ __('common.status') }}</th>
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

@include('plans::children.components.form_modal')
@include('plans::children.components.assignments_modal')
@include('backEnd.partials.delete_modal', ['item_name' => __('common.subplan')])

@endsection

@include('plans::components.utils')

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" integrity="sha256-9/1mJJNpvNo/341ptfdv39tqI832BuZx7Jq5DyqX1D4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>

<style>

    .rule-item { border: 1px solid #dee2e6; border-radius: 6px; padding: 8px 12px; margin-bottom: 6px; cursor: pointer; transition: background .15s; }
    .rule-item:hover { background-color: #f8f9fa; }
    .rule-item.selected { background-color: #e8f4fd; border-color: #7ab3d3; }
    .benefit-item { border: 1px solid #dee2e6; border-radius: 6px; padding: 8px 12px; margin-bottom: 6px; cursor: pointer; transition: background .15s; }
    .benefit-item:hover { background-color: #f8f9fa; }
    .benefit-item.selected { background-color: #eafbe8; border-color: #8dae58; }

    #childrenTable .drag-handle {
        cursor: grab;
        color: #a3a3a3;
        font-size: 14px;
        display: inline-block;
    }
    #childrenTable .drag-handle:active { cursor: grabbing; }
    #childrenTable tbody tr.sortable-ghost { opacity: 0.4; background: #e8f4fd; }
    #childrenTable tbody tr.sortable-chosen { background: #f0f8ff; }

    
</style>

<script type="text/javascript">
(function($) {
    "use strict";
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    var getTranslatedValue = window.plansUtils.getTranslatedValue;

    const planId = {{ $plan->id }};
    const reorderUrl = `/plans/${planId}/children/reorder`;
    let childrenSortable = null;
    let table;
    let currentChildId = null;

    function initChildSortable() {
        var tbody = document.querySelector('#childrenTable tbody');
        if (!tbody) return;
        if (childrenSortable) { childrenSortable.destroy(); childrenSortable = null; }
        childrenSortable = Sortable.create(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function() {
                var ids = [];
                $('#childrenTable tbody tr').each(function() {
                    var id = parseInt($(this).data('id'), 10);
                    if (id) ids.push(id);
                });
                if (!ids.length) return;
                var formData = '_token={{ csrf_token() }}';
                ids.forEach(function(id) {
                    formData += '&ids[]=' + encodeURIComponent(id);
                });
                $('#pre-loader').removeClass('d-none');
                $.ajax({
                    url: reorderUrl, type: 'POST', data: formData,
                    success: function(r) {
                        if (r.success) {
                            toastr.success('{{ __("common.order_updated") }}');
                        } else {
                            toastr.error(r.error || '{{ __("common.server_error") }}');
                        }
                    },
                    error: function() {
                        toastr.error('{{ __("common.server_error") }}');
                    },
                    complete: function() {
                        table.ajax.reload(function() { initChildSortable(); }, false);
                        $('#pre-loader').addClass('d-none');
                    }
                });
            }
        });
    }

    $(document).ready(function() {

        // DataTable
        table = $('#childrenTable').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            paging: false,
            ordering: false,
            ajax: { url: `/plans/${planId}/children/get-data` },
            columns: [
                { data: 'level_order_drag', name: 'level_order', orderable: false, searchable: false },
                { data: 'order_number',     name: 'order_number' },
                { data: 'title',            name: 'title' },
                { data: 'description',      name: 'description', orderable: false, defaultContent: '<span class="text-muted">—</span>' },
                { data: 'rules_badge',      name: 'rules_count', searchable: false },
                { data: 'benefits_badge',   name: 'benefits_count', searchable: false },
                { data: 'status_badge',     name: 'is_active', searchable: false },
                { data: 'action',           name: 'action', orderable: false, searchable: false }
            ],
            bLengthChange: false,
            bDestroy: true,
            language: { search: "<i class='ti-search'></i>", searchPlaceholder: "{{ __('common.search_subplan') }}" },
            responsive: true,
            createdRow: function(row, data) {
                $(row).attr('data-id', data.id);
            },
            columnDefs: [{
                targets: [-1],
                responsivePriority: 1
            }]
        });

        table.on('draw', function() { initChildSortable(); });

        // =================== CREATE / EDIT CHILD ===================

        $('#btn_create_child').on('click', function(e) {
            e.preventDefault();
            $('#childForm')[0].reset();
            $('#child_id').val('');
            $('#childFormModalLabel').text('{{ __('common.new_subplan') }}');
            $('#childFormModal').modal('show');
        });

        $('#childForm').on('submit', function(e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            let childId = $('#child_id').val();
            let url = childId ? `/plans/${planId}/children/${childId}/update` : `/plans/${planId}/children/store`;
            $.ajax({
                url: url, type: 'POST', data: $(this).serialize(),
                success: function(r) {
                    if (r.success) {
                        toastr.success(r.message, "{{ __('common.success') }}");
                        $('#childFormModal').modal('hide');
                        table.ajax.reload(function() { initChildSortable(); }, false);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || "{{ __('common.server_error') }}", "{{ __('common.error') }}");
                },
                complete: function() { $('#pre-loader').addClass('d-none'); }
            });
        });

        $(document).on('click', '.btn-edit-child', function(e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            let id = $(this).data('value');
            $.get(`/plans/${planId}/children/${id}/edit`, function(child) {
                $('#child_id').val(child.id);
                $('#child_title').val(getTranslatedValue(child.title, ''));
                $('#child_description').val(getTranslatedValue(child.description, ''));
                $('#child_level_order').val(child.level_order);
                $('#child_is_active').prop('checked', child.is_active);
                $('#childFormModalLabel').text('{{ __('common.edit_subplan') }}');
                $('#childFormModal').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });

        // =================== DELETE ===================
        $(document).on('click', '.btn-delete-child', function(e) {
            e.preventDefault();
            let id = $(this).data('value');
            confirm_modal(`/plans/${planId}/children/${id}/destroy`);
        });

        $(document).on('click', '#delete_link', function(e) {
            e.preventDefault();
            let url = $(this).attr('href');
            $('#pre-loader').removeClass('d-none');
            $.ajax({
                url: url, type: 'POST', data: { _token: '{{ csrf_token() }}' },
                success: function(r) {
                    toastr.success(r.message || "{{ __('common.deleted_successfully') }}", "{{ __('common.success') }}");
                    $('#confirm-delete').modal('hide');
                    table.ajax.reload(function() { initChildSortable(); }, false);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || "{{ __('common.error') }}", "{{ __('common.error') }}");
                    $('#confirm-delete').modal('hide');
                },
                complete: function() { $('#pre-loader').addClass('d-none'); }
            });
        });

        // =================== ASSIGNMENTS (Rules & Benefits) ===================

        $(document).on('click', '.btn-assign', function(e) {
            e.preventDefault();
            currentChildId = $(this).data('value');
            $('#pre-loader').removeClass('d-none');

            // Load available rules and benefits, plus current assignments
            Promise.all([
                $.get(`/plans/${planId}/children/available-rules`),
                $.get(`/plans/${planId}/children/available-benefits`),
                $.get(`/plans/${planId}/children/${currentChildId}/assignments`)
            ]).then(function([availRules, availBenefits, assignments]) {
                renderRulesList(availRules, assignments.rules);
                renderBenefitsList(availBenefits, assignments.benefits);
                $('#assignModalChildTitle').text(assignments.child.title);
                $('#assignmentsModal').modal('show');
                $('#pre-loader').addClass('d-none');
            }).catch(function() {
                toastr.error('Error al cargar asignaciones.', 'Error');
                $('#pre-loader').addClass('d-none');
            });
        });

        function renderRulesList(available, assigned) {
            let assignedIds = assigned.map(r => r.id);
            let assignedRequiredMap = {};
            assigned.forEach(r => assignedRequiredMap[r.id] = r.is_required);

            let html = '';
            available.forEach(function(rule) {
                let isSelected = assignedIds.includes(rule.id);
                let isRequired = assignedRequiredMap[rule.id] !== undefined ? assignedRequiredMap[rule.id] : true;
                let rTitle = getTranslatedValue(rule.title, '');
                let rSearch = rule.search || (rule.code + ' ' + rTitle).toLowerCase();
                html += `<div class="rule-item ${isSelected ? 'selected' : ''}" data-id="${rule.id}" data-search="${rSearch}">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="rule-check mr-3" data-id="${rule.id}" ${isSelected ? 'checked' : ''}>
                        <div class="flex-grow-1">
                            <strong>[${rule.code}]</strong> ${rTitle}
                            <br><small class="text-muted">${rule.category}</small>
                        </div>
                        <div class="ml-2 rule-required-wrap" style="min-width:90px;${isSelected ? '' : 'display:none;'}">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input rule-required" data-id="${rule.id}" ${isRequired ? 'checked' : ''}>
                                <label class="form-check-label small">{{ __('common.mandatory_rule') }}</label>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            $('#rules-assignment-list').html(html || '<p class="text-muted">{{ __('common.no_rules_available') }}.</p>');
        }

        function renderBenefitsList(available, assigned) {
            let assignedIds = assigned.map(b => b.id);
            let html = '';
            available.forEach(function(benefit) {
                let isSelected = assignedIds.includes(benefit.id);
                let bTitle = getTranslatedValue(benefit.title, '');
                let bSearch = benefit.search || ((benefit.code || '') + ' ' + bTitle).toLowerCase();
                html += `<div class="benefit-item ${isSelected ? 'selected' : ''}" data-id="${benefit.id}" data-search="${bSearch}">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="benefit-check mr-3" data-id="${benefit.id}" ${isSelected ? 'checked' : ''}>
                        <div class="flex-grow-1">
                            <strong>${benefit.code ? '[' + benefit.code + '] ' : ''}${bTitle}</strong>
                            <br><small class="text-muted">${benefit.category}</small>
                        </div>
                    </div>
                </div>`;
            });
            $('#benefits-assignment-list').html(html || '<p class="text-muted">{{ __('common.no_benefits_available') }}.</p>');
        }

        // Toggle rule selection
        $(document).on('change', '.rule-check', function() {
            let id = $(this).data('id');
            let item = $(this).closest('.rule-item');
            if ($(this).is(':checked')) {
                item.addClass('selected');
                item.find('.rule-required-wrap').show();
            } else {
                item.removeClass('selected');
                item.find('.rule-required-wrap').hide();
            }
        });

        // Toggle benefit selection
        $(document).on('change', '.benefit-check', function() {
            let item = $(this).closest('.benefit-item');
            if ($(this).is(':checked')) item.addClass('selected'); else item.removeClass('selected');
        });

        // Search rules
        $('#assignmentsModal').on('input', '#rules-search', function() {
            let q = $(this).val().toLowerCase();
            $('#rules-assignment-list .rule-item').each(function() {
                let ds = $(this).attr('data-search');
                $(this).toggle(q === '' || (ds || '').includes(q));
            });
        });

        // Search benefits
        $('#assignmentsModal').on('input', '#benefits-search', function() {
            let q = $(this).val().toLowerCase();
            $('#benefits-assignment-list .benefit-item').each(function() {
                let ds = $(this).attr('data-search');
                $(this).toggle(q === '' || (ds || '').includes(q));
            });
        });

        // Clear search on modal close
        $('#assignmentsModal').on('hidden.bs.modal', function() {
            $('#rules-search').val('');
            $('#benefits-search').val('');
            $('#rules-assignment-list .rule-item').show();
            $('#benefits-assignment-list .benefit-item').show();
        });

        // Save rules
        $('#btn-save-rules').on('click', function() {
            let formData = '_token={{ csrf_token() }}';
            let i = 0;
            $('.rule-check:checked').each(function() {
                let id = $(this).data('id');
                let isRequired = $('.rule-required[data-id="' + id + '"]').is(':checked') ? 1 : 0;
                formData += '&rules[' + i + '][rule_id]=' + encodeURIComponent(id);
                formData += '&rules[' + i + '][is_required]=' + encodeURIComponent(isRequired);
                i++;
            });

            $('#pre-loader').removeClass('d-none');
            $.ajax({
                url: `/plans/${planId}/children/${currentChildId}/assign-rules`,
                type: 'POST',
                data: formData,
                success: function(r) {
                    if (r.success) { toastr.success(r.message); table.ajax.reload(function() { initChildSortable(); }, false); }
                },
                error: function(xhr) { toastr.error(xhr.responseJSON?.error || 'Error'); },
                complete: function() { $('#pre-loader').addClass('d-none'); }
            });
        });

        // Save benefits
        $('#btn-save-benefits').on('click', function() {
            let formData = '_token={{ csrf_token() }}';
            $('.benefit-check:checked').each(function() {
                formData += '&benefit_ids[]=' + encodeURIComponent($(this).data('id'));
            });

            $('#pre-loader').removeClass('d-none');
            $.ajax({
                url: `/plans/${planId}/children/${currentChildId}/assign-benefits`,
                type: 'POST',
                data: formData,
                success: function(r) {
                    if (r.success) { toastr.success(r.message); table.ajax.reload(function() { initChildSortable(); }, false); }
                },
                error: function(xhr) { toastr.error(xhr.responseJSON?.error || 'Error'); },
                complete: function() { $('#pre-loader').addClass('d-none'); }
            });
        });

    });
})(jQuery);
</script>
@endpush
