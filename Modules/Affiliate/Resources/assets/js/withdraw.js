(function($) {
    "use strict";
    $(document).ready(function(){
        $(document).on('click', '.withdraw_confirm', function(event){
            event.preventDefault();
            let id = $(this).data('id');
            let url =  $('#confirm_withdraw_url').val();
            url = url.replace(':id',id);
            $.get(url, function(response){
                if(response){
                    toastr.success("Pago confirmado exitosamente");
                    resetAfterChange();
                }
            });
        });

        function resetAfterChange(){
            let table = $('#lms_table').DataTable() ;
            table.clearPipeline();
            table.ajax.reload();
        }
    });
})(jQuery);
