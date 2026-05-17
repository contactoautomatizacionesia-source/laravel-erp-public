(function($) {
    "use strict";
    let _token = $('meta[name=_token]').attr('content');
    if (typeof _token == 'undefined'){
       _token = $('#csrf_token').val();
    }

    $(document).ready(function(){

        $(document).on('click','#balance_transfer_btn',function (event){
            $('#balance_transfer_modal').modal('show');
        });

        $(document).on('keyup','#transfer_amount',function (){
            let transferAmount = parseFloat($(this).val());
            let userBalance = parseFloat($('#user_balance').val());
            if(transferAmount > userBalance){
                $('#transfer_amount_msg').html('Saldo insuficiente');
                $('#transfer_submit_btn').prop('disabled', true);
                $(this).css({
                    'border': '1px solid red'
                });
                $(this).focus();
            }else {
                $('#transfer_amount_msg').html('');
                $('#transfer_submit_btn').prop('disabled', false);
                $(this).css({
                    'border': "1px solid #eceef4"
                });
                $(this).focusout();
            }
        });

        $(document).on('submit', '#create_balance_transfer', function(event){
            event.preventDefault();
            let formElement = $(this).serializeArray()
            let formData = new FormData();
            formElement.forEach(element => {
                formData.append(element.name,element.value);
            });
            formData.append('_token',_token);
            resetValidationError();
            $.ajax({
                url: $('#balance_transfer_url').val(),
                type:"POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success:function(response){
                    create_form_reset();
                    $('#balance_transfer_modal').modal('hide');
                    window.location.reload();
                    toastr.success('Saldo transferido a billetera exitosamente','Éxito');
                },
                error:function(response) {
                    showValidationErrors('#create_balance_transfer',response.responseJSON.errors);
                }
            });
        });
        $(document).on('click','.delete_row',function (event){
            event.preventDefault();
            let id = $(this).data('id');
            $('#delete_item_id').val(id);
            $('#deleteItemModal').modal('show');
        });
        $(document).on('submit', '#item_delete_form', function(event) {
            event.preventDefault();
            $('#deleteItemModal').modal('hide');
            var formData = new FormData();
            formData.append('_token', _token);
            formData.append('id', $('#delete_item_id').val());
            $.ajax({
                url:  $('#withdraw_request_delete_url').val(),
                type: "POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success: function(response) {
                    window.location.reload();
                    toastr.success("Eliminado exitosamente");
                },
                error: function(response) {
                    toastr.error("Algo salió mal");
                }
            });
        });

        $(document).on('click', '.edit_row', function(event){
            event.preventDefault();
            let id = $(this).data('id');
            let url =  $('#withdraw_request_edit_url').val();
            url = url.replace(':id',id);
            $.get(url, function(response){
                if(response){
                    $('#append_html').html(response);
                    $('.primary_select').niceSelect();
                    $('.theme_select').niceSelect();
                    $('#edit_withdraw_request_modal').modal('show');
                }
            });
        });
        $(document).on('submit', '#update_withdraw_request', function(event){
            event.preventDefault();
            let formElement = $(this).serializeArray()
            let formData = new FormData();
            formElement.forEach(element => {
                formData.append(element.name,element.value);
            });
            formData.append('_token',_token);
            let id = $('#rowId').val();
            let url = $('#withdraw_request_update_url').val();
            url = url.replace(':id',id);
            resetValidationError();
            $.ajax({
                url: url,
                type:"POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success:function(response){
                    $('#edit_withdraw_request_modal').modal('hide');
                    window.location.reload();
                    toastr.success('Actualizado exitosamente');
                },
                error:function(response) {
                    $('#edit_withdraw_request_modal').modal('show');
                    showValidationErrors('#update_withdraw_request',response.responseJSON.errors);
                }
            });
        });

        function create_form_reset(){
            $('#create_balance_transfer')[0].reset();
        }
        function showValidationErrors(formType, errors){
            $(formType +' #error_transfer_amount').text(errors.transfer_amount);
            $(formType +' #error_withdraw_amount').text(errors.withdraw_amount);
            $(formType +' #error_payment_type').text(errors.payment_type);
        }
        function resetValidationError(){
            $('#error_transfer_amount').html('');
            $('#error_withdraw_amount').html('');
            $('#error_payment_type').html('');
        }

    });
})(jQuery);
