<div class="modal fade admin-query" id="edit_parameter_modal">
    <div class="modal-dialog modal_800px modal-dialog-centered">
        <div class="modal-content custom-modal-content">
            
            <div class="modal-header custom-modal-header">
                <h4 class="modal-title">{{ __('common.edit') }}</h4> 
                <button type="button" class="close custom-close-btn" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body custom-modal-body">
                <form id="edit_form" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="parameter_id">

                    {{-- Parámetro: Intentos Fallidos --}}
                    <div class="row parameter-fields d-none" data-slug="daily-count-failures">
                        <div class="col-lg-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">{{ __('common.max_allowed') }}</label>
                                <input
                                    type="number"
                                    name="value_limit"
                                    id="value_limit"
                                    class="primary_input_field modern-input currency-mask"
                                    placeholder="Ej: 5.000"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Parámetro: Doble Aprobación --}}
                    <div class="row parameter-fields d-none tall-field-container" data-slug="double-approval">
                        <div class="col-lg-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">
                                    {{ __('common.staff') }}
                                </label>

                                <div class="select-wrapper">
                                    <select
                                        name="staff_id"
                                        id="staff_id"
                                        class="primary_select w-100 modern-input scrollable-select"
                                        data-search-text="{{ __('common.search') }}"
                                    >
                                        <option value="" disabled selected>
                                            {{ __('common.select_staff') }}
                                        </option>

                                        @foreach($staffs as $staff)
                                            <option value="{{ $staff->staff_id }}">
                                                {{ $staff->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="focus"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Parámetro: Apertura de Caja --}}
                    <div class="row parameter-fields d-none" data-slug="cash-opening">
                        <div class="col-lg-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">{{ __('common.initial_value') }}</label>
                                <div class="input-symbol-wrapper">
                                    <span class="currency-symbol">$</span>
                                    <input
                                        type="text"
                                        name="monetary_value"
                                        id="monetary_value"
                                        class="primary_input_field modern-input currency-mask pl-4"
                                        placeholder="0"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Parámetro: Vigencia de Datos (KYC) --}}
                    <div class="row parameter-fields d-none" data-slug="entrepreneur-data-ttl">
                        <div class="col-lg-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label">{{ __('Vigencia de Datos (Meses)') }}</label>
                                <input
                                    type="number"
                                    id="ttl_value_limit"
                                    class="primary_input_field modern-input"
                                    placeholder="Ej: 6"
                                    min="0"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Parámetro: Campos Bloqueados KYC (JERÁRQUICO) --}}
                    <div class="row parameter-fields d-none tall-field-container" data-slug="kyc-readonly-fields">
                        <div class="col-lg-12">
                            <div class="primary_input mb-25">
                                <label class="primary_input_label mb-3">{{ __('general_settings.kyc_fields') }}</label>
                                
                                <div class="kyc-checkbox-grid pl-3" style="max-height: 450px; overflow-y: auto;">
                                    @if(isset($availableKycFields))
                                        @foreach($availableKycFields as $sectionId => $sectionData)
                                            <div class="kyc-section-group mb-4 pb-2" style="border-bottom: 1px solid #f1f3f5;">
                                                {{-- Checkbox de Sección (Padre) --}}
                                                <div class="section-header mb-2" style="background: #f8f9fa; padding: 8px 12px; border-radius: 8px;">
                                                    <label class="d-flex align-items-center mb-0" style="cursor: pointer; font-weight: 700;">
                                                        <input type="checkbox" class="kyc-section-checkbox mr-2" value="{{ $sectionId }}">
                                                        <span class="text-black">{{ $sectionData['label'] }}</span>
                                                    </label>
                                                </div>
                                                
                                                {{-- Checkboxes de Campos (Hijos) --}}
                                                <div class="row pl-4">
                                                    @foreach($sectionData['fields'] as $fieldKey => $fieldLabel)
                                                        <div class="col-lg-4 col-md-6 mb-2">
                                                            <label class="d-flex align-items-center mb-0" style="cursor: pointer;">
                                                                <input type="checkbox" class="kyc-field-checkbox mr-2" 
                                                                        value="{{ $fieldKey }}" 
                                                                        data-parent="{{ $sectionId }}">
                                                                <span class="text-black" style="font-size: 12px;">{{ $fieldLabel }}</span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-10 footer-btn-container">
                        <div class="col-lg-12">
                            <button type="submit" class="primary-btn fix-gr-bg w-100 modern-submit-btn">
                                <span class="btn-text">{{ __('common.update') }}</span>
                                <i class="ti-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const submitBtn = $('.modern-submit-btn');
        const staffSelect = $('#staff_id');

        // 1. Script de máscara de moneda
        $(document).on('keyup', '.currency-mask', function() {
            let val = $(this).val().replace(/\D/g, "");
            $(this).val(val.replace(/\B(?=(\d{3})+(?!\d))/g, "."));
        });

        // 2. Validación de Staff para Doble Aprobación
        function validateStaffSelection() {
            const isDoubleApproval = $('.parameter-fields[data-slug="double-approval"]').hasClass('d-none') === false;
            if (isDoubleApproval) {
                if (!staffSelect.val()) {
                    submitBtn.prop('disabled', true).addClass('disabled-btn').removeClass('primary-btn');
                } else {
                    submitBtn.prop('disabled', false).removeClass('disabled-btn').addClass('primary-btn');
                }
            } else {
                submitBtn.prop('disabled', false).removeClass('disabled-btn');
            }
        }

        staffSelect.on('change', validateStaffSelection);
        $('#edit_parameter_modal').on('shown.bs.modal', validateStaffSelection);
        $('#edit_parameter_modal').on('hidden.bs.modal', () => submitBtn.prop('disabled', false).removeClass('disabled-btn'));

        // 3. Lógica de Checkboxes KYC (Padre/Hijo)
        // Seleccionar PADRE selecciona todos los HIJOS
        $(document).on('change', '.kyc-section-checkbox', function() {
            const sectionId = $(this).val();
            const isChecked = $(this).is(':checked');
            $(`.kyc-field-checkbox[data-parent="${sectionId}"]`).prop('checked', isChecked);
        });

        // Si se alteran los HIJOS, se actualiza el estado del PADRE
        $(document).on('change', '.kyc-field-checkbox', function() {
            const sectionId = $(this).data('parent');
            const totalChildren = $(`.kyc-field-checkbox[data-parent="${sectionId}"]`).length;
            const totalChecked = $(`.kyc-field-checkbox[data-parent="${sectionId}"]:checked`).length;
            $(`.kyc-section-checkbox[value="${sectionId}"]`).prop('checked', totalChildren === totalChecked);
        });
    });
</script>