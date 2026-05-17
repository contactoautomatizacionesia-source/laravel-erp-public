@extends('backEnd.master')
@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid white_box_30px">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="box_header">
                        <div class="main-title d-flex">
                            <h3 class="mb-0 mr-30">{{ __('common.activity_logs') }}</h3>
                            <ul class="d-flex">
                                @if (permissionCheck('activity_log.destroy_all'))
                                    <li><a class="primary-btn radius_30px mr-10 fix-gr-bg" href=""
                                            id="clear_log_btn">{{ __('common.clean_all') }}</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table">
                            <div class="table-responsive" id="log_table_div">
                                @include('useractivitylog::components.log_list')
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('backEnd.partials._deleteModalForAjax', ['item_name' => __('common.activity_logs')])
    </section>
@endsection

@push('scripts')
    <script>
        (function($) {
            var showMoreMessage = "{{ __('common.show_more') }}";
            var showLessMessage = "{{ __('common.show_less') }}";
            "use strict";
            $(document).ready(function() {
                activeDataTable();

                $(document).on('click', '#clear_log_btn', function(event) {
                    event.preventDefault();
                    $('#deleteItemModal').modal('show');
                });

                $(document).on('submit', '#item_delete_form', function(event) {
                    event.preventDefault();
                    $('#deleteItemModal').modal('hide');
                    $("#pre-loader").removeClass('d-none');
                    var formData = new FormData();
                    formData.append('_token', "{{ csrf_token() }}");
                    $.ajax({
                        url: "{{ route('activity_log.destroy_all') }}",
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {
                            $('#log_table_div').html(response.log_lists);
                            $("#pre-loader").addClass('d-none');
                            toastr.success("{{ __('common.deleted_successfully') }}",
                                "{{ __('common.success') }}");
                            activeDataTable();
                        },
                        error: function(response) {
                            if (response.responseJSON.error) {
                                toastr.error(response.responseJSON.error,
                                    "{{ __('common.error') }}");
                                $('#pre-loader').addClass('d-none');
                                return false;
                            }
                            toastr.error("{{ __('common.error_message') }}",
                                "{{ __('common.error') }}");
                            $("#pre-loader").addClass('d-none');
                        }
                    });
                });

                function activeDataTable() {
                    var url = "{{ route('activity_log.get-data') }}";
                    $('#activityDataTable').DataTable({
                        processing: true,
                        serverSide: true,
                        stateSave: true,
                        "ajax": ({
                            url: url
                        }),
                        "initComplete": function(json) {

                        },
                        columns: [{
                                className: 'details-control',
                                orderable: false,
                                data: null,
                                defaultContent: '<i class="ti-plus" style="cursor:pointer; color:var(--base_color)"></i>',
                            },
                            {
                                data: 'DT_RowIndex',
                                name: 'id',
                                render: function(data) {
                                    return numbertrans(data)
                                }
                            },
                            {
                                data: 'type',
                                name: 'type'
                            },
                            {
                                data: 'subject',
                                name: 'subject',
                                render: function(data) {
                                    if (!data || data.length <= 60) {
                                        return data || '';
                                    }
                                    return `
                                        <div class="subject-cell">
                                            <span class="subject-short">
                                                ${data.substring(0, 60)}
                                                <span class="subject-ellipsis">...</span>
                                            </span>
                                            <span class="subject-full d-none">
                                                ${data}
                                            </span>
                                            <a href="javascript:void(0)" class="subject-toggle ml-1"
                                            style="color:var(--base_color); font-size:11px; white-space:nowrap;">
                                                <i class="ti-plus"></i> ${showMoreMessage}
                                            </a>
                                        </div>
                                    `;
                                }
                            },

                            {
                                data: 'url',
                                name: 'url'
                            },
                            {
                                data: 'ip',
                                name: 'ip'
                            },
                            {
                                data: 'agent',
                                name: 'agent'
                            },
                            {
                                data: 'attempt_at',
                                name: 'attempt_at'
                            },
                            {
                                data: 'user_name',
                                name: 'user_name'
                            }

                        ],

                        bLengthChange: false,
                        "bDestroy": true,
                        language: {
                            search: "<i class='ti-search'></i>",
                            searchPlaceholder: trans('common.quick_search'),
                            paginate: {
                                next: "<i class='ti-arrow-right'></i>",
                                previous: "<i class='ti-arrow-left'></i>"
                            }
                        },
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'copyHtml5',
                                text: '<i class="fa fa-files-o"></i>',
                                title: $("#header_title").text(),
                                titleAttr: 'Copy',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i>',
                                titleAttr: 'Excel',
                                title: $("#header_title").text(),
                                margin: [10, 10, 10, 0],
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },

                            },
                            {
                                extend: 'csvHtml5',
                                text: '<i class="fa fa-file-text-o"></i>',
                                titleAttr: 'CSV',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i>',
                                title: $("#header_title").text(),
                                titleAttr: 'PDF',
                                exportOptions: {
                                    columns: ':visible',
                                    columns: ':not(:last-child)',
                                },
                                pageSize: 'A4',
                                margin: [0, 0, 0, 0],
                                alignment: 'center',
                                header: true,

                            },
                            {
                                extend: 'print',
                                text: '<i class="fa fa-print"></i>',
                                titleAttr: 'Print',
                                title: $("#header_title").text(),
                                exportOptions: {
                                    columns: ':not(:last-child)',
                                }
                            },
                            {
                                extend: 'colvis',
                                text: '<i class="fa fa-columns"></i>',
                                postfixButtons: ['colvisRestore']
                            }
                        ],
                        columnDefs: [{
                            visible: false
                        }],
                        responsive: true,
                    });
                }

                // Función para generar el contenido desplegable (puedes personalizar el diseño aquí)
                function formatUser(d) {
                    if (!d.user) {
                        return '<div class="p-3 text-center">Sin información de usuario detallada.</div>';
                    }

                    const fullName = d.user.name || `${d.user.first_name || ''} ${d.user.last_name || ''}`.trim();

                    return `
                        <div class="user-details-container">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="user-details-title">Información General</h6>
                                    <p class="user-details-info"><strong>Nombre:</strong> ${fullName}</p>
                                    <p class="user-details-info"><strong>Email:</strong> ${d.user.email || 'N/A'}</p>
                                    <p class="user-details-info"><strong>Username:</strong> ${d.user.username || 'N/A'}</p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="user-details-title">Estado y Seguridad</h6>
                                    <p class="user-details-info"><strong>Estado:</strong> ${d.user.is_active ? '<span class="badge_1">Activo</span>' : '<span class="badge_4">Inactivo</span>'}</p>
                                    <p class="user-details-info"><strong>Verificado:</strong> ${d.user.is_verified ? 'Sí' : 'No'}</p>
                                    <p class="user-details-info"><strong>Moneda:</strong> ${d.user.currency_code || 'N/A'}</p>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="user-details-title">Preferencias</h6>
                                    <p class="user-details-info"><strong>Idioma:</strong> ${d.user.lang_code ? d.user.lang_code.toUpperCase() : 'N/A'}</p>
                                    <p class="user-details-info"><strong>Notificación:</strong> ${d.user.notification_preference || 'S/N'}</p>
                                    <p class="user-details-info"><strong>IP de Acción:</strong> ${d.ip || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
                $(document).on('click', '.subject-toggle', function () {
                    var cell = $(this).closest('.subject-cell');
                    var short = cell.find('.subject-short');
                    var full  = cell.find('.subject-full');

                    if (full.hasClass('d-none')) {
                        short.addClass('d-none');
                        full.removeClass('d-none');
                        $(this).html(`<i class="ti-minus"></i> ${showLessMessage}`);
                    } else {
                        full.addClass('d-none');
                        short.removeClass('d-none');
                        $(this).html(`<i class="ti-plus"></i> ${showMoreMessage}`);
                    }
                });
                // Evento Click para expandir la fila
                $(document).on('click', '#activityDataTable tbody td.details-control', function () {
                    var tr = $(this).closest('tr');

                    // Obtenemos la instancia directamente del ID de la tabla
                    var table = $('#activityDataTable').DataTable();
                    var row = table.row(tr);

                    if (row.child.isShown()) {
                        // Esta fila ya está abierta - cerrarla
                        row.child.hide();
                        tr.removeClass('shown');
                        $(this).find('i').removeClass('ti-minus').addClass('ti-plus');
                    } else {
                        // Abrir esta fila
                        row.child(formatUser(row.data())).show();
                        tr.addClass('shown');
                        $(this).find('i').removeClass('ti-plus').addClass('ti-minus');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
