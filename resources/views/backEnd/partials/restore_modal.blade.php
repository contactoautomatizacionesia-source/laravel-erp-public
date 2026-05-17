<script type="text/javascript">
    function confirm_restore_modal(restore_url)
    {
        jQuery('#confirm-restore').modal('show', {backdrop: 'static'});
        document.getElementById('restore_link').setAttribute('href' , restore_url);
    }
</script>

<div class="modal fade" id="confirm-restore">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <span class="text-capitalize">@lang('common.restore')</span> {{ isset($item_name)?$item_name:'' }}
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h4 class="fs-18 text-black">@lang('common.are_you_sure_to_restore')</h4>
                </div>
                <div class="mt-10 d-flex justify-content-between">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">@lang('common.cancel')</button>
                    <a id="restore_link" class="btn-toolkit btn-primary">{{__('common.restore')}}</a>
                    
                </div>
            </div>
        </div>
    </div>
</div>
