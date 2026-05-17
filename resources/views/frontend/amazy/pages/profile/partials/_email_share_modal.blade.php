<div class="modal fade" id="emailShareModal" tabindex="-1" aria-labelledby="emailShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font_18 f_w_600" id="emailShareModalLabel">{{__('defaultTheme.share_via_email')}}</h5>
                <button type="button" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <p class="font_14 text-muted mb-3">{{__('defaultTheme.enter_friend_email')}}</p>
                <div class="form-group">
                    <input type="email" id="targetFriendEmail" class="form-control" placeholder="ejemplo@correo.com" required>
                    <span class="text-danger font_12 d-none mt-1" id="emailShareError">{{__('defaultTheme.invalid_email')}}</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{__('common.cancel')}}</button>
                <button type="button" class="amaz_primary_btn btn-sm" id="sendEmailShareBtn">{{__('common.send')}}</button>
            </div>
        </div>
    </div>
</div>