@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                            <li class="nav-item">
                                <a class="nav-link action" href="#" id="btn_create_plan">
                                    <i class="ti-plus mr-2"></i> {{ __('common.new_plan') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="box_header common_table_header">
                    <div class="main-title">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px px-0">{{ __('common.Plans') }}</h3>
                        <p class="text-muted mb-0 align-self-center">{{ __('common.plans_description') }}</p>
                    </div>
                </div>

                <div class="QA_section QA_section_heading_custom check_box_table">
                    <div class="QA_table">
                        <div class="table-responsiv">
                            <table id="plansTable" class="table">
                                <thead>
                                    <tr>
                                        <th style="width:50px"><i class="ti-menu"></i></th>
                                        <th>{{ __('common.plan_order') }}</th>
                                        <th style="width:50px">{{ __('common.image') }}</th>
                                        <th style="width:60px">{{ __('common.color') }}</th>
                                        <th>{{ __('common.title') }}</th>
                                        <th>{{ __('common.network') }}</th>
                                        <th>{{ __('common.plan_scale_order') }}</th>
                                        <th>{{ __('common.subplans') }}</th>
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

@include('plans::components.form_modal')
@include('backEnd.partials.delete_modal', ['item_name' => __('common.plan')])

@endsection

@include('plans::components.utils')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>

<style>
    #plansTable .drag-handle {
        cursor: grab;
        color: #a3a3a3;
        font-size: 14px;
        display: inline-block;
    }
    #plansTable .drag-handle:active { cursor: grabbing; }
    #plansTable tbody tr.sortable-ghost { opacity: 0.4; background: #e8f4fd; }
    #plansTable tbody tr.sortable-chosen { background: #f0f8ff; }
</style>

<script type="text/javascript">
(function($) {
    "use strict";
    var getTranslatedValue = window.plansUtils.getTranslatedValue;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var reorderUrl = "{{ route('plans.reorder') }}";
    var plansSortable = null;
    var table;

    function initPlanSortable() {
        var tbody = document.querySelector('#plansTable tbody');
        if (!tbody) return;
        if (plansSortable) { plansSortable.destroy(); plansSortable = null; }
        plansSortable = Sortable.create(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function() {
                var ids = [];
                $('#plansTable tbody tr').each(function() {
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
                        table.ajax.reload(function() { initPlanSortable(); }, false);
                        $('#pre-loader').addClass('d-none');
                    }
                });
            }
        });
    }

    $(document).ready(function() {
        // DataTable
        let ajaxUrl = "{{ route('plans.get-data') }}";
        var columnData = [
                { data: 'order_drag',     name: 'order', orderable: false, searchable: false },
                { data: 'order_number',   name: 'order_number' },
                { data: 'image_col',      name: 'image', orderable: false, searchable: false },
                { data: 'color_col',      name: 'color', orderable: false, searchable: false },
                { data: 'title',          name: 'title' },
                { data: 'network_type_badge', name: 'is_life_title', orderable: false, searchable: false },
                { data: 'scale_detail',   name: 'scale', orderable: false },
                { data: 'children_badge', name: 'plan_children_count', searchable: false },
                { data: 'status_badge',   name: 'is_active', searchable: false },
                { data: 'action',         name: 'action', orderable: false, searchable: false }
            ];
        table = initGlobalDataTable('#plansTable', ajaxUrl, columnData, {
            createdRow: function(row, data) {
                $(row).attr('data-id', data.id);
            }
        });

        table.on('draw', function() { initPlanSortable(); });

        function resetPlanImageUI() {
            $('#plan_image_input').val('');
            $('#plan_image_name').val('');
            $('#plan_image_thumb').hide().attr('src', '');
            $('#plan_image_icon').show();
            $('#plan_image_clear').hide();
            $('#remove_image').val('0');
        }

        $('#plan_image_input').on('change', function() {
            var file = this.files[0];
            if (!file) return;
            $('#plan_image_name').val(file.name);
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#plan_image_thumb').attr('src', e.target.result).show();
                $('#plan_image_icon').hide();
            };
            reader.readAsDataURL(file);
            $('#plan_image_clear').show();
            $('#remove_image').val('0');
        });

        $('#plan_image_clear').on('click', function() {
            resetPlanImageUI();
            $('#remove_image').val('1');
        });

        // ── SVG icon helpers ────────────────────────────────────────────────────

        /**
         * Minimal client-side SVG validation:
         * - Must be a non-empty string starting with <svg (optional whitespace)
         * - Must not contain event handlers (on*=), <script>, PHP tags, or javascript: URIs
         * - Must not contain <foreignObject> or data: URIs in href/xlink:href
         * Returns an error string or null if valid.
         */
        function validateSvgClient(raw) {
            var s = raw.trim();
            if (!s) return null; // empty is allowed (field is optional)
            if (!/^<svg[\s>]/i.test(s)) {
                return '{{ __("common.icon_error_must_start_svg") }}';
            }
            // Block event handlers
            if (/\bon\w+\s*=/i.test(s)) {
                return '{{ __("common.icon_error_event_handlers") }}';
            }
            // Block <script> tags
            if (/<script[\s>]/i.test(s)) {
                return '{{ __("common.icon_error_script_tag") }}';
            }
            // Block PHP processing instructions
            if (/<\?(?:php|=)/i.test(s)) {
                return '{{ __("common.icon_error_php") }}';
            }
            // Block javascript: URIs
            if (/javascript\s*:/i.test(s)) {
                return '{{ __("common.icon_error_js_uri") }}';
            }
            // Block data: URIs in href attributes
            if (/href\s*=\s*["']?\s*data\s*:/i.test(s)) {
                return '{{ __("common.icon_error_data_uri") }}';
            }
            // Block <foreignObject>
            if (/<foreignObject[\s>]/i.test(s)) {
                return '{{ __("common.icon_error_foreign_object") }}';
            }
            return null;
        }

        function resetPlanIconUI() {
            $('#plan_icon_textarea').val('');
            $('#plan_icon_render').html('').hide();
            $('#plan_icon_placeholder').show();
            $('#plan_icon_clear').hide();
            $('#remove_icon').val('0');
            $('#plan_icon_help').text('{{ __("common.icon_svg_help") }}').removeClass('text-danger');
        }

        function renderIconPreview(svgText) {
            var err = validateSvgClient(svgText);
            if (err) {
                $('#plan_icon_render').html('').hide();
                $('#plan_icon_placeholder').show();
                $('#plan_icon_help').text(err).addClass('text-danger');
                return false;
            }
            if (!svgText.trim()) {
                $('#plan_icon_render').html('').hide();
                $('#plan_icon_placeholder').show();
                $('#plan_icon_help').text('{{ __("common.icon_svg_help") }}').removeClass('text-danger');
                return true;
            }
            // Safely set innerHTML via DOMParser to avoid direct injection
            try {
                var parser = new DOMParser();
                var doc = parser.parseFromString(svgText.trim(), 'image/svg+xml');
                var parseError = doc.querySelector('parsererror');
                if (parseError) {
                    $('#plan_icon_render').html('').hide();
                    $('#plan_icon_placeholder').show();
                    $('#plan_icon_help').text('{{ __("common.icon_error_invalid_xml") }}').addClass('text-danger');
                    return false;
                }
                var svgNode = doc.documentElement;
                svgNode.setAttribute('width', '44');
                svgNode.setAttribute('height', '44');
                $('#plan_icon_render').empty().append(document.importNode(svgNode, true)).show();
                $('#plan_icon_placeholder').hide();
                $('#plan_icon_help').text('{{ __("common.icon_svg_help") }}').removeClass('text-danger');
                return true;
            } catch(ex) {
                $('#plan_icon_render').html('').hide();
                $('#plan_icon_placeholder').show();
                $('#plan_icon_help').text('{{ __("common.icon_error_invalid_xml") }}').addClass('text-danger');
                return false;
            }
        }

        $('#plan_icon_textarea').on('input', function() {
            var val = $(this).val();
            renderIconPreview(val);
            if (val.trim()) {
                $('#plan_icon_clear').show();
                $('#remove_icon').val('0');
            } else {
                $('#plan_icon_clear').hide();
            }
        });

        $('#plan_icon_clear').on('click', function() {
            resetPlanIconUI();
            $('#remove_icon').val('1');
        });

        // ── Color helpers ────────────────────────────────────────────────────────

        // Regex centralizado: acepta #rgb o #rrggbb, rechaza #rgba, #1234, etc.
        var HEX_COLOR_RE = /^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/;

        function expandHex(hex) {
            // Convierte #abc → #aabbcc para input[type=color]
            if (/^#[0-9A-Fa-f]{3}$/.test(hex)) {
                return '#' + hex[1]+hex[1] + hex[2]+hex[2] + hex[3]+hex[3];
            }
            return hex;
        }

        $('#plan_primary_color').on('input', function() {
            $('#plan_primary_color_hex').val($(this).val());
        });
        $('#plan_primary_color_hex').on('input', function() {
            var hex = $(this).val().trim();
            if (HEX_COLOR_RE.test(hex)) {
                $('#plan_primary_color').val(expandHex(hex));
            }
        });

        // Open create modal
        $('#btn_create_plan').on('click', function(e) {
            e.preventDefault();
            $('#planForm')[0].reset();
            $('#plan_id').val('');
            $('#scale_type').trigger('change');
            $('#is_life_title').prop('checked', false);
            resetPlanImageUI();
            resetPlanIconUI();
            $('#plan_primary_color').val('#4A90D9');
            $('#plan_primary_color_hex').val('#4A90D9');
            $('#planFormModalLabel').text('{{ __('common.new_plan') }}');
            $('#planFormModal').modal('show');
        });

        // Submit form (FormData para soportar archivos)
        $('#planForm').on('submit', function(e) {
            e.preventDefault();

            // Validate SVG icon before submit
            var iconVal = $('#plan_icon_textarea').val().trim();
            var iconErr = validateSvgClient(iconVal);
            if (iconErr) {
                toastr.error(iconErr, '{{ __("common.error") }}');
                return;
            }

            $('#pre-loader').removeClass('d-none');
            var planId = $('#plan_id').val();
            var url    = planId ? '/plans/' + planId + '/update' : "{{ route('plans.store') }}";
            var fd     = new FormData(this);
            $.ajax({
                url: url, type: 'POST', data: fd,
                processData: false, contentType: false,
                success: function(r) {
                    if (r.success) {
                        toastr.success(r.message, "{{ __('common.success') }}");
                        $('#planFormModal').modal('hide');
                        table.ajax.reload(function() { initPlanSortable(); }, false);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || "{{ __('common.server_error') }}", "{{ __('common.error') }}");
                },
                complete: function() { $('#pre-loader').addClass('d-none'); }
            });
        });

        // Edit
        $(document).on('click', '.edit_plan', function(e) {
            e.preventDefault();
            $('#pre-loader').removeClass('d-none');
            $.get('/plans/' + $(this).data('value') + '/edit', function(plan) {
                $('#plan_id').val(plan.id);
                $('#title').val(getTranslatedValue(plan.title, ''));
                $('#description').val(getTranslatedValue(plan.description, ''));
                $('#order').val(plan.order);
                $('#is_active').prop('checked', plan.is_active);
                $('#is_life_title').prop('checked', !!plan.is_life_title);
                $('#scale_type').val(plan.scale_type).trigger('change');

                // Imagen
                resetPlanImageUI();
                if (plan.image_url) {
                    $('#plan_image_thumb').attr('src', plan.image_url).show();
                    $('#plan_image_icon').hide();
                    $('#plan_image_name').val(plan.image.split('/').pop());
                    $('#plan_image_clear').show();
                }

                // Color
                var color = (plan.styles && plan.styles.primaryColor) ? plan.styles.primaryColor : '#4A90D9';
                $('#plan_primary_color').val(color);
                $('#plan_primary_color_hex').val(color);

                // Icono SVG
                resetPlanIconUI();
                if (plan.icon) {
                    $('#plan_icon_textarea').val(plan.icon);
                    renderIconPreview(plan.icon);
                    $('#plan_icon_clear').show();
                }

                $('#planFormModalLabel').text('{{ __('common.edit_plan') }}');
                $('#planFormModal').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });

        // Delete
        $(document).on('click', '.delete_plan', function(e) {
            e.preventDefault();
            confirm_modal(`/plans/${$(this).data('value')}/destroy`);
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
                    table.ajax.reload(function() { initPlanSortable(); }, false);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || "{{ __('common.error') }}", "{{ __('common.error') }}");
                    $('#confirm-delete').modal('hide');
                },
                complete: function() { $('#pre-loader').addClass('d-none'); }
            });
        });
    });
})(jQuery);
</script>
@endpush
