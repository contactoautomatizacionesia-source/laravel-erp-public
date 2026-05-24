@push('scripts')
    <script>
        (function($) {
        	"use strict";
            $(document).ready(function(){

                let table = initGlobalDataTable('#allData', "{{ route('setup.state.getData') }}", [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'country', name: 'country.name' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action' }
                ], {
                    columnDefs: [
                        { targets: 4, responsivePriority: 1 },   // action
                        { targets: 1, responsivePriority: 2 },   // name
                        { targets: 3, responsivePriority: 3 },   // status
                        { targets: 0, responsivePriority: 4 },   // DT_RowIndex
                        { targets: 2, responsivePriority: 5 },   // country
                    ]
                });

                $('.nav-link[data-toggle="tab"]').on('click', function (e) {
                    e.preventDefault();
                    $('.nav-link').removeClass('active show');
                    $(this).addClass('active show');
                    let tableType = $(this).data('table');
                    let url = "{{ route('setup.state.getData') }}?table=" + tableType;
                    table.ajax.url(url).load();
                    if(tableType === 'all') {
                        $('#table_title').text("{{ __('common.state') }} {{ __('common.list') }}");
                    } else if(tableType === 'active') {
                        $('#table_title').text("{{ __('common.state') }} {{ __('common.active') }}");
                    } else if(tableType === 'inactive') {
                        $('#table_title').text("{{ __('common.state') }} {{ __('common.inactive') }}");
                    } else if(tableType === 'default') {
                        $('#table_title').text("{{ __('common.state') }} {{ __('setup.default') }}");
                    }
                });

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

                $(document).on('click', '.delete_state', function(event){
                    event.preventDefault();
                    let id = $(this).data('id');
                    let url = "{{ route('setup.state.destroy') }}";
                    window._deletePayload = { id: id, url: url };
                    confirm_modal(url);
                });

                $(document).on('click', '#delete_link', function(event) {
                    event.preventDefault();
                    let payload = window._deletePayload;
                    if (!payload) return;

                    $('#confirm-delete').modal('hide');
                    $('#pre-loader').removeClass('d-none');

                    let formData = new FormData();
                    formData.append('_token', "{{ csrf_token() }}");
                    formData.append('id', payload.id);

                    $.ajax({
                        url: payload.url,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            $('#pre-loader').addClass('d-none');
                            toastr.success(response.message, "{{__('common.success')}}");
                            resetAfterChange();
                        },
                        error: function(response) {
                            $('#pre-loader').addClass('d-none');
                            if(response.responseJSON && response.responseJSON.message){
                                toastr.error(response.responseJSON.message, "{{__('common.error')}}");
                            } else {
                                toastr.error("{{__('common.error_message')}}", "{{__('common.error')}}");
                            }
                        }
                    });
                });

                function resetAfterChange(TableData){
                    $('#item_table').html(TableData);
                    table = initGlobalDataTable('#allData', "{{ route('setup.state.getData') }}", [
                        { data: 'DT_RowIndex', name: 'id' },
                        { data: 'name', name: 'name' },
                        { data: 'country', name: 'country.name' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action' }
                    ], {
                        columnDefs: [
                            { targets: 4, responsivePriority: 1 },
                            { targets: 1, responsivePriority: 2 },
                            { targets: 3, responsivePriority: 3 },
                            { targets: 0, responsivePriority: 4 },
                            { targets: 2, responsivePriority: 5 },
                        ]
                    });
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
