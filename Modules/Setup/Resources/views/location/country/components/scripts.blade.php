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

                    let flag = $('#flag')[0].files[0];

                    if(flag){
                        formData.append('flag',flag);
                    }
                    formData.append('_token',"{{ csrf_token() }}");


                    resetValidationError();
                    $.ajax({
                        url: "{{ route('setup.country.store')}}",
                        type:"POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success:function(response){
                            resetAfterChange();
                            create_form_reset();
                            toastr.success("{{__('common.added_successfully')}}", "{{__('common.success')}}");
                            $('#pre-loader').addClass('d-none');
                            $('#continent').niceSelect();

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

                    let flag = $('#flag')[0].files[0];

                    if(flag){
                        formData.append('flag',flag);
                    }
                    formData.append('_token',"{{ csrf_token() }}");
                    resetValidationError();
                    $.ajax({
                        url: "{{ route('setup.country.update')}}",
                        type:"POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success:function(response){
                            resetAfterChange();
                            toastr.success("{{__('common.updated_successfully')}}", "{{__('common.success')}}");
                            $('#pre-loader').addClass('d-none');
                            $('#continent').niceSelect();
                            $('#formHtml').html(response.createForm);

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

                $(document).on('click', '.edit_country', function(event){
                    event.preventDefault();
                    $('#pre-loader').removeClass('d-none');
                    let id = $(this).data('id');
                    let base_url = $('#url').val();
                    let url = base_url + '/setup/location/country/edit/' +id;
                    $.get(url, function(response){
                        if(response){
                            $('#formHtml').html(response);
                            $('#continent').niceSelect();
                        }
                        $('#pre-loader').addClass('d-none');
                    });

                });

                $(document).on('change', '#flag', function(event){
                    event.preventDefault();
                    getFileName($(this).val(),'#flag_file');
                    imageChangeWithFile($(this)[0],'#FlagPreview');

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
                                'type': 'country',
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
                                
                                if(response.responseJSON && response.responseJSON.error){
                                    toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                                } else {
                                    toastr.error("{{__('common.error_message')}}");
                                }
                            }
                        });
                    } else {
                        // Si se está activando, proceder normalmente
                        executeStatusChange(id, status);
                    }
                });

                $(document).on('change', '.is_default_change', function(event){
                    event.preventDefault();

                    let checkbox = $(this);
                    let id = checkbox.data('id');

                    if(!checkbox.prop('checked')) {
                        checkbox.prop('checked', true);
                        return;
                    }

                    $('#pre-loader').removeClass('d-none');

                    $.ajax({
                        url: "{{ route('setup.country.toggle-default') }}",
                        type: "POST",
                        data: {
                            '_token': "{{ csrf_token() }}",
                            'id': id
                        },
                        success: function() {
                            $('#pre-loader').addClass('d-none');
                            resetAfterChange();
                        },
                        error: function(response) {
                            $('#pre-loader').addClass('d-none');
                            checkbox.prop('checked', false);

                            if(response.responseJSON && response.responseJSON.message){
                                toastr.error(response.responseJSON.message ,"{{__('common.error')}}");
                            } else if(response.responseJSON && response.responseJSON.error){
                                toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                            } else {
                                toastr.error("{{__('common.error_message')}}");
                            }
                        }
                    });
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
                        url: "{{ route('setup.country.status') }}",
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {
                            toastr.success("{{__('common.updated_successfully')}}","{{__('common.success')}}");
                            $('#pre-loader').addClass('d-none');
                            resetAfterChange();
                        },
                        error: function(xhr) {
                            $('#pre-loader').addClass('d-none');

                            const numericStatus = parseInt(status, 10);
                            const $checkbox = $('#checkbox' + id);
                            // Ante 422 (DomainException desde status()) u otro error: estado previo del toggle
                            // (intentaba pasar a inactivo => volver a activo; intentaba activar => volver a inactivo)
                            $checkbox.prop('checked', numericStatus === 0);

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                toastr.error(xhr.responseJSON.message, "{{__('common.error')}}");
                            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                                toastr.error(xhr.responseJSON.error, "{{__('common.error')}}");
                            } else {
                                toastr.error("{{__('common.error_message')}}");
                            }
                        }
                    });
                }

                function YajraReActive(){

                    $('#allData').DataTable({
                        processing: true,
                        serverSide: true,
                        "stateSave": true,
                        ajax: "{{ route('setup.country.getData') }}",
                        columns: [
                            { data: 'DT_RowIndex', name: 'id' },
                            { data: 'name', name: 'name' },
                            { data: 'code', name: 'code' },
                            { data: 'phonecode', name: 'phonecode' },
                            { data: 'flag', name: 'flag' },
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

                function resetAfterChange(){
                    $('#allData').DataTable().ajax.reload();
                }

                function create_form_reset(){
                    $('#create_form')[0].reset();
                    $('#countryFlagFileDiv').html(
                        `<div class="primary_input mb-25">
                            <label class="primary_input_label" for="">{{ __('setup.flag') }} (61 X 36)</label>
                            <div class="primary_file_uploader">
                                <input class="primary-input" type="text" id="flag_file"
                                    placeholder="{{__('common.browse_image')}}" readonly="">
                                <button class="" type="button">
                                    <label class="primary-btn small fix-gr-bg"
                                        for="flag">{{ __('common.browse') }} </label>
                                    <input type="file" class="d-none" name="flag" id="flag"
                                        onchange="getFileName(this.value,'#flag_file'),imageChangeWithFile(this,'#FlagPreview')">
                                </button>
                            </div>
                        </div>

                        <span class="text-danger" id="error_slider_image"></span>`
                    );
                    $('#createCountryFlagDiv').html(
                        `<img id="FlagPreview"
                            src="{{ showImage('flags/no_image.png') }}" alt="">`
                    );
                }

                function showValidationErrors(formType, errors){
                    $(formType +' #error_name').text(errors.name);
                    $(formType +' #error_continent').text(errors.continent);
                    $(formType +' #error_code').text(errors.code);
                    $(formType +' #error_phonecode').text(errors.phonecode);
                }

                function resetValidationError(){
                    $('#error_name').html('');
                    $('#error_continent').html('');
                    $('#error_code').html('');
                    $('#error_phonecode').html('');
                }

            });
        })(jQuery);
    </script>
@endpush
