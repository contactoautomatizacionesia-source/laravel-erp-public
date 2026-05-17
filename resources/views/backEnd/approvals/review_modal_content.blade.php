@extends('backEnd.master')

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-xl-12">
                <div class="white_box_30px mb_30">
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade active show" id="review_modal_list_div">
                            <div class="box_header common_table_header ">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('double_approval.all_pending_approvals')}}</h3>
                                </div>
                            </div>
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table">
                                    <!-- table-responsive -->
                                    <div class="">
                                        @include('backEnd.approvals._review_modal_list')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('backEnd.approvals.rejection_modal')
@endsection
@push('scripts')
<script type="text/javascript">
    (function($){
        "use strict";
        let module_check = $('#module_check').val();
        let columnData;
        $(document).ready(function(){
            if(module_check == 'false') {
            columnData = [
                { data: 'module', name: 'module' },
                { data: 'new_data', name: 'new_data' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions',searchable:false,orderable:false }
            ]
        } else {
            columnData = [
                { data: 'module', name: 'module' },
                { data: 'new_data', name: 'new_data' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', searchable:false, orderable:false }
            ]
        }
        reviewModalTable();
        });
        $(document).on('click', '.approve_request', function(e) {
            e.preventDefault();

            // Agrega un spinner mientras se envia la solicitud
            $("#pre-loader").removeClass('d-none');

            // Recupera el id de la solicitud pendiente
            let btn               = $(this);
            let pendingApprovalId = btn.data('id');

            $.ajax({
                url: "{{ route('double_approval.update_status') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: pendingApprovalId,
                    status: 1
                },
                success: function(response) {
                    // Recarga la tabla de datos
                    $('#reviewModalTable').DataTable().ajax.reload(null, false);

                    // Oculta el spinner
                    $("#pre-loader").addClass('d-none');

                    // Muestra mensaje de exito
                    toastr.success("{{ __('common.approved_successfully') }}");
                },
                error: function(xhr) {
                    // Oculta el spinner
                    $("#pre-loader").addClass('d-none');

                    var errorMessage = "{{ __('common.error_message') }}";

                    // Recupera el mensaje de error si existe
                    if (xhr.responseJSON && xhr.responseJSON.details) {
                        errorMessage = xhr.responseJSON.details;
                    };

                    // Muestra mensaje de error
                    toastr.error(errorMessage);
                }
            });
        });

        $(document).on('click', '.reject_request', function(e) {
            e.preventDefault();
            $('#pending_approval_id').val($(this).data('id'));
            $('.pending-approval-fields').removeClass('d-none');
            $('#rejection_reason_modal').modal('show');
        });

        $('#rejection_reason_modal').on('hidden.bs.modal', function () {
            $('#edit_form')[0].reset();
            $('.pending-approval-fields').addClass('d-none');
            $('#pending_approval_id').val('');
            $('.modern-submit-btn').prop('disabled', false).removeClass('disabled-btn');
        });

        $(document).on('submit', '#rejection_reason_modal', function (e) {
            e.preventDefault();

            // Agrega un spinner mientras se envia la solicitud
            $("#pre-loader").removeClass('d-none');

            // Recupera el id de la solicitud pendiente
            let pendingApprovalId = $('#pending_approval_id').val();

            // Recupera el motivo de rechazo, limpia el espacio en blanco
            let rejectionReason = $('#rejection_reason').val().trim();

            $.ajax({
                url: "{{ route('double_approval.update_status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: pendingApprovalId,
                    rejection_reason: rejectionReason,
                    status: 2
                },
                success: function () {
                    // Oculta el spinner
                    $("#pre-loader").addClass('d-none');

                    // Recarga la tabla de datos
                    $('#reviewModalTable').DataTable().ajax.reload(null, false);

                    // Oculta el modal
                    $('#rejection_reason_modal').modal('hide');

                    // Muestra mensaje de exito
                    toastr.success("{{ __('common.rejected_successfully') }}");
                },
                error: function (xhr) {
                    // Oculta el spinner
                    $("#pre-loader").addClass('d-none');

                    var errorMessage = "{{ __('common.error_message') }}";

                    // Recupera el mensaje de error si existe
                    if (xhr.responseJSON && xhr.responseJSON.details) {
                        errorMessage = xhr.responseJSON.details;
                    };

                    // Muestra mensaje de error
                    toastr.error(errorMessage);
                }
            });
        });

        function reviewModalTable(){
            $('#reviewModalTable').DataTable({
                processing: true,
                serverSide: true,
                "stateSave": false,
                "ajax": ( {
                    url: "{{ route('double_approval.review') }}",
                }),
                "initComplete":function(json){
                },
                columns: columnData,
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
                        orientation: 'landscape',
                        pageSize: 'A4',
                        margin: [0, 0, 0, 0],
                        alignment: 'center',
                        header: true,
                        customize : function(doc){
                            var colCount = new Array();
                            var tbl = $('#reviewModalTable');
                            $(tbl).find('tbody tr:first-child td').each(function(){
                                if($(this).attr('colspan')){
                                    for(var i=1;i<=$(this).attr('colspan');$i++){
                                        colCount.push('*');
                                    }
                                }else{ colCount.push('*'); }
                            });
                            doc.content[1].table.widths = colCount;
                        }
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
                    visible: true,
                    targets: [-1],
                    responsivePriority: 1
                }],
                    responsive: true,
            });
        }
    })(jQuery);
</script>
@endpush
