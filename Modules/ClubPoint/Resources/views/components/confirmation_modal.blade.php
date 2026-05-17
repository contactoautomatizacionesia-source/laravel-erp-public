<div class="modal fade" id="double_approval_modal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--toolkit_corporative-orange-color);">
                <h4 class="modal-title">{{ __('clubpoint.approval_required') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close text-danger"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-2">
                        <i class="ti-lock" style="font-size: 30px; color: var(--toolkit_corporative-orange-color);"></i>
                    </div>
                    <h4 style="color: var(--toolkit_corporative-orange-color);">{{ __('clubpoint.double_approval_process') }}</h4>
                    
                    <p class="text-muted mt-2">{{ __('clubpoint.confirm_message') }}</p>
                </div>
                <div class="mt-10 d-flex justify-content-between">
                    <button type="button" class="btn text-white" style="background-color: var(--toolkit_secondary-gray-color); border-color: var(--toolkit_secondary-gray-color);" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <a id="confirm_approval_btn" class="btn text-white cursor-pointer" style="background-color: var(--toolkit_corporative-orange-color); border-color: var(--toolkit_corporative-orange-color);">{{ __('common.confirm') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>