@push('scripts')
<script>
    (function($) {
        "use strict";
        $(document).ready(function(){

            let table = initGlobalDataTable('#allData', "{{ route('setup.country.getData') }}", [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'code', name: 'code' },
                { data: 'phonecode', name: 'phonecode' },
                { data: 'flag', name: 'flag', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ], {
                columnDefs: [
                    { targets: 6, responsivePriority: 1 },   // action
                    { targets: 1, responsivePriority: 2 },   // name
                    { targets: 5, responsivePriority: 3 },   // status
                    { targets: 0, responsivePriority: 4 },   // DT_RowIndex
                    { targets: 4, responsivePriority: 5 },   // flag
                    { targets: 2, responsivePriority: 6 },   // code
                    { targets: 3, responsivePriority: 7 },   // phonecode
                ]
            });

            $('.nav-link[data-toggle="tab"]').on('click', function (e) {
                e.preventDefault();
                $('.nav-link').removeClass('active show');
                $(this).addClass('active show');
                let tableType = $(this).data('table');
                let url = "{{ route('setup.country.getData') }}?table=" + tableType;
                table.ajax.url(url).load();
                if(tableType === 'all') {
                    $('#table_title').text("{{ __('common.country') }} {{ __('common.list') }}");
                } else if(tableType === 'active') {
                    $('#table_title').text("{{ __('common.country') }} {{ __('common.active') }}");
                } else if(tableType === 'inactive') {
                    $('#table_title').text("{{ __('common.country') }} {{ __('common.inactive') }}");
                } else if(tableType === 'default') {
                    $('#table_title').text("{{ __('common.country') }} {{ __('setup.default') }}");
                }
            });

            // Flag preview on file select
            $(document).on('change', '#flag', function() {
                let file = this.files[0];
                if (!file) return;

                let validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                let errorEl = $('#error_flag');
                errorEl.text('');

                if (!validTypes.includes(file.type)) {
                    errorEl.text('{{ __("Solo se permiten archivos JPEG, PNG, JPG o GIF.") }}');
                    $(this).val('');
                    $('#flag_file').val('');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    errorEl.text('{{ __("La imagen no debe superar los 2MB.") }}');
                    $(this).val('');
                    $('#flag_file').val('');
                    return;
                }

                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#FlagPreview').attr('src', e.target.result);
                    $('.clear_flag_btn').removeClass('d-none');
                };
                reader.readAsDataURL(file);

                $('#flag_file').val(file.name);
            });

            // Clear flag
            $(document).on('click', '.clear_flag_btn', function() {
                $('#FlagPreview').attr('src', "{{ showImage('flags/no_image.png') }}");
                $('#flag').val('');
                $('#flag_file').val('');
                $(this).addClass('d-none');
                $('#error_flag').text('');

                if ($('#edit_form').length) {
                    if ($('input[name="remove_flag"]').length) {
                        $('input[name="remove_flag"]').val('1');
                    } else {
                        $('#edit_form').append('<input type="hidden" name="remove_flag" value="1">');
                    }
                }
            });

            $(document).on('submit', '#create_form', function(event){
                event.preventDefault();
                $('#pre-loader').removeClass('d-none');
                let formElement = $(this).serializeArray();
                let formData = new FormData();
                formElement.forEach(element => {
                    formData.append(element.name, element.value);
                });
                let flag = $('#flag')[0].files[0];
                if(flag){
                    formData.append('flag', flag);
                }
                formData.append('_token', "{{ csrf_token() }}");

                resetValidationError();
                $.ajax({
                    url: "{{ route('setup.country.store') }}",
                    type: "POST",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(response){
                        resetAfterChange();
                        create_form_reset();
                        toastr.success("{{__('common.added_successfully')}}", "{{__('common.success')}}");
                        $('#pre-loader').addClass('d-none');
                        $('#continent').niceSelect('update');
                    },
                    error: function(response) {
                        $('#pre-loader').addClass('d-none');
                        if(response.responseJSON && response.responseJSON.errors){
                            showValidationErrors('#create_form', response.responseJSON.errors);
                        } else if(response.responseJSON && response.responseJSON.error){
                            toastr.error(response.responseJSON.error, "{{__('common.error')}}");
                        } else {
                            toastr.error("{{__('common.error_message')}}", "{{__('common.error')}}");
                        }
                    }
                });
            });

            $(document).on('submit', '#edit_form', function(event){
                event.preventDefault();
                $('#pre-loader').removeClass('d-none');
                let formElement = $(this).serializeArray();
                let formData = new FormData();
                formElement.forEach(element => {
                    formData.append(element.name, element.value);
                });
                let flag = $('#flag')[0].files[0];
                if(flag){
                    formData.append('flag', flag);
                }
                if ($('input[name="remove_flag"]').val() == "1") {
                    formData.append('remove_flag', 1);
                }
                formData.append('_token', "{{ csrf_token() }}");

                resetValidationError();
                $.ajax({
                    url: "{{ route('setup.country.update') }}",
                    type: "POST",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                    success: function(response){
                        resetAfterChange();
                        toastr.success("{{__('common.updated_successfully')}}", "{{__('common.success')}}");
                        $('#pre-loader').addClass('d-none');
                        $('#continent').niceSelect('update');
                        $('#formHtml').html(response.createForm);
                        $('#continent').niceSelect();
                    },
                    error: function(xhr) {
                        $('#pre-loader').addClass('d-none');
                        var resp = xhr.responseJSON;
                        if (resp && resp.status === 'error' && resp.message) {
                            toastr.error(resp.message, "{{__('common.error')}}");
                        } else if (resp && resp.errors) {
                            showValidationErrors('#edit_form', resp.errors);
                        } else {
                            toastr.error("{{__('common.error_message')}}", "{{__('common.error')}}");
                        }
                    }
                });
            });

            $(document).on('click', '.edit_country', function(event){
                event.preventDefault();
                $('#pre-loader').removeClass('d-none');
                let id = $(this).data('id');
                let base_url = $('#url').val() || window.location.origin;
                let url = base_url + '/setup/location/country/edit/' + id;
                $.get(url, function(response){
                    if(response){
                        $('#formHtml').html(response);
                        $('#continent').niceSelect();
                    }
                    $('#pre-loader').addClass('d-none');
                });
            });

            $(document).on('click', '.delete_country', function(event){
                event.preventDefault();
                let id = $(this).data('id');
                let url = "{{ route('setup.country.destroy') }}";
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
                if(TableData){
                    $('#item_table').html(TableData);
                }
                table = initGlobalDataTable('#allData', "{{ route('setup.country.getData') }}", [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'phonecode', name: 'phonecode' },
                    { data: 'flag', name: 'flag', orderable: false, searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ], {
                    columnDefs: [
                        { targets: 6, responsivePriority: 1 },
                        { targets: 1, responsivePriority: 2 },
                        { targets: 5, responsivePriority: 3 },
                        { targets: 0, responsivePriority: 4 },
                        { targets: 4, responsivePriority: 5 },
                        { targets: 2, responsivePriority: 6 },
                        { targets: 3, responsivePriority: 7 },
                    ]
                });
            }

            function create_form_reset(){
                $('#create_form')[0].reset();
            }

            function showValidationErrors(formType, errors){
                if(errors.name) $(formType +' #error_name').text(errors.name[0] || errors.name);
                if(errors.code) $(formType +' #error_code').text(errors.code[0] || errors.code);
                if(errors.phonecode) $(formType +' #error_phonecode').text(errors.phonecode[0] || errors.phonecode);
                if(errors.flag) $(formType +' #error_flag').text(errors.flag[0] || errors.flag);
                if(errors.status) $(formType +' #error_status').text(errors.status[0] || errors.status);
            }

            function resetValidationError(){
                $('#error_name').html('');
                $('#error_code').html('');
                $('#error_phonecode').html('');
                $('#error_flag').html('');
            }

        });
    })(jQuery);
</script>
@endpush
