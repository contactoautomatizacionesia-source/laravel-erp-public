<dialog wire:ignore.self class="modal fade" id="editDianModal" tabindex="-1"  aria-hidden="true">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('general_settings.settings')}} {{__('general_settings.dian')}} - {{ $brand_name }}</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>

            <form wire:submit.prevent="save">
                <div class="modal-body">
                    <div class="form-card">
                        <h3>{{__('common.conexion_data')}}</h3>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="api_url">{{ __('common.url') }} {{ __('common.api') }} <span class="text-danger">*</span></label>
                                    <input wire:model.defer="api_url" class="primary_input_field" id="api_url" type="text" required>
                                    @error('api_url') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="api_user">
                                        {{ __('common.user') }}<span class="text-danger">*</span>
                                    </label>
                                    <input
                                        wire:model.defer="api_user"
                                        class="primary_input_field"
                                        id="api_user"
                                        type="text"
                                        required
                                        maxlength="30"
                                    >
                                    @error('api_user') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="api_password">
                                        {{ __('common.password') }}
                                    </label>
                                    <input
                                        wire:model.defer="api_password"
                                        class="primary_input_field"
                                        id="api_password"
                                        type="password"
                                        placeholder="******"
                                        maxlength="20"
                                    >
                                     @error('api_password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn-toolkit btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('common.update') }}</span>
                        <span wire:loading>{{ __('common.saving') }}...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</dialog>
