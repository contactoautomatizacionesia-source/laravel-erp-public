@extends('backEnd.master')
@section('mainContent')
<style>
    .exp-badge{
        background-color: #fff;
        position: absolute;
        top: -15px;
        right: 0px;
        border-radius: 20px;

    }
    .lot-indicator{
        background-color: #fff;
        position: absolute;
        top: 15px;
        right: 0px;
        border-radius: 20px;

    }
    .position-relative { position: relative !important; }

    .custom-error-badge {
        position: absolute;
        top: 35px;
        left: 0;
        width: 170px;
        background-color: #FFF1F0;
        color: #D92D20;
        padding: 4px 8px;
        border-radius: 8px;
        white-space: normal;
        text-align: center;
        z-index: 110;
        font-size: 10px;
        box-shadow: 0px 4px 6px rgba(0,0,0,0.05);
        display: none;
        line-height: 1.2;
    }

    .exp-badge {
        position: absolute;
        top: -18px;
        right: 0;
        z-index: 100;
    }
</style>
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">

        <div class="row mb-10">
            <div class="col-md-12">
                <div class="d-flex align-items-center main-title">
                    <x-backEnd.back-button :text="false" />
                    <h3 class="">{{ __('inventoryentry::inventory.new_entry') }}</h3>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Buscador de producto --}}
            <div class="col-md-12 mb-10">
                <div class="">
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="form-card pb-5">
                                <h3>{{__('common.select_product')}}</h3>
                                <div class="row mx-0">
                                    <div class="col-12 px-0">

                                        <label class="primary_input_label d-none" for="product_search">
                                            {{ __('inventoryentry::inventory.product') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="mb_20">

                                            <select
                                            data-validate="required"
                                            name="product_search"
                                            id="product_search"
                                            class="nice-select-ajax style2 wide"
                                            data-url="{{ url('/products/get-active-products') }}"
                                            data-initial="true"
                                            data-sync-text="true"
                                            data-text-target="product_name"
                                            >

                                            <option value="">{{__('common.please_select')}} </option>


                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                        <div class="col-xl-6">
                            <div class="product_detail_view_div"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de SKUs (se muestra tras seleccionar producto) --}}
            <div class="col-md-12" id="skus_section">
                <div class="">
                    <div class="d-flex flex-column flex-md-row  justify-content-between mb-15 main-title">
                        <h3 class="mb-0">
                            <i class="ti-package mr-2"></i>
                            {{ __('inventoryentry::inventory.new_entry') }} — <span id="skus_product_name"></span>
                        </h3>
                        <span class="text-muted" style="font-size:.85rem;">
                            <i class="ti-alert mr-1"></i>{!! __('inventoryentry::inventory.warehouse_info') !!}
                        </span>
                    </div>
                    <div class="dataTables_wrapper my-3">
                        <x-admin.table-container>
                        <div class="table-responsive ign-scrollbar">
                            <table class="table dataTable" id="skuEntryTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('inventoryentry::inventory.variant') }}</th>
                                        <th>{{ __('inventoryentry::inventory.lot_number') }} <span>*</span></th>
                                        <th>{{ __('inventoryentry::inventory.manufacture_date') }}</th>
                                        <th>{{ __('inventoryentry::inventory.expiration_date') }}</th>
                                        <th>{{ __('inventoryentry::inventory.quantity') }} <span>*</span></th>
                                        <th>{{ __('inventoryentry::inventory.unit_cost') }}</th>
                                        <th>{{ __('inventoryentry::inventory.supplier') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="skuEntryBody">
                                    {{-- Filas generadas por JS --}}
                                    <tr>
                                        <td colspan="7">
                                            <p class="text-center">{{__('common.select_product')}}</p>

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        </x-admin.table-container>
                    </div>
                    {{-- Campo común: notas del lote --}}
                    <div class="row pt-3  border-top">
                        <div class="col-md-12">
                            <label class="primary_input_label" for="common_notes">
                                {{ __('inventoryentry::inventory.notes') }}
                            </label>
                            <textarea type="text" id="common_notes" class="primary_textarea" placeholder="{{ __('inventoryentry::inventory.notes') }}"></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-20">
                        <button type="button" id="btn_save_entries" class="btn-toolkit btn-primary">
                            <i class="ti-save mr-1"></i> {{ __('inventoryentry::inventory.confirm_save') }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<div class="modal fade" id="confirmSaveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Confirmar guardado</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ti-close"></i></button>
            </div>

            <div class="modal-body">

                <!-- Resumen -->
                <div id="entries_summary" class="form-card"></div>



                <!-- Checkbox -->
                <div class="form-check mt-3">
                    <x-backEnd.checkbox name="stay_on_page" :required="false" :field="trans('Permanecer en esta vista para registrar otro ingreso')" :checked="false" />
                </div>

                <!-- Resultado -->
                <div id="save_result" class="mt-3 d-none"></div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">Cancelar</button>
                <button type="button" id="confirm_save_btn" class="btn-toolkit btn-primary btn-icon">
                    <i class="ti-save"></i> Confirmar
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('public/js/nice-ajax.js') }}" defer></script>

<script>
$(document).ready(function () {

    const storeUrl   = "{{ route('inventory_entry.store') }}";
    const findLotUrl = "{{ route('inventory_entry.lots.find') }}";
    const searchUrl  = "{{ route('inventory_entry.products.search') }}";
    const skusUrl    = "{{ route('inventory_entry.products.skus', ['productId' => ':id']) }}";

    const labelValid    = "{{ __('inventoryentry::inventory.status_valid') }}";
    const labelExpiring = "{{ __('inventoryentry::inventory.status_expiring') }}";
    const labelExpired  = "{{ __('inventoryentry::inventory.status_expired') }}";
    const labelLocation = "{{ __('inventoryentry::inventory.location_default') }}"; // valor fijo enviado en payload
    const labelLotFound = "{{ __('inventoryentry::inventory.lot_found') }}";
    const labelLotNew   = "{{ __('inventoryentry::inventory.lot_new') }}";
    const labelDateEstatus = "{{ __('inventoryentry::inventory.status_date') }}";

    $('#product_search').on('change', function () {
        let product = $(this).find(':selected');
        renderProductInfo({
            id: product.val(),
            text: product.text()
        });

        loadAndRenderSkus(product.val());
    });

    $(document).on('click', '.product_detail', function(event){
            event.preventDefault();
            let id = $(this).data('id');
            $('#pre-loader').removeClass('d-none');
            $.post('{{ route('product.show') }}', {_token:'{{ csrf_token() }}', id:id}, function(data){
                $('.product_detail_view_div').html(data);
                $('#productDetails').modal('show');
                $('#pre-loader').addClass('d-none');
            });
        });
    function renderProductInfo(product) {
        $.post('{{ route('product.showSimple') }}', {_token:'{{ csrf_token() }}', id:product.id}, function(data){
            $('.product_detail_view_div').html(data);
        });
        $('#skus_product_name').text(product.text);

    }

    // ─── Cargar SKUs y renderizar tabla ──────────────────────────
    function loadAndRenderSkus(productId) {
        let url = skusUrl.replace(':id', productId);
        $('#pre-loader').removeClass('d-none');

        $.get(url, function (data) {
            renderSkuRows(data.skus);
            $('#skus_section').show();
            $('#pre-loader').addClass('d-none');
        }).fail(function () {
            $('#pre-loader').addClass('d-none');
            toastr.error("{{ __('common.something_wrong') }}");
        });
    }

    function renderSkuRows(skus) {
        let $body = $('#skuEntryBody').empty();

        skus.forEach(function (sku, idx) {
            let row = `
            <tr class="sku-entry-row" data-sku-id="${sku.id}" data-sku="${sku.text}">
                <td class="align-middle">
                    <strong>${sku.text}</strong>
                    <span class="badge_5 d-block">Stock: ${sku.product_stock}</span>
                </td>

                <td>
                    <div class="d-flex align-items-center">
                        <input type="text"
                               class="primary_input_field lot-number-input"
                               placeholder="{{ __('inventoryentry::inventory.lot_number_hint') }}"
                               autocomplete="off"
                               data-idx="${idx}">
                        <span class="lot-indicator"></span>
                    </div>
                    <small class="lot-hint text-muted d-none mt-1" style="font-size:.75rem;"></small>
                </td>
                <td>
                    <input type="date" class="primary_input_field manufacture-date-input" data-idx="${idx}" max="${new Date().toISOString().split('T')[0]}">
                </td>
                <td>
                    <div class="d-flex align-items-center position-relative">
                        <small class="date-error-msg custom-error-badge" data-idx="${idx}" style="display:none;"></small>
                        <input type="date" class="primary_input_field expiration-date-input" data-idx="${idx}" disabled>
                        <span class="exp-badge ml-1"></span>

                    </div>
                </td>
                <td>
                    <input type="number" class="primary_input_field quantity-input" min="1" step="1"
                           placeholder="0" data-idx="${idx}">
                </td>
                <td>
                    <input type="number" class="primary_input_field unit-cost-input" min="0" step="0.01"
                           placeholder="{{ __('inventoryentry::inventory.unit_cost_hint') }}" data-idx="${idx}">
                </td>
                <td>
                    <input type="text" class="primary_input_field supplier-input"
                           placeholder="{{ __('inventoryentry::inventory.supplier') }}" data-idx="${idx}">
                </td>
            </tr>`;
            $body.append(row);
        });
    }

    $(document).on('keydown', '.quantity-input', function (e) {
        // Previene la escritura de -, +, e, E y el punto/coma si solo quieres enteros
        if (['-', '+', 'e', 'E', '.', ','].includes(e.key)) {
            e.preventDefault();
        }
    });

    $(document).on('input', '.quantity-input', function () {
        // Asegura que solo se ingresen números enteros positivos eliminando cualquier otro carácter
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Definir la fecha de hoy para el límite de fabricación
    const today = new Date().toISOString().split('T')[0];
    $('.manufacture-date-input').attr('max', today);

    // Función para actualizar el Badge de Estado (Vencido, Próximo, Vigente)
    function updateExpirationBadge($input) {
        let $badge = $input.closest('td').find('.exp-badge');
        let val = $input.val();

        if (!val) { $badge.html(''); return; }

        let todayObj = new Date(); todayObj.setHours(0,0,0,0);
        let expObj = new Date(val + 'T00:00:00');
        let diff = Math.ceil((expObj - todayObj) / 86400000);

        let cls, label;
        // Variables globales de traducción de Laravel
        let txtExpired  = typeof labelExpired !== 'undefined' ? labelExpired : 'Vencido';
        let txtExpiring = typeof labelExpiring !== 'undefined' ? labelExpiring : 'Próximo';
        let txtValid    = typeof labelValid !== 'undefined' ? labelValid : 'Vigente';

        if (diff < 0)       { cls = 'badge_2 micro_badge'; label = txtExpired; }
        else if (diff <= 30){ cls = 'badge_3 micro_badge'; label = txtExpiring; }
        else                { cls = 'badge_1 micro_badge'; label = txtValid; }

        $badge.html(`<span class="${cls}" style="white-space:nowrap;">${label}</span>`);
    }

    // Evento: Cambio en FECHA DE FABRICACIÓN
    $(document).on('change', '.manufacture-date-input', function() {
        const idx = $(this).data('idx');
        const mfgValue = $(this).val();
        const $expInput = $(`.expiration-date-input[data-idx="${idx}"]`);
        const $errorMsg = $(`.date-error-msg[data-idx="${idx}"]`);

        // Asignar el texto de error traducido desde Laravel
        if (typeof labelDateEstatus !== 'undefined') {
            $errorMsg.text(labelDateEstatus);
        }

        if (mfgValue) {
            $expInput.prop('disabled', false);

            // Calcular día siguiente
            let date = new Date(mfgValue + 'T00:00:00');
            date.setDate(date.getDate() + 1);
            const nextDay = date.toISOString().split('T')[0];

            // Setear valor y mínimo
            $expInput.val(nextDay);
            $expInput.attr('min', nextDay);

            // Forzar actualización de badge de estado
            updateExpirationBadge($expInput);

            $errorMsg.fadeOut(200);
            $expInput.css('border-color', '');
        } else {
            $expInput.prop('disabled', true).val('');
            updateExpirationBadge($expInput);
            $errorMsg.fadeOut(200);
        }
    });

    // Evento: Cambio manual en FECHA DE VENCIMIENTO
    $(document).on('change', '.expiration-date-input', function() {
        const idx = $(this).data('idx');
        const expValue = $(this).val();
        const mfgValue = $(`.manufacture-date-input[data-idx="${idx}"]`).val();
        const $errorMsg = $(`.date-error-msg[data-idx="${idx}"]`);

        // Actualizar badge de estado (Vencido/Vigente)
        updateExpirationBadge($(this));

        // Validar contra fecha de fabricación
        if (expValue && mfgValue) {
            if (expValue <= mfgValue) {
                // Asegurar que el texto esté actualizado antes de mostrar
                if (typeof labelDateEstatus !== 'undefined') {
                    $errorMsg.text(labelDateEstatus);
                }
                $errorMsg.fadeIn(200);
                $(this).css('border-color', '#d92d20');
            } else {
                $errorMsg.fadeOut(200);
                $(this).css('border-color', '');
            }
        }
    });



    // ─── Búsqueda de lote existente por fila ─────────────────────
    let lotTimers = {};
    $(document).on('keyup', '.lot-number-input', function () {
        let $row      = $(this).closest('tr');
        let $hint     = $row.find('.lot-hint');
        let $indicator= $row.find('.lot-indicator');
        let val       = $(this).val().trim();
        let idx       = $(this).data('idx');

        clearTimeout(lotTimers[idx]);
        if (val.length < 2) { $hint.text(''); $indicator.html(''); return; }

        $indicator.html(`
            <span class="badge_6 micro_badge">
                <i class="fas fa-spinner fa-spin mr-1"></i>
            </span>
        `);

        lotTimers[idx] = setTimeout(function () {
            $.get(findLotUrl, { lot_number: val }, function (data) {
                if (data.found) {
                    $row.find('.manufacture-date-input').val(data.manufacture_date || '');
                    $row.find('.expiration-date-input').val(data.expiration_date || '').trigger('change');
                    $hint.html(`<span class="text-muted line-normal d-inline-block"> ${labelLotFound}</span>`);
                    $indicator.html('<span class="badge_5 micro_badge">Existente</span>');
                } else {
                    $hint.html(`<span class="text-muted line-normal d-inline-block"> ${labelLotNew}</span>`);
                    $indicator.html('<span class="badge_1 micro_badge">Nuevo</span>');
                }
            });
        }, 500);
    });

    let currentEntries = [];

    $('#btn_save_entries').on('click', function () {

        let result = collectEntries();

        if (result.hasError) return;

        if (result.entries.length === 0) {
            toastr.warning("No hay registros para guardar");
            return;
        }

        currentEntries = result.entries;

        // Render resumen
        let html = `<h3>Resumen de ingreso</h3>`;
        html += `<table class="table table-sm text-center">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Cantidad</th>
                </tr>
            </thead><tbody>`;

        currentEntries.forEach(e => {
            html += `
                <tr>
                    <td>${e.sku}</td>
                    <td>${e.quantity}</td>
                </tr>
            `;
        });

        html += `</tbody></table>`;

        $('#entries_summary').html(html);
        $('#save_result').addClass('d-none').html('');

        $('#confirmSaveModal').modal('show');
    });

    $('#confirm_save_btn').on('click', function () {

        let stay = $('#stay_on_page').is(':checked');
        let $btn = $(this).prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        $.post(storeUrl, {
            _token: "{{ csrf_token() }}",
            entries: currentEntries,
        }, function (response) {

            if (response.success) {

                $('#save_result')
                    .removeClass('d-none')
                    .html(`<div class="alert alert-success">${response.message}</div>`);

                if (!stay) {
                    setTimeout(function () {
                        window.location.href = "{{ route('inventory_entry.index') }}";
                    }, 1200);
                } else {
                    setTimeout(function () {
                        $('.product_detail_view_div').html('');
                        $('#skus_product_name').text('');
                        $('#skuEntryBody').empty()
                        $('#confirmSaveModal').modal('hide');
                        $('#entries_summary').html('');
                        $('#save_result').addClass('d-none').html('');
                    }, 1200);

                    toastr.success(response.message);
                }

            } else {
                $('#save_result')
                    .removeClass('d-none')
                    .html(`<div class="alert alert-danger">${response.message}</div>`);
            }

        }).fail(function (xhr) {

            let msg = xhr.responseJSON?.message || "Error inesperado";

            $('#save_result')
                .removeClass('d-none')
                .html(`<div class="alert alert-danger">${msg}</div>`);

        }).always(function () {
            $btn.prop('disabled', false)
                .html('<i class="fas fa-save mr-1"></i> Confirmar');
        });

    });

    function collectEntries() {
        let entries = [];
        let hasError = false;

        $('#skuEntryBody .sku-entry-row').each(function () {
            let skuId  = $(this).data('sku-id');
            let skuText  = $(this).data('sku');
            let lotNum = $(this).find('.lot-number-input').val().trim();
            let qty    = $(this).find('.quantity-input').val();

            if (!lotNum && !qty) return;

            if (!lotNum) {
                toastr.warning(`Lote requerido en SKU ${skuId}`);
                hasError = true; return false;
            }

            if (!qty || parseInt(qty) <= 0) {
                toastr.warning(`Cantidad inválida en SKU ${skuId}`);
                hasError = true; return false;
            }

            entries.push({
                product_sku_id: skuId,
                sku: skuText,
                quantity: parseInt(qty),
                lot_number: lotNum,
                manufacture_date: $(this).find('.manufacture-date-input').val() || null,
                expiration_date: $(this).find('.expiration-date-input').val() || null,
                unit_cost: $(this).find('.unit-cost-input').val() || null,
                warehouse_location: labelLocation,
                supplier: $(this).find('.supplier-input').val() || null,
                notes: $('#common_notes').val() || null,
            });
        });

        return { entries, hasError };
    }



    $(window).on('load pageshow', function () {
        $('#pre-loader').addClass('d-none');
    });
});
</script>
@endpush
