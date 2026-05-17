<dialog wire:ignore.self class="modal fade" id="editDianGeneralSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('common.general_settings') }} {{__('general_settings.dian')}} - {{ $brand_name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="modal-body">
                    <div class="form-card">
                        <h3>{{__('common.information')}}</h3>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="resolution_number">
                                        {{__('general_settings.resolution_number')}}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.defer="resolution_number" class="primary_input_field" type="text" required id="resolution_number" placeholder="02565865">
                                    @error('resolution_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-xl-12">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="resolution_date">
                                        {{__('general_settings.resolution_date')}}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="primary_datepicker_input">
                                        <div class="no-gutters input-right-icon">
                                            <div class="col">
                                                <div class="">
                                                    <input
                                                        wire:model.defer="resolution_date"
                                                        placeholder="{{__('common.date')}}"
                                                        class="primary_input_field primary-input date form-control"
                                                        id="resolution_date"
                                                        name="resolution_date"
                                                        autocomplete="off"
                                                        required
                                                    >
                                                </div>
                                            </div>
                                            <button class="" type="button"> <i class="ti-calendar" id="resolution-date-date-icon"></i></button>
                                        </div>
                                    </div>
                                    @error('resolution_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="invoice_number_from">
                                        {{__('general_settings.invoice_number_from')}}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.defer="invoice_number_from" class="primary_input_field" type="number" required placeholder="2000001">
                                    @error('invoice_number_from')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="invoice_number_to">
                                        {{__('general_settings.invoice_number_to')}}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input wire:model.defer="invoice_number_to" class="primary_input_field" type="number" required placeholder="2999999">
                                    @error('invoice_number_to')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="btn-toolkit btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('common.update') }}</span>
                        <span wire:loading wire:target="save">{{ __('common.saving') }}...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</dialog>

<script>
    $(document).ready(function () {
        $('#resolution_date').on('change', function () {
            const input = document.getElementById('resolution_date');
            const nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
            nativeInputValueSetter.call(input, $(this).val());
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });
</script>
