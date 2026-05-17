
<div class="modal fade" id="confirm-status-modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">@lang('common.update_status') {{ isset($item_name)?$item_name:'' }} </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h4>@lang('common.are_you_sure_to_inactive_?')</h4>
                </div>
                <div class="form-group">
                    <label>{{__('hr.reason_for_inactivation')}}</label>
                    <textarea id="status-causal" class="form-control" required></textarea>
                </div>
                <div class="mt-10 d-flex justify-content-between">
                    <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                    <button type="button" id="confirm-status-btn" class="primary-btn fix-gr-bg">
                        {{__('common.confirm')}}
                    </button>
                    
                </div>
            </div>
        </div>
    </div>
</div>
