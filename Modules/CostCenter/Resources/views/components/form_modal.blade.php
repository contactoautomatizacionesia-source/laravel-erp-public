@section('styles')
{{-- No necesitamos añadir CSS extra, usamos el de ign_custom.css --}}
@endsection

<div class="modal fade" id="costCenterFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="
            max-height: 90vh;
        ">
            <div class="modal-header">
                <h5 class="modal-title" id="costCenterFormModalLabel">{{ __('cost_center.create_cost_center') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                   <i class="ti-close "></i>
                </button>
            </div>
            <form id="costCenterForm" style="
                overflow-y: auto;
            ">
                @csrf
                <input type="hidden" name="cost_center_id" id="cost_center_id">
                
                <div class="modal-body py-2 px-md-4 px-2" style="
                    overflow-y: scroll;
                ">
                    
                    {{-- ==========================================
                        TARJETA 1: INFORMACIÓN GENERAL
                    =========================================== --}}
                    <div class="form-card">
                        <h3><i class="ti-info-alt mr-2"></i>{{ __('common.general_info') }}</h3>
                        <div class="row">
                            {{-- Centro de Costo --}}
                            <div class="col-md-12 form-group">
                                <label class="primary_input_label" for="name">{{ __('cost_center.name') }} <span>*</span></label>
                                <input type="text" class="primary_input_field" name="name" id="cc_name" required placeholder="{{ __('cost_center.enter_name') }}">
                            </div>

                            {{-- Marca --}}
                            <div class="col-lg-6 col-md-12 form-group">
                                <label class="primary_input_label" for="brand_id">{{ __('cost_center.brand') }}</label>
                                <select
                                    class="nice-select-ajax primary_input_select"
                                    name="brand_id"
                                    id="brand_id"
                                    data-url="{{ route('cost_centers.get-brands') }}"
                                    data-initial="true"
                                    data-sync-text="true"
                                    data-text-target="brand_name"
                                >
                                    <option value="">{{ __('cost_center.select_brand') }}</option>
                                </select>
                                <input type="hidden" name="brand_name" id="brand_name" value="">
                            </div>

                            {{-- Estado (Switch) --}}
                            <div class="col-md-6 form-group mt-2">
                                <label class="primary_checkbox d-flex mr-12 mb-3" for="status">{{ __('cost_center.status') }}</label>
                                <label class="switch_toggle">
                                    <span class="hidden">{{ __('cost_center.status') }}</span>
                                    <input type="hidden" name="status" value="0">
                                    <input type="checkbox" name="status" id="status" value="1" checked>
                                    <div class="slider round"></div>
                                </label>
                            </div>

                            <div class="col-md-6 form-group mt-2">
                                <label class="primary_checkbox d-flex mr-12 mb-3" for="is_default">{{ __('cost_center.is_default') }}</label>
                                <label class="switch_toggle">
                                    <span class="hidden">{{ __('cost_center.is_default') }}</span>
                                    <input type="hidden" name="is_default" value="0">
                                    <input type="checkbox" name="is_default" id="is_default" value="1">
                                    <div class="slider round"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ==========================================
                        TARJETA 2: UBICACIÓN Y CONTACTO
                    =========================================== --}}
                    <div class="form-card">
                        <h3><i class="ti-location-pin mr-2"></i>{{ __('common.location_contact') }}</h3>
                        <div class="row">
                            {{-- Ciudad --}}
                            <div class="col-lg-6 col-md-12 form-group">
                                <label class="primary_input_label" for="city_id">{{ __('cost_center.city') }} <span>*</span></label>
                                <select
                                    class="nice-select-ajax primary_input_select"
                                    name="city_id"
                                    id="city_id"
                                    data-url="{{ url('/location/city/search-for-select') }}"
                                    data-initial="true"
                                    data-sync-text="true"
                                    data-text-target="city_name"
                                >
                                    <option value="">{{ __('cost_center.select_city') }}</option>
                                </select>
                                <input type="hidden" name="city_name" id="city_name" value="">
                            </div>

                            {{-- Teléfono --}}
                            <div class="col-md-6 form-group">
                                <label class="primary_input_label" for="phone">{{ __('cost_center.phone') }} <span>*</span></label>
                                <input type="text" class="primary_input_field" name="phone" id="phone" placeholder="{{ __('cost_center.enter_phone') }}">
                            </div>

                            {{-- Dirección --}}
                            <div class="col-md-8 form-group">
                                <label class="primary_input_label" for="address">{{ __('cost_center.address') }} <span>*</span></label>
                                <input type="text" class="primary_input_field" name="address" id="address" placeholder="{{ __('cost_center.enter_address') }}">
                            </div>

                            {{-- Código Postal --}}
                            <div class="col-md-4 form-group">
                                <label class="primary_input_label" for="pin_code">{{ __('cost_center.pin_code') }} <span>*</span></label>
                                <input type="text" class="primary_input_field" name="pin_code" id="pin_code" placeholder="{{ __('cost_center.enter_pin_code') }}">
                            </div>
                        </div>
                    </div>

                    {{-- ==========================================
                        TARJETA 3: CONFIGURACIÓN ADICIONAL
                    =========================================== --}}
                    <div class="form-card mb-0">
                        <h3><i class="ti-settings mr-2"></i>{{ __('common.additional_config') }}</h3>
                        <div class="row">
                            {{-- Forma de Pago --}}
                            <div class="col-lg-6 col-md-12 form-group">
                                <label class="primary_input_label" for="payment_form_id">{{ __('cost_center.payment_form') }}</label>
                                <select class="nice-select-regular primary_input_select" name="payment_form_id" id="payment_form_id">
                                    <option value="">{{ __('cost_center.select_payment_form') }}</option>
                                </select>
                            </div>

                            {{-- Comentario --}}
                            <div class="col-md-12 form-group">
                                <label class="primary_input_label" for="comment">{{ __('cost_center.comment') }}</label>
                                <textarea class="primary_textarea" name="comment" id="comment" rows="2" placeholder="{{ __('cost_center.enter_comment') }}"></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('cost_center.cancel') }}</button>
                    <button type="submit" class="btn-toolkit btn-primary" id="btn_save_cost_center">{{ __('cost_center.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Validación de nombre: letras, números, espacios y caracteres españoles
        $('#cc_name').on('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9\sñÑáéíóúÁÉÍÓÚ]/g, '');
        });

        // Validación de teléfono: solo números y máximo 10 dígitos
        $('#phone').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });

        // Opcional: Evitar pegar caracteres no numéricos
        $('#phone').on('paste', function(e) {
            let pasteData = e.originalEvent.clipboardData.getData('text');
            if (!/^\d+$/.test(pasteData)) {
                // Si el pegado contiene no-números, lo limpiamos después del evento
                setTimeout(() => {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
                }, 0);
            }
        });
    });
</script>
