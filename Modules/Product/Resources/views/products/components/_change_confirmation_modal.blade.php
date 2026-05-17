<dialog class="modal fade" id="changeConfirmationModal" tabindex="-1" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-white">{{ __('common.important_notice') }}</h4>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>
            <div class="modal-body">
                {{-- Sección 1: Advertencia --}}
                <p class="font-weight-bold mb-3">
                    {{ __('common.important_change_notice_message') }}
                </p>

                {{-- Sección 2: Observación --}}
                <x-admin.textarea-counter
                    name="observation"
                    :label="__('common.observation').' *'"
                    id="change_observation_input"
                    :placeholder="__('common.change_reason_placeholder')"
                    :min="200"
                    :max="1000"
                    :rows="4" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" id="confirm_update_btn" class="btn-toolkit btn-primary" disabled>{{ __('common.confirm') }}</button>
            </div>
        </div>
    </div>
</dialog>

<script>
    (function($){
        "use strict";
        $(document).ready(function(){
            $(document).on('input', '#change_observation_input', function(){
                let min = $(this).data('min');
                let max = $(this).data('max');
                let value = $(this).val();
                let parent = $(this).closest('.mb-3');
                let counter = parent.find('.char-count');
                let confirmBtn = $('#confirm_update_btn');

                counter.text(value.length + ' / ' + max);

                if(value.length >= min){
                    confirmBtn.prop('disabled', false);
                    counter.removeClass('text-danger').addClass('text-muted');
                }else{
                    confirmBtn.prop('disabled', true);
                    counter.removeClass('text-muted').addClass('text-danger');
                }
            });
        });
    })(jQuery);
</script>
