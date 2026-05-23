@push('scripts')
    <script>
        (function($) {
        	"use strict";
            $(document).ready(function(){

                YajraReActive();

                $(document).on('submit', '#create_form', function(event){
                    event.preventDefault();
                    $('#pre-loader').removeClass('d-none');

                    let formElement = $(this).serializeArray()
                    let formData = new FormData();
                    formElement.forEach(element => {
                        formData.append(element.name,element.value);
                    });

                    formData.append('_token',"{{ csrf_token() }}");


                    resetValidationError();
                    $.ajax({
                        url: "{{ route('setup.state.store')}}",
                        type:"POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success:function(response){
                            resetAfterChange(response.TableData);
                            create_form_reset();
                            toastr.success("{{__('common.added_successfully')}}", "{{__('common.success')}}");
                            $('#pre-loader').addClass('d-none');


                        },
                        error:function(response) {
                            if(response.responseJSON.error){
                                toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                                $('#pre-loader').addClass('d-none');
                                return false;
                            }
                            showValidationErrors('#create_form',response.responseJSON.errors);
                            $('#pre-loader').addClass('d-none');
                        }
                    });
                });

                $(document).on('submit', '#edit_form', function(event){
                    event.preventDefault();
                    $('#pre-loader').removeClass('d-none');
                    let formElement = $(this).serializeArray()
                    let formData = new FormData();
                    formElement.forEach(element => {
                        formData.append(element.name,element.value);
                    });

                    formData.append('_token',"{{ csrf_token() }}");
                    resetValidationError();
                    $.ajax({
                        url: "{{ route('setup.state.update')}}",
                        type:"POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success:function(response){
                            resetAfterChange(response.TableData);
                            toastr.success("{{__('common.updated_successfully')}}", "{{__('common.success')}}");
                            $('#pre-loader').addClass('d-none');
                            $('#formHtml').html(response.createForm);
                            $('#country').niceSelect();

                        },
                        error:function(response) {
                            if(response.responseJSON.error){
                                toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                                $('#pre-loader').addClass('d-none');
                                return false;
                            }
                            showValidationErrors('#edit_form',response.responseJSON.errors);
                            $('#pre-loader').addClass('d-none');
                        }
                    });
                });

                $(document).on('click', '.edit_state', function(event){
                    event.preventDefault();
                    $('#pre-loader').removeClass('d-none');
                    let id = $(this).data('id');
                    let base_url = $('#url').val();
                    let url = base_url + '/setup/location/state/edit/' +id;
                    $.get(url, function(response){
                        if(response){
                            $('#formHtml').html(response);
                            $('#country').niceSelect();
                        }
                        $('#pre-loader').addClass('d-none');
                    });

                });

                let statusChangeData = {};

                $(document).on('change', '.status_change', function(event){
                    event.preventDefault();
                    
                    let checkbox = $(this);
                    let status = checkbox.prop('checked') ? 1 : 0;
                    let id = checkbox.data('id');

                    // Si se está inactivando (status = 0), mostrar preview de cascada
                    if(status === 0) {
                        $('#pre-loader').removeClass('d-none');
                        
                        // Guardar datos para usar después de la confirmación
                        statusChangeData = {
                            id: id,
                            status: status,
                            checkbox: checkbox
                        };

                        $.ajax({
                            url: "{{ route('setup.country.preview-cascade') }}",
                            type: "POST",
                            data: {
                                '_token': "{{ csrf_token() }}",
                                'type': 'state',
                                'id': id
                            },
                            success: function(response) {
                                $('#pre-loader').addClass('d-none');
                                
                                if(response.impact) {
                                    $('#cascadeStatesCount').text(response.impact.states || 0);
                                    $('#cascadeCitiesCount').text(response.impact.cities || 0);
                                    $('#cascadeConfirmModal').modal('show');
                                }
                            },
                            error: function(response) {
                                $('#pre-loader').addClass('d-none');
                                // Revertir el checkbox
                                checkbox.prop('checked', true);
                                
                                var msg = (response.responseJSON && response.responseJSON.message)
                                    ? response.responseJSON.message
                                    : 'Ocurrió un error';
                                toastr.error(msg, "{{__('common.error')}}");
                            }
                        });
                    } else {
                        // Si se está activando, proceder normalmente
                        executeStatusChange(id, status);
                    }
                });

                // Confirmar cascada
                $(document).on('click', '#cascadeConfirmBtn', function(event){
                    event.preventDefault();
                    $('#cascadeConfirmModal').modal('hide');
                    
                    if(statusChangeData.id && statusChangeData.status !== undefined) {
                        executeStatusChange(statusChangeData.id, statusChangeData.status);
                    }
                });

                // Cancelar modal - revertir checkbox
                $('#cascadeConfirmModal').on('hidden.bs.modal', function () {
                    // Si el botón de confirmar no fue clickeado, revertir
                    if(statusChangeData.checkbox) {
                        statusChangeData.checkbox.prop('checked', true);
                        statusChangeData = {};
                    }
                });

                function executeStatusChange(id, status) {
                    $('#pre-loader').removeClass('d-none');
                    let formData = new FormData();
                    formData.append('_token', "{{ csrf_token() }}");
                    formData.append('id', id);
                    formData.append('status', status);

                    $.ajax({
                        url: "{{ route('setup.state.status') }}",
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {
                            toastr.success("{{__('common.updated_successfully')}}", "{{__('common.success')}}");
                            $('#pre-loader').addClass('d-none');
                        },
                        error: function(xhr) {
                            $('#pre-loader').addClass('d-none');

                            var numericStatus = parseInt(status, 10);
                            var $checkbox = $('#checkbox' + id);
                            // Revertir el estado previo del toggle
                            $checkbox.prop('checked', numericStatus === 0);

                            var msg = (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'Ocurrió un error';
                            toastr.error(msg, "{{__('common.error')}}");
                        }
                    });
                }

                function YajraReActive(){

                    $('#allData').DataTable({
                        processing: true,
                        serverSide: true,
                        stateSave: true,
                        ajax: "{{ route('setup.state.getData') }}",
                        columns: [
                            { data: 'DT_RowIndex', name: 'id' },
                            { data: 'name', name: 'name' },
                            { data: 'country', name: 'country.name' },
                            { data: 'status', name: 'status' },
                            { data: 'action', name: 'action' }

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
                function resetAfterChange(TableData){
                    $('#item_table').html(TableData);
                    YajraReActive();
                }

                function create_form_reset(){
                    $('#create_form')[0].reset();

                }

                function showValidationErrors(formType, errors){
                    $(formType +' #error_name').text(errors.name);
                    $(formType +' #error_country').text(errors.country);
                }

                function resetValidationError(){
                    $('#error_name').html('');
                    $('#error_country').html('');
                }

            });
        })(jQuery);
    </script>
@endpush
