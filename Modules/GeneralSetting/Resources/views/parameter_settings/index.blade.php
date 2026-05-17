@extends('backEnd.master')

@section('mainContent')
<x-admin.section>
{{-- <section class="sms-breadcrumb mb-40 white-box">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <h1 class="text-black"></h1>
        </div>
    </div>
</section> --}}

<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-12">
                <div class="main-title d-flex justify-content-start align-items-center">
                    <h3 class="mb-0">{{ __('general_settings.parameter_settings.title') }}</h3>
                </div>
                <div class="white_box_30px box_shadow_white mb-20">
                    <div class="QA_section QA_section_heading_custom check_box_table">
                        <div class="QA_table table-responsive">
                            <table class="table Crm_table_active3 custom-hover-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('common.parameter') }}</th>
                                        <th scope="col">{{ __('common.status') }}</th>
                                        <th scope="col">{{ __('common.value_configuration') }}</th>
                                        <th scope="col">{{ __('common.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parameters as $parameter)
                                    <tr class="text-center">
                                        <td class="parameter {{ !$parameter->is_active ? 'disabled-value-config' : '' }}">
                                            <span class="parameter-text text-black">{{ __('general_settings.' . $parameter->parameter_name)  }}</span>
                                        </td>
                                        <td>
                                            <label class="switch_toggle">
                                                <input type="checkbox" class="parameter-toggle" data-id="{{ $parameter->id }}" {{ $parameter->is_active ? 'checked' : '' }}>
                                                <div class="slider round"></div>
                                            </label>
                                        </td>
                                        <td class="value-config-cell">
                                            <div class="input-group-container justify-content-center {{ !$parameter->is_active ? 'disabled-value-config' : '' }}">
                                                
                                                @if($parameter->slug == 'daily-count-failures')
                                                    <div class="value-pill">
                                                        <span class="pill-label text-black">{{ __('common.max_allowed') }}:</span>
                                                        <span class="pill-value text-black bold">{{ number_format($parameter->value_limit, 0, ',', '.') }}</span>
                                                    </div>

                                                @elseif($parameter->slug == 'cash-opening')
                                                    <div class="value-pill">
                                                        <span class="pill-label text-black">{{ __('common.initial_value') }}:</span>
                                                        <span class="pill-value text-black bold">${{ number_format($parameter->monetary_value, 0, ',', '.') }}</span>
                                                    </div>

                                                @elseif($parameter->slug == 'double-approval')
                                                    @foreach($staffs as $staff)
                                                        @if($parameter->staff_id == $staff->staff_id)
                                                            <div class="value-pill">
                                                                <span class="pill-label text-black">{{ __('common.staff') }}:</span>
                                                                <span class="pill-value text-black bold">{{ $staff->full_name ?? __('common.not_assigned') }}</span>
                                                            </div>
                                                            @break
                                                        @endif
                                                    @endforeach
                                                @endif

                                                @if($parameter->slug == 'entrepreneur-data-ttl')
                                                    <div class="value-pill">
                                                        <span class="pill-label text-black">{{ __('Meses') }}:</span>
                                                        <span class="pill-value text-black bold">{{ $parameter->value_limit ?? 0 }}</span>
                                                    </div>

                                                @elseif($parameter->slug == 'kyc-readonly-fields')
                                                    <div class="value-pill">
                                                        <span class="pill-label text-black">{{ __('Campos Bloqueados') }}:</span>
                                                        @php
                                                            $fields = is_string($parameter->json_value) ? json_decode($parameter->json_value, true) : ($parameter->json_value ?? []);
                                                        @endphp
                                                        <span class="pill-value text-black bold">{{ count((array)$fields) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="value-config-cell2">
                                            @if($parameter->slug != 'product-stock')
                                                <a href="#"
                                                    class="btn-toolkit btn-primary text-white edit_parameter"
                                                    data-value='@json($parameter)'
                                                    title="{{ __('common.edit') }}">
                                                    <i class="ti-pencil"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" id="parameter_id" name="id">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</x-admin.section>

<script>
    $(document).ready(function() {
        // Bandera para saber si debemos activar el switch al guardar el modal
        let _pendingActivation = false;

        // Toggle status change with AJAX
        $('.parameter-toggle').on('change', function() {
            let toggleElement = this;
            let isChecked = $(this).is(':checked');
            let row = $(this).closest('tr');
            
            // Datos del parámetro
            let editBtn = row.find('.edit_parameter');
            let parameterData = editBtn.length ? editBtn.data('value') : null;
            let slug = parameterData ? parameterData.slug : '';

            // ============================================================
            // LOGICA DOBLE APROBACIÓN
            // ============================================================
            if (slug === 'double-approval' && isChecked) {
                // Si NO tiene staff asignado
                if (!parameterData.staff_id) {
                    
                    // 1. Revertimos visualmente el switch (apagado)
                    $(toggleElement).prop('checked', false);

                    // 2. Activamos la bandera: "El usuario quería activar esto"
                    _pendingActivation = true;

                    // 3. Abrimos el modal silenciosamente (Sin warning)
                    editBtn.trigger('click');

                    return; // Detenemos aquí
                }
            }

            // ... (Resto de lógica normal del switch) ...
            $("#pre-loader").removeClass('d-none');
            let parameterId = $(this).data('id');
            let container = row.find('.input-group-container');
            let parameter = row.find('.parameter');

            // Feedback visual inmediato
            if(!isChecked) {
                container.addClass('disabled-value-config');
                parameter.addClass('disabled-value-config');
            } else {
                container.removeClass('disabled-value-config');
                parameter.removeClass('disabled-value-config');
            }

            $.ajax({
                url: "{{ route('parameter_settings.update_active_status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: parameterId,
                    is_active: isChecked ? 1 : 0
                },
                success: function(response) {
                    toastr.success("{{ __('common.updated_successfully') }}", "{{ __('common.success') }}");
                },
                error: function(xhr) {                    
                    var errorMessage = xhr.responseJSON.message ?? "{{ __('common.error_message') }}";
                    toastr.error(errorMessage, "{{ __('common.error') }}", { timeOut: 5000 });
                    
                    // Revertir si falla
                    $(toggleElement).prop('checked', !isChecked);
                    if(isChecked) {
                        container.addClass('disabled-value-config');
                        parameter.addClass('disabled-value-config');
                    } else {
                        container.removeClass('disabled-value-config');
                        parameter.removeClass('disabled-value-config');
                    }
                },
                complete: function() {
                    $("#pre-loader").addClass('d-none');
                }
            });
        });

        // Limpieza de bandera al cerrar modal
        $('#edit_parameter_modal').on('hidden.bs.modal', function () {
            $('#edit_form')[0].reset();
            $('.parameter-fields').addClass('d-none');
            $('#parameter_id').val('');
            $('#staff_id').val('').niceSelect('update').trigger('change');

            // IMPORTANTE: Reseteamos la intención de activación si cancelan
            _pendingActivation = false;
        });

        // Lógica de Edición (Click en lápiz)
        $(document).on('click', '.edit_parameter', function (e) {
            e.preventDefault();

            let parameter = $(this).data('value');

            $('#parameter_id').val(parameter.id);
            toggleEditFieldsBySlug(parameter.slug);

            // Carga de datos específicos
            if (parameter.slug === 'product-stock') {
                $('#min_value').val(parameter.min_value);
                $('#max_value').val(parameter.max_value);
            }

            if (parameter.slug === 'daily-count-failures') {
                $('#value_limit').val(formatNumberWithDots(parameter.value_limit));
            }

            if (parameter.slug === 'cash-opening') {
                $('#monetary_value').val(formatNumberWithDots(Math.floor(parameter.monetary_value)));
            }

            // NUEVO: Cargar los meses de vigencia
            if (parameter.slug === 'entrepreneur-data-ttl') {
                $('#ttl_value_limit').val(parameter.value_limit);
            }

            // NUEVO: Cargar el arreglo de campos bloqueados (Checkboxes)
            if (parameter.slug === 'kyc-readonly-fields') {
                let fields = [];
                if (parameter.json_value) {
                    fields = typeof parameter.json_value === 'string' ? JSON.parse(parameter.json_value) : parameter.json_value;
                }
                
                // 1. Desmarcar todos por defecto
                $('.kyc-field-checkbox').prop('checked', false);
                
                // 2. Marcar solo los que vienen guardados en base de datos
                if (Array.isArray(fields)) {
                    fields.forEach(function(field) {
                        $('.kyc-field-checkbox[value="' + field + '"]').prop('checked', true);
                    });
                }
            }

            // Pre-selección de Staff (Tu requerimiento)
            if (parameter.slug === 'double-approval') {
                if (parameter.staff_id) {
                    // Si ya tiene staff, lo ponemos
                    $('#staff_id').val(parameter.staff_id).niceSelect('update').trigger('change');
                } else {
                    // Si no, limpiamos
                    $('#staff_id').val('').niceSelect('update').trigger('change');
                }
            }

            $('#edit_parameter_modal').modal('show');
        });

        // Submit del Formulario (Guardar cambios)
        $(document).on('submit', '#edit_form', function (e) {
            e.preventDefault();
            $("#pre-loader").removeClass('d-none');

            let id = $('#parameter_id').val();
            let url = "{{ url('generalsetting/parameter-settings-update') }}/" + id;
            let formData = new FormData(this);

            // Limpieza de formatos numéricos
            let monetaryInput = $('#monetary_value');
            if(monetaryInput.length && monetaryInput.val()) {
                let cleanValue = monetaryInput.val().replace(/\./g, ""); 
                formData.set('monetary_value', parseInt(cleanValue, 10)); 
            }
            let limitInput = $('#value_limit');
            if(limitInput.length && limitInput.val()) {
                let cleanLimit = limitInput.val().replace(/\./g, ""); 
                formData.set('value_limit', parseInt(cleanLimit, 10)); 
            }

            // NUEVO: Inyectar Vigencia
            let ttlInput = $('#ttl_value_limit');
            if(!ttlInput.closest('.parameter-fields').hasClass('d-none') && ttlInput.val()) {
                formData.set('value_limit', parseInt(ttlInput.val(), 10)); 
            }

            // NUEVO: Inyectar JSON de Campos KYC
            let kycGrid = $('.parameter-fields[data-slug="kyc-readonly-fields"]');
            if(!kycGrid.hasClass('d-none')) {
                let selected = [];
                // ✅ Corrección: Buscamos tanto campos individuales como IDs de sección
                $('.kyc-field-checkbox:checked, .kyc-section-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });
                formData.set('json_value', JSON.stringify(selected));
            }

            formData.append('_token', "{{ csrf_token() }}");

            // PETICIÓN 1: Guardar Valores (Staff, Montos, etc)
            $.ajax({
                url: url,
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function () {
                    // Si la bandera está activa, hacemos la PETICIÓN 2: Activar Switch
                    if (_pendingActivation) {
                        $.ajax({
                            url: "{{ route('parameter_settings.update_active_status') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                id: id,
                                is_active: 1 // Forzamos activación
                            },
                            success: function() {
                                toastr.success("{{ __('common.updated_successfully') }}");
                                window.location.reload();
                            },
                            error: function() {
                                // Si falla activar, al menos se guardó el staff
                                toastr.warning("Staff guardado, pero no se pudo activar automáticamente");
                                window.location.reload();
                            }
                        });
                    } else {
                        // Flujo normal
                        toastr.success("{{ __('common.updated_successfully') }}");
                        window.location.reload();
                    }
                },
                error: function (xhr) {
                    var errorMessage = "{{ __('common.error_message') }}";
                    if (xhr.responseJSON && xhr.responseJSON.details) {
                        errorMessage = xhr.responseJSON.details;
                    };
                    toastr.error(errorMessage);
                }
            });
        });
    });

    $(document).on('click', '.delete_parameter', function (e) {
        e.preventDefault();
        let route = $(this).data('value');
        confirm_modal(route);
    });

    function toggleEditFieldsBySlug(slug) {
        $('.parameter-fields').addClass('d-none');
        $('.parameter-fields[data-slug="' + slug + '"]').removeClass('d-none');
    }

    // Función auxiliar para formatear con puntos (Miles)
    function formatNumberWithDots(num) {
        if (!num) return '';
        
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }


</script>
@include('generalsetting::parameter_settings.edit_modal')

@include('backEnd.partials.delete_modal', [
    'item_name' => __('general_settings.parameter_settings.title')
])
@endsection