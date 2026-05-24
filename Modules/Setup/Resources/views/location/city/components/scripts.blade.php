@push('scripts')
    <script>
        (function($) {
        	"use strict";
                $(document).ready(function(){
                    let table = initGlobalDataTable('#allData', "{{ route('setup.city.getData') }}", [
                        { data: 'DT_RowIndex', name: 'id' },
                        { data: 'name', name: 'name' },
                        { data: 'country', name: 'state.country.name' },
                        { data: 'state', name: 'state.name' },
                        { data: 'status', name: 'status' },
                        { data: 'action', name: 'action' }
                    ], {
                        columnDefs: [
                            { targets: 5, responsivePriority: 1 },   // action
                            { targets: 1, responsivePriority: 2 },   // name
                            { targets: 4, responsivePriority: 3 },   // status
                            { targets: 0, responsivePriority: 4 },   // DT_RowIndex
                            { targets: 2, responsivePriority: 5 },   // country
                            { targets: 3, responsivePriority: 6 },   // state
                        ]
                    });

                    $('.nav-link[data-toggle="tab"]').on('click', function (e) {
                        e.preventDefault();
                        $('.nav-link').removeClass('active show');
                        $(this).addClass('active show');
                        let tableType = $(this).data('table');
                        let url = "{{ route('setup.city.getData') }}?table=" + tableType;
                        table.ajax.url(url).load();
                        if(tableType === 'all') {
                            $('#table_title').text("{{ __('common.city') }} {{ __('common.list') }}");
                        } else if(tableType === 'active') {
                            $('#table_title').text("{{ __('common.city') }} {{ __('common.active') }}");
                        } else if(tableType === 'inactive') {
                            $('#table_title').text("{{ __('common.city') }} {{ __('common.inactive') }}");
                        } else if(tableType === 'default') {
                            $('#table_title').text("{{ __('common.city') }} {{ __('setup.default') }}");
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
                            url: "{{ route('setup.city.store')}}",
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
                            url: "{{ route('setup.city.update')}}",
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
                                $('#state').niceSelect();

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

                    $(document).on('change', '#country', function(event){
                        let country = $('#country').val();
                        $('#pre-loader').removeClass('d-none');
                        if(country){
                            let data = {
                                '_token' : '{{ csrf_token() }}',
                                'country_id' : country
                            }
                            $.post("{{route('setup.city.get-state')}}",data, function(response){

                                if(response){
                                    $('#stateDiv').html(response);
                                    $('#state').niceSelect();
                                }
                                $('#pre-loader').addClass('d-none');
                            });
                        }
                    });

                    $(document).on('click', '.edit_city', function(event){
                        event.preventDefault();
                        $('#pre-loader').removeClass('d-none');
                        let id = $(this).data('id');
                        let base_url = $('#url').val();
                        let url = base_url + '/setup/location/city/edit/' +id;
                        $.get(url, function(response){
                            if(response){
                                $('#formHtml').html(response);
                                $('#country').niceSelect();
                                $('#state').niceSelect();
                            }
                            $('#pre-loader').addClass('d-none');
                        });

                    });

                    $(document).on('change', '.status_change', function(event){
                        event.preventDefault();
                        let status = 0;
                        if($(this).prop('checked')){
                            status = 1;
                        }
                        else{
                            status = 0;
                        }
                        let id = $(this).data('id');
                        let checkbox = $(this);
                        $('#pre-loader').removeClass('d-none');
                        let formData = new FormData();
                        formData.append('_token', "{{ csrf_token() }}");
                        formData.append('id', id);
                        formData.append('status', status);

                        $.ajax({
                            url: "{{ route('setup.city.status') }}",
                            type: "POST",
                            cache: false,
                            contentType: false,
                            processData: false,
                            data: formData,
                            success: function(response) {
                                toastr.success("{{ __('common.updated_successfully') }}","{{__('common.success')}}");
                                $('#pre-loader').addClass('d-none');
                            },
                            error: function(xhr) {
                                $('#pre-loader').addClass('d-none');

                                var numericStatus = parseInt(status, 10);
                                var $checkbox = checkbox;
                                // Revertir el estado previo del toggle
                                $checkbox.prop('checked', numericStatus === 0);

                                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                                    ? xhr.responseJSON.message
                                    : 'Ocurrió un error';
                                toastr.error(msg, "{{__('common.error')}}");
                            }
                        });

                    });

                    $(document).on('click', '.delete_city', function(event){
                        event.preventDefault();
                        let id = $(this).data('id');
                        let url = "{{ route('setup.city.destroy') }}";
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
                        table = initGlobalDataTable('#allData', "{{ route('setup.city.getData') }}", [
                            { data: 'DT_RowIndex', name: 'id' },
                            { data: 'name', name: 'name' },
                            { data: 'country', name: 'state.country.name' },
                            { data: 'state', name: 'state.name' },
                            { data: 'status', name: 'status' },
                            { data: 'action', name: 'action' }
                    ], {
                            columnDefs: [
                                { targets: 5, responsivePriority: 1 },
                                { targets: 1, responsivePriority: 2 },
                                { targets: 4, responsivePriority: 3 },
                                { targets: 0, responsivePriority: 4 },
                                { targets: 2, responsivePriority: 5 },
                                { targets: 3, responsivePriority: 6 },
                            ]
                        });
                    }



                    function create_form_reset(){
                        $('#create_form')[0].reset();

                    }

                    function showValidationErrors(formType, errors){
                        $(formType +' #error_name').text(errors.name);
                        $(formType +' #error_country').text(errors.country);
                        $(formType +' #error_state').text(errors.state);
                    }

                    function resetValidationError(){
                        $('#error_name').html('');
                        $('#error_country').html('');
                        $('#error_state').html('');
                    }

                });
        })(jQuery);
    </script>
@endpush
