<div wire:ignore.self class="modal fade" id="confirmInactiveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('common.confirm_inactivation') }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <h4>{{__('general_settings.inactivation_alert')}}</h4>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="primary-btn tr-bg" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="primary-btn fix-gr-bg"
                    onclick="$('#pre-loader').removeClass('d-none');"
                    wire:click="toggleStatus">{{ __('common.confirm') }}</button>
            </div>
        </div>
    </div>
</div>