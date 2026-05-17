(function($) {
    "use strict";
    $(document).ready(function(){

        //referral duration
        let referralDurationDiv = $('.referral_duration_div');

        let referralDurationType =   $('.referral_duration_type:checked').val();

        if (referralDurationType === 'Fixed') {
            referralDurationDiv.show();
        } else {
            referralDurationDiv.hide();
        }
        $(document).on('change','.referral_duration_type', function(event){
            let referral_duration_type = $(this).val();
            if (referral_duration_type === 'Fixed') {
                referralDurationDiv.show();
            } else {
                referralDurationDiv.hide();
            }
        });


        //commission type
        let productWiseCommissionDiv = $('#product_wise_commission_div');
        let categoryWiseCommissionDiv = $('#category_wise_commission_div');

        let commissionType =   $('.commission_type:checked').val();

        if (commissionType === 'Product') {
            productWiseCommissionDiv.show();
            categoryWiseCommissionDiv.hide();
        } else {
            productWiseCommissionDiv.hide();
            categoryWiseCommissionDiv.show();
        }
        $(document).on('change','.commission_type', function(event){
            let commission_type = $(this).val();
            if (commission_type === 'Product') {
                productWiseCommissionDiv.show();
                categoryWiseCommissionDiv.hide();
            } else {
                productWiseCommissionDiv.hide();
                categoryWiseCommissionDiv.show();
            }
        });

        $(document).on('click','.copy_cron',function(event){
            event.preventDefault();
            var r = document.createRange();
            r.selectNode(document.getElementById('cron_job'));
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(r);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            toastr.success(trans('common.link_copied_successfully'), trans('common.success'));
        });


    });
})(jQuery);
