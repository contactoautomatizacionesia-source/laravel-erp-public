(function($) {
    "use strict";
    let _token = $('meta[name=_token]').attr('content') ;
    if (typeof _token == 'undefined'){
        _token = $('#csrf_token').val();
    }
    $(document).ready(function(){
        $(document).on('change','#url',function (event){
          let url = $(this).val();
          let userName = $('#user_name').val();
          let affiliateUrl = url +'?reference='+userName;
          $('#affiliate_link').val(affiliateUrl);
        });

        $(document).on('click','#withdraw_request_btn',function (event){
            let minimumWithdrawAmount = parseFloat($('#minimum_withdraw_amount').val());
            let userBalance = parseFloat($('#user_balance').val());
            if(userBalance < minimumWithdrawAmount ){
                toastr.info('El pago mínimo es '+minimumWithdrawAmount + '. Tu saldo actual es ' +userBalance)
            }else{
                $('#withdraw_request_modal').modal('show');
            }
        });

        $(document).on('keyup','#withdraw_amount',function (){
            let withdrawAmount = parseFloat($(this).val());
            let userBalance = parseFloat($('#user_balance').val());
            let minimumWithdrawAmount = parseFloat($('#minimum_withdraw_amount').val());
            if(withdrawAmount > userBalance){
                $('#withdraw_amount_msg').html('Saldo insuficiente');
                $('#withdraw_submit_btn').prop('disabled', true);
                $(this).css({
                    'border': '1px solid red'
                });
                $(this).focus();
            }else if(withdrawAmount < minimumWithdrawAmount){
                $('#withdraw_amount_msg').html('No puedes retirar menos del pago mínimo ' +minimumWithdrawAmount);
                $('#withdraw_submit_btn').prop('disabled', true);
                $(this).css({
                    'border': '1px solid red'
                });
                $(this).focus();
            }else {
                $('#withdraw_amount_msg').html('');
                $('#withdraw_submit_btn').prop('disabled', false);
                $(this).css({
                    'border': "1px solid #eceef4"
                });
                $(this).focusout();
            }
        });

        $(document).on('submit', '#create_withdraw_request', function(event){
            event.preventDefault();
            let formElement = $(this).serializeArray()
            let formData = new FormData();
            formElement.forEach(element => {
                formData.append(element.name,element.value);
            });
            formData.append('_token',_token);
            resetValidationError();
            $.ajax({
                url: $('#withdraw_request_store_url').val(),
                type:"POST",
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                success:function(response){
                    create_form_reset();
                    $('#withdraw_request_modal').modal('hide');
                    window.location.reload();
                    toastr.success('Solicitud de retiro enviada exitosamente','Éxito');
                },
                error:function(response) {
                    showValidationErrors('#create_withdraw_request',response.responseJSON.errors);
                }
            });
        });


        //tab active
        $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeAffiliateTab', $(e.target).attr('href'));
        });
        var activeTab = localStorage.getItem('activeAffiliateTab');

        if (activeTab) {
            $('a[href="' + activeTab + '"]').tab('show');
        }
        //end tab active


        function create_form_reset(){
            $('#create_withdraw_request')[0].reset();
            $(".primary_select").niceSelect('update');
        }
        function showValidationErrors(formType, errors){
            $(formType +' #error_withdraw_amount').text(errors.withdraw_amount);
            $(formType +' #error_payment_type').text(errors.payment_type);
        }
        function resetValidationError(){
            $('#error_withdraw_amount').html('');
            $('#error_payment_type').html('');
        }

    });
})(jQuery);
