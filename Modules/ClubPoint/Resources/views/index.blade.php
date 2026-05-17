@extends('backEnd.master')
@section('styles')
    <link rel="stylesheet" href="{{asset(asset_path('modules/product/css/product_index.css'))}}">
@endsection
@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor ign-set-product-point">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-xl-8">
                    <div class="white_box_30px mb_30">
                        <div class="tab-content">
                            @if (permissionCheck('product.get-data'))
                                <div role="tabpanel" class="tab-pane fade active show" id="order_processing_data">
                                    <div class="box_header common_table_header ">
                                        <div class="main-title d-md-flex">
                                            <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('product.product_list') }}</h3>
                                        </div>
                                    </div>
                                    <div class="QA_section QA_section_heading_custom check_box_table">
                                        <div class="QA_table">
                                            <!-- table-responsive -->
                                            <div class="" id="product_list_div">
                                                @include('clubpoint::product_point_list')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    @if(env('CLUB_POINT_SET_RANGE')=='true')
                        <div class="white_box_30px mb_30">
                            <form id="multiple_point">
                                @csrf
                                <h4>{{__('product.Set Point for Product Within a Range')}}</h4>
                                <div class="row align-items-center">
                                    <div class="col-lg-6 mb-3">
                                        <label class="primary_input_label mb-0" for="multiple"> {{__('product.Set Point for multiple products') }}</label>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input class="primary_input_field" name="multiple" id="multiple" placeholder="{{__('clubpoint.multiple')}}" type="number" min="0" step="{{step_decimal()}}" value="">
                                        <span class="text-danger" id="error_multiple">{{ $errors->first('multiple')}}</span>
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-lg-6 mb-3">
                                        <label class="primary_input_label mb-0" for="main"> {{ __('product.Min Price') }}</label>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input class="primary_input_field" name="min" id="main" placeholder="{{getNumberTranslate(0)}}" type="number" min="0" step="{{step_decimal()}}" value="{{old('main')?old('main'):0}}">
                                        <span class="text-danger" id="error_main">{{ $errors->first('main')}}</span>
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-lg-6 mb-3">
                                        <label class="primary_input_label mb-0" for="max"> {{ __('product.Max Price') }}</label>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input class="primary_input_field" name="max" id="max" placeholder="{{getNumberTranslate(0)}}" type="number" min="0" step="{{step_decimal()}}" value="{{old('max')?old('max'):0}}">
                                        <span class="text-danger" id="error_max">{{ $errors->first('max')}}</span>
                                    </div>
                                </div>
                                <div class="text-right mt-1">
                                    <button class=" btn-toolkit btn-primary btn-icon saveBtn">{{__('common.save')}}</button>
                                </div>
                            </form>
                        </div>
                    @endif
                    @if(env('CLUB_POINT_SET_GLOBAL')=='true')
                        <div class="white_box_30px mb_30">
                            <form id="set_point">
                                @csrf
                                <h4>{{__('product.Set Point for all Products')}}</h4>
                                <div class="row align-items-center">
                                    <div class="col-lg-6 mb-3">
                                        <label class="primary_input_label mb-0" for="set"> {{__('clubpoint.set_point') }} <i class="ti-info-alt" title="If you want to earn all the points, first set the points that will be multiplied with the price of your product.Like:(set point 10 * price 10) = Total Point 100"></i> </label>
                                    </div>
                                    <div class="col-lg-6 mb-3">
                                        <input class="primary_input_field" name="set" id="set" placeholder="{{__('clubpoint.set_point')}}" type="number" min="1" step="{{step_decimal()}}" value="">
                                        <span class="text-danger" id="error_set"></span>
                                    </div>
                                </div>
                                <div class="text-right mt-1">
                                    <button class=" btn-toolkit btn-primary btn-icon saveBtn">{{__('common.save')}}</button>
                                </div>
                            </form>
                        </div>
                    @endif
                    <div class="white_box_30px mb_30">

                        <h3 class="mb-3 text-center fs-20 text-black">{{__('common.point_value')}}</h3>

                        {{-- INFORMACIÓN ACTUAL --}}
                        <div class="mb-4 p-3 rounded" style="background:#f8f9fa;">
                            <div class="text-center">
                                <div class="my-3">
                                    <span class="badge bg-color-orange p-3 text-white" style="font-size:16px;">
                                        1 {{ __('common.point') }} = {{ single_price($wallet_point->wallet_point) }}
                                    </span>
                                </div>
                            </div>

                            <p class=" d-block mt-2 text-black text-center">
                                *{{ __('common.value_point_message') }}
                            </p>
                        </div>


                        {{-- FORMULARIO --}}
                        <form id="set_value_point" action="{{route('clubpoint.wallet.point.Create')}}" method="POST">
                            @csrf

                            <div class="row align-items-end">
                                <div class="col-12 mb-3">
                                    <label class="primary_input_label" for="wallet_point">
                                        {{__('common.set_value_point')}}
                                    </label>

                                    <input
                                        class="primary_input_field currency-mask"
                                        name="wallet_point"
                                        id="wallet_point"
                                        placeholder="$1500"
                                        type="text"
                                        required
                                        value="{{ old('wallet_point', $wallet_point->wallet_point) }}"
                                    >

                                    <span class="text-danger">
                                        {{ $errors->first('wallet_point') }}
                                    </span>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="confirm_change_checkbox"
                                        >
                                        <label class="form-check-label text-black" for="confirm_change_checkbox">
                                            <strong>
                                                {{ __('common.confir_point_message') }}
                                            </strong>
                                        </label>
                                    </div>
                                </div>

                            </div>
                            <div class="text-center  mt-2">
                                <button class=" btn-toolkit btn-primary btn-icon saveBtn"><i class="ti-check"></i> {{__('common.update')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="module_check" value="{{isModuleActive('MultiVendor')?'true':'false'}}">
    </section>
    <div class="product_detail_view_div">
    </div>
@include('backEnd.partials._deleteModalForAjax',['item_name' => __('common.product '),'form_id' =>
'product_delete_form','modal_id' => 'product_delete_modal', 'delete_item_id' => 'product_delete_id'])
@include('clubpoint::components.confirmation_modal')
@include('clubpoint::double_approval_moda')
@endsection
@push('scripts')
<script src="{{ asset('public/js/currency-mask.js') }}" defer></script>
<script type="text/javascript">
(function($){
        "use strict";
        var Table = '';

    function removeURLHash() {
        let path = window.location.pathname.split('/');

        // eliminar vacío final si existe
        if (path[path.length - 1] === '') {
            path.pop();
        }

        // eliminar último segmento real
        path.pop();

        let cleanPath = path.join('/') || '/';

        window.history.replaceState({}, document.title, window.location.origin + cleanPath);
    }

    $(document).ready(function(){
        // ====== VERIFICAR SI LLEGA CÓDIGO Y HACER PETICIÓN ======
        let modalCode = "{{ $modalCode ?? '' }}";
        const HTTP_CONFLICT = 409;

        if(modalCode){
            // Mostrar pre-loader mientras se valida
            $("#pre-loader").removeClass('d-none');

            // Hacer petición AJAX para validar y obtener datos
            $.ajax({
                url: "{{ route('clubpoint.modal-data', ':code') }}".replace(':code', modalCode),
                type: "GET",
                dataType: "json",
                success: function(response) {
                    $("#pre-loader").addClass('d-none');

                    if(response.success && response.data && response.data.new_data && typeof response.data.new_data === 'object'){
                        $('#modal_item_id').val(response.data.id);

                        // Los datos son válidos, mostrar modal
                        $('#double_approval_moda').modal('show');

                        let data = response.data.new_data;
                        let text = '';

                        Object.keys(data).forEach(function(key){

                            let value = data[key];

                            // Si el valor es objeto lo convertimos en texto plano
                            if (typeof value === 'object' && value !== null) {
                                value = JSON.stringify(value);
                            }

                            // --- VALIDACIÓN DINÁMICA ---
                            // Si la llave es 'wallet_point', la reemplazamos por la traducción de Laravel
                            let label = key;
                            if (key === 'wallet_point') {
                                label = "{{ __('product.Convert Point To Wallet') }}";
                            }
                            // ---------------------------
                            text += label + ': ' + single_price(value) + '\n';
                        });

                        // Convertimos saltos de línea en <br>
                        $('#new_data').html(text.replace(/\n/g, '<br>'));

                        // Puedes guardar los datos para usarlos después
                        window.modalData = response.data;
                    } else {
                        removeURLHash();
                    }
                },
                error: function(xhr, status, error) {
                    $("#pre-loader").addClass('d-none');
                    if(xhr.status === HTTP_CONFLICT) {
                        toastr.info(xhr.responseJSON.message || "{{ __('common.unknown_error_message') }}", "{{ __('common.attention') }}");
                        removeURLHash();
                    } else {
                        toastr.error(xhr.responseJSON.message || "{{ __('common.unknown_error_message') }}", "{{ __('common.warning') }}");
                        removeURLHash();
                    }
                }
            });
        }
        // ====== MOSTRAR CAMPO DE RAZONES AL HACER CLIC EN CANCELAR ======
        $('#cancel_approved_btn').on('click', function(e){
            e.preventDefault();

            // Ocultar información inicial
            $('#approval_info').slideUp();

            // Ocultar botones iniciales
            $('#initial_buttons').hide();
            $('#initial_buttons').removeClass('d-flex');

            // Mostrar campo de razones
            $('#cancel_reason_container').slideDown();

            // Mostrar botones de cancelación
            $('#cancel_buttons').show();
            $('#cancel_buttons').addClass('d-flex');

            // Enfocar en el textarea
            setTimeout(function(){
                $('#cancel_reason').focus();
            }, 300);
        });

        // ====== VOLVER A LA VISTA INICIAL ======
        $('#back_to_approval_btn').on('click', function(){
            // Mostrar información inicial
            $('#approval_info').slideDown();

            // Mostrar botones iniciales
            $('#initial_buttons').show();
            $('#initial_buttons').addClass('d-flex');

            // Ocultar campo de razones
            $('#cancel_reason_container').slideUp();

            // Ocultar botones de cancelación
            $('#cancel_buttons').hide();
            $('#cancel_buttons').removeClass('d-flex');

            // Limpiar el campo y errores
            $('#cancel_reason').val('');
            $('#error_cancel_reason').text('');
        });
        // ====== LIMPIAR MODAL AL CERRARSE ======
        $('#double_approval_moda').on('hidden.bs.modal', function () {
            // Resetear vista a estado inicial
            $('#approval_info').show();
            $('#initial_buttons').show();
            $('#initial_buttons').addClass('d-flex');
            $('#cancel_reason_container').hide();
            $('#cancel_buttons').hide();
            $('#cancel_buttons').removeClass('d-flex');

            // Limpiar valores
            $('#new_data').html('');
            $('#modal_item_id').val('');
            $('#cancel_reason').val('');
            $('#error_cancel_reason').text('');
        });

        // ====== CONFIRMAR APROBACIÓN (status 1 + hash) ======
        $('#confirm_approved_btn').on('click', function(){
            let itemHash = $('#modal_item_id').val();

            if(itemHash){
                $("#pre-loader").removeClass('d-none');

                $.ajax({
                    url: "{{ route('double_approval.update_status') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: itemHash,
                        status: 1
                    },
                    success: function(response) {
                        // Ocultamos loader y modal
                        $("#pre-loader").addClass('d-none');
                        $('#double_approval_moda').modal('hide');

                        toastr.success(response.message || "{{ __('common.approved_successfully') }}", "{{ __('common.success') }}");
                        if(window.modalData && window.modalData.new_data && window.modalData.new_data.wallet_point !== undefined){
                            $('#wallet_point').val(window.modalData.new_data.wallet_point);
                            if (Table) {
                                Table.ajax.reload(null, false);
                                Table.columns.adjust().draw(false);
                            }
                        }
                        removeURLHash();
                    },
                    error: function(xhr) {
                        $("#pre-loader").addClass('d-none');
                        toastr.error("{{ __('common.error_occurred') }}", "{{ __('common.error') }}");
                        removeURLHash();
                    }
                });
            }
        });

        //CONFIRMAR RECHAZO
        $('#confirm_cancel_btn').on('click', function(){
            let reason = $('#cancel_reason').val().trim();

            if(reason === ''){
                $('#error_cancel_reason').text('{{ __("validation.this_field_is_required") }}');
                $('#cancel_reason').focus();
                return;
            }

            $('#error_cancel_reason').text('');
            let itemHash = $('#modal_item_id').val();

            $("#pre-loader").removeClass('d-none');

            $.ajax({
                url: "{{ route('double_approval.update_status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: itemHash,
                    rejection_reason: reason,
                    status: 2
                },
                success: function(response) {
                    $("#pre-loader").addClass('d-none');
                    $('#double_approval_moda').modal('hide');

                    toastr.success(response.message || "{{ __('common.rejected_successfully') }}", "{{ __('common.success') }}");
                    removeURLHash();
                },
                error: function(xhr) {
                    $("#pre-loader").addClass('d-none');
                    toastr.error("{{ __('common.error_occurred') }}", "{{ __('common.error') }}");
                    removeURLHash();
                }
            });
        });
        let ajaxUrl = "{{route('clubpoint.get-point')}}";
        var columnData = [
            { data: 'DT_RowIndex', name: 'id',render:function(data){
                return numbertrans(data)
            }},
            { data: 'product_name', name: 'product_name' },
            { data: 'owner', name: 'owner' },
            { data: 'price', name: 'price' , className: 'col-price'},
            { data: 'price_based_point', name: 'price_based_point' },
            { data: 'point', name: 'club_point' },
            { data: 'action', name: 'action' }
        ]

        initGlobalDataTable("#productPointtTable", ajaxUrl, columnData);
    });

    // ==========================================
    // LÓGICA DE DOBLE APROBACIÓN
    // ==========================================

    // Obtenemos el valor enviado desde el controlador
    let isDoubleApproval = {{ $isDoubleApprovalActive ? 'true' : 'false' }};
    let pendingForm = null;

    // Interceptamos el click en cualquier botón de guardar (clase saveBtn)
    $('.saveBtn').on('click', function(e){

        
        if($('#wallet_point').val() == ''){
            
            toastr.error('Ingrese el valor del punto' ,"{{__('common.error')}}");
            return;
        }
        if(!$('#confirm_change_checkbox').prop("checked")){
            e.preventDefault();
            toastr.error('Confirme que reconoce el cambio que esta realizando' ,"{{__('common.error')}}");
            return;

        }

        // Limpiar el currency mask antes de enviar (siempre)
        if($('#wallet_point').val() !== ''){
            $('#wallet_point').val(cleanNumber($('#wallet_point').val()));
        }

        // Solo interceptamos si la doble aprobación está activa
        if(isDoubleApproval){
            e.preventDefault(); // Detenemos el envío normal

            // Guardamos referencia al formulario al que pertenece el botón presionado
            pendingForm = $(this).closest('form');

            // Mostramos la modal de doble aprobación
            $('#double_approval_modal').modal('show');
        }
        // Si no está activa, el formulario se envía normalmente (AJAX o POST según el caso)
    });

    // Al hacer click en "Confirmar" dentro de la modal
    $('#confirm_approval_btn').on('click', function(){
        if(pendingForm){
            $("#pre-loader").removeClass('d-none');

            // Transformamos el vaor a enviar
            if($('#wallet_point').val() !== ''){
                const val = cleanNumber($('#wallet_point').val());
                $('#wallet_point').val(val);
            }
            // Disparamos el submit del formulario guardado.
            // Esto activará los listeners de AJAX de abajo (para los forms 1 y 2)
            // o el submit nativo (para el form 3).
            

            pendingForm.submit();
        }
        $('#double_approval_modal').modal('hide');
    });

    $(document).on('submit', '#multiple_point',  function(event) {
        event.preventDefault();
        $("#pre-loader").removeClass('d-none');
        var formElement = $(this).serializeArray()
        var formData = new FormData();
        formElement.forEach(element => {
        formData.append(element.name, element.value);
        });
        formData.append('_token', "{{ csrf_token() }}");
        resetValidationErrors();
        $.ajax({
            url: "{{ route('clubpoint.multiple-Point-Create') }}",
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function(response) {
                Table.ajax.reload(null,true);
                toastr.success("{{__('common.created_successfully')}}", "{{__('common.success')}}");
                $("#pre-loader").addClass('d-none');
                $('#multiple').val('');
                $('#main').val('');
                $('#max').val('');
            },
            error: function(response) {
                toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                $("#pre-loader").addClass('d-none');
                showValidationErrors(response.responseJSON.errors);
            }
        });
    });
    $(document).on('submit', '#set_point',  function(event) {
        event.preventDefault();
        $("#pre-loader").removeClass('d-none');
        var formElement = $(this).serializeArray()
        var formData = new FormData();
        formElement.forEach(element => {
        formData.append(element.name, element.value);
        });
        formData.append('_token', "{{ csrf_token() }}");
        $('#error_set').text('');
        $.ajax({
            url: "{{ route('clubpoint.set-Point-Create') }}",
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function(response) {
                Table.ajax.reload(null,true);
                toastr.success("{{__('common.created_successfully')}}", "{{__('common.success')}}");
                $("#pre-loader").addClass('d-none');
                pointProductDataTable();
            },
            error: function(response) {
                toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                $("#pre-loader").addClass('d-none');
                $('#error_set').text(response.responseJSON.errors.set);
            }
        });
    });

    function showValidationErrors(errors) {
        $(' #error_multiple').text(errors.multiple);
        $(' #error_main').text(errors.min);
        $(' #error_max').text(errors.max);
    }
    function resetValidationErrors(){
        $('#error_multiple').text('');
        $('#error_main').text('');
        $('#error_max').text('');
    }
})(jQuery);
</script>
@endpush

