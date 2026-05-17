@push('scripts')
    <script type="text/javascript">
        (function($){
            "use strict";
            var baseUrl = $('#app_base_url').val();
            var canViewProcessList = @json(permissionCheck('order_manage.process_list'));
            var deliveryProcessExportTitle = @json(app('general_setting')->site_title . ' | ' . __('order.delivery_process'));
            $(document).ready(function () {
                function resetEditProcessForm() {
                    $("#processEditForm").trigger("reset");
                    $("#processEditForm .edit_id").val(0);
                    $('.edit-empty-state').removeClass('d-none');
                    $('.edit-form-fields, .edit-form-actions').addClass('d-none');
                    @if(isModuleActive('FrontendMultiLang'))
                        $('#edit_name_error_{{auth()->user()->lang_code}}').text('');
                        $('#edit_description_error_{{auth()->user()->lang_code}}').text('');
                    @else
                        $('#edit_name_error').text('');
                        $('#edit_description_error').text('');
                    @endif
                }

                function applyDeliveryProcessTableLocalization() {
                    if (!canViewProcessList || !$('#refund_process_list .Crm_table_active3').length) {
                        return;
                    }

                    $('#header_title').text(deliveryProcessExportTitle);
                    document.title = deliveryProcessExportTitle;
                    CRMTableThreeReactive();
                }

                applyDeliveryProcessTableLocalization();
                $(document).on("submit", "#processEditForm", function (event) {
                    event.preventDefault();
                    $('#pre-loader').removeClass('d-none');
                    let id = $(".edit_id").val();
                    @if(isModuleActive('FrontendMultiLang'))
                        $('#edit_name_error_{{auth()->user()->lang_code}}').text('');
                        $('#edit_description_error_{{auth()->user()->lang_code}}').text('');
                    @else
                        $('#edit_name_error').text('');
                        $('#edit_description_error').text('');
                    @endif
                    var formElement = $(this).serializeArray()
                    var formData = new FormData();
                    formElement.forEach(element => {
                        formData.append(element.name, element.value);
                    });
                    formData.append('_token', "{{ csrf_token() }}");
                    $.ajax({
                        url: "{{route('admin.delivery-process.update')}}",
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {
                            resetEditProcessForm();
                            toastr.success("{{__('common.updated_successfully')}}","{{__('common.success')}}")
                            refund_process_list();
                            $('#pre-loader').addClass('d-none');
                        },
                        error: function(response) {
                            if(response.responseJSON.error){
                                toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                                $('#pre-loader').addClass('d-none');
                                return false;
                            }
                            if (response) {
                            @if(isModuleActive('FrontendMultiLang'))
                                $('#edit_name_error_{{auth()->user()->lang_code}}').text(response.responseJSON.errors['name.{{auth()->user()->lang_code}}']);
                                $('#edit_description_error_{{auth()->user()->lang_code}}').text(response.responseJSON.errors['description.{{auth()->user()->lang_code}}']);
                            @else
                                $('#edit_name_error').text(response.responseJSON.errors.name);
                                $('#edit_description_error').text(response.responseJSON.errors.description);
                            @endif
                            }
                            toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                            $('#pre-loader').addClass('d-none');
                        }
                    });
                });

                $("#refund_process_list").on("click", ".edit_reason", function () {
                    let name = $(this).data("name");
                    let description = $(this).data("description");
                    let id = $(this).data("id");

                    $('.edit-empty-state').addClass('d-none');
                    $('.edit-form-fields, .edit-form-actions').removeClass('d-none');
                    $("#processEditForm .name").val(name);
                    $("#processEditForm .description").val(description);
                    $("#processEditForm .edit_id").val(id);
                });

                $(document).on('click', '.cancel_edit_process', function(){
                    resetEditProcessForm();
                });

                function refund_process_list() {
                    if (!canViewProcessList) {
                        return;
                    }

                    $('#pre-loader').removeClass('d-none');
                    $.ajax({
                        url: "{{route("order_manage.process_list")}}",
                        type: "GET",
                        dataType: "HTML",
                        success: function (response) {
                            $("#refund_process_list").html(response);
                            applyDeliveryProcessTableLocalization();
                            $('#pre-loader').addClass('d-none');
                        },
                        error: function (error) {

                        }
                    });
                }
            });
        })(jQuery);
    </script>
@endpush
