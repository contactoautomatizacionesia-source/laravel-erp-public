<dialog id="confirmExitModal" class="modal fade" tabindex="-1" aria-labelledby="confirmExitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="confirmExitModalLabel">
                    <i class="fa fa-exclamation-triangle text-warning"></i>
                    {{ __('inventoryexit::messages.confirm_title') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body">
                <p>
                    {!! __('inventoryexit::messages.confirm_body', ['cost_center' => '<strong id="confirmExitCcName">—</strong>']) !!}
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                    {{ __('inventoryexit::messages.btn_cancel') }}
                </button>
                <button type="button" class="btn-toolkit btn-primary" id="btnConfirmExitFinal">
                    <i class="fa fa-check"></i> {{ __('inventoryexit::messages.btn_accept') }}
                </button>
            </div>

        </div>
    </div>
</dialog>
