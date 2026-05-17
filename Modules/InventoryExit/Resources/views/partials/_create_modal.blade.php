<dialog id="createExitModal" class="modal fade" tabindex="-1" aria-labelledby="createExitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="createExitModalLabel">
                    {{ __('inventoryexit::messages.new_request') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>

            <form id="createExitForm" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">

                    {{-- ============================================================ --}}
                    {{-- SECCIÓN 1 — Responsable --}}
                    {{-- ============================================================ --}}
                    <div class="form-card">
                        <h3 class="form-section-title">
                            {{ __('inventoryexit::messages.section_responsible') }}
                        </h3>
                        <div class="row">
                            <div class="col-lg-6 col-12">
                                <label class="primary_input_label" for="">{{ __('inventoryexit::messages.responsible_user') }}</label>
                                <input type="text" class="primary_input_field" value="{{ auth()->user()->name }} ({{ auth()->user()->role?->name ?? '—' }})" readonly />

                            </div>
                            <div class="col-lg-6 col-12">
                                <label class="primary_input_label" for="">{{ __('inventoryexit::messages.request_date') }}</label>
                                <input type="text" class="primary_input_field" value="{{ now()->format('d/m/Y H:i') }}" readonly />

                            </div>
                        </div>
                        <div class="row mt-15">
                            <div class="col-lg-6 col-12">
                                <label class="primary_input_label" for="exit_reason_id">
                                    {{ __('inventoryexit::messages.exit_reason') }} <span class="text-danger">*</span>
                                </label>
                                <select id="exit_reason_id" name="exit_reason_id" class="primary_input_select" required>
                                    <option value="">{{ __('inventoryexit::messages.exit_reason_placeholder') }}</option>
                                    {{-- Cargado por AJAX al abrir --}}
                                </select>
                            </div>
                            <div class="col-lg-6 col-12">
                                <label class="primary_input_label" for="exit_date">
                                    {{ __('inventoryexit::messages.filter_date_from') }} <span class="text-danger">*</span>
                                </label>
                                <input type="date" id="exit_date" name="exit_date"
                                    class="primary_input_field"
                                    value="{{ now()->format('Y-m-d') }}"
                                    required>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================================ --}}
                    {{-- SECCIÓN 2 — Centro de costo + productos --}}
                    {{-- ============================================================ --}}
                    <div class="form-card">
                        <h3 class="form-section-title">
                            {{ __('inventoryexit::messages.section_products') }}
                        </h3>
                        <div class="row">
                            <div class="col-lg-6 col-sm-12">
                                <label class="primary_input_label" for="exit_cost_center">
                                    {{ __('inventoryexit::messages.cost_center') }} <span class="text-danger">*</span>
                                </label>
                                <select id="exit_cost_center" name="location" class="primary_input_select" required>
                                    @if($isGlobal)
                                        <option value="">— Seleccionar —</option>
                                        <option value="main">{{ __('costcenter::main_warehouse.name') }}</option>
                                        @foreach($costCenters as $cc)
                                            <option value="center-{{ $cc->id }}">{{ $cc->name }} ({{ $cc->code }})</option>
                                        @endforeach
                                    @else
                                        @foreach($costCenters as $cc)
                                            <option value="center-{{ $cc->id }}" selected>{{ $cc->name }} ({{ $cc->code }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-lg-6 col-sm-12">
                                <label class="primary_input_label" for="productSkuSelect">
                                    {{ __('inventoryexit::messages.search_product') }}
                                </label>
                                <select id="productSkuSelect"
                                        class="nice-select-ajax wide"
                                        disabled>
                                    <option value="">— {{  __('inventoryexit::messages.search_product') }} —</option>
                                </select>
                            </div>
                        </div>

                        <!-- Loader para productos -->
                        <div id="loaderExitItems" style="display:none; padding: 40px 0px;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem;">
                                    <span class="sr-only">{{ __('common.loading') }}</span>
                                </div>
                                <span class="ml-10">{{ __('common.loading_products') }}</span>
                            </div>
                        </div>
                        {{-- Grilla de productos seleccionados --}}
                        <div class="table-responsive ign-scrollbar mt-5" id="exitItemsTable" style="display:none">
                            <table class="table table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('inventoryexit::messages.col_sku') }}</th>
                                        <th>{{ __('inventoryexit::messages.col_lot') }}</th>
                                        <th>{{ __('inventoryexit::messages.col_lot_expiry') }}</th>
                                        <th>{{ __('inventoryexit::messages.col_stock') }}</th>
                                        <th>{{ __('inventoryexit::messages.col_qty_requested') }}</th>
                                        <th>{{ __('inventoryexit::messages.col_final_stock') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="exitItemsBody">
                                    {{-- Filas agregadas por JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ============================================================ --}}
                    {{-- SECCIÓN 3 — Observación y documentos --}}
                    {{-- ============================================================ --}}
                    <div class="form-card">
                        <h3 class="form-section-title">
                             {{ __('inventoryexit::messages.section_observation') }}
                        </h3>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="primary_input_label" for="exit_observation">
                                    {{ __('inventoryexit::messages.observation_label') }} <span class="text-danger">*</span>
                                </label>
                                <textarea id="exit_observation" name="observation" class="primary_textarea"
                                    rows="3"
                                    placeholder="{{ __('inventoryexit::messages.observation_placeholder') }}"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="row mt-15">
                            <div class="col-md-12">
                                <x-backEnd.file name="documents[]" id="exit_documents" accept=".pdf,.jpg,.jpeg,.png,.webp" :multiple="true" :field="trans('inventoryexit::messages.documents_label')" />

                            </div>
                        </div>
                    </div>

                </div>{{-- /modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">
                        {{ __('inventoryexit::messages.btn_cancel') }}
                    </button>
                    <button type="submit" class="btn-toolkit btn-primary">
                        <i class="fa fa-check"></i> {{ __('inventoryexit::messages.btn_confirm') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</dialog>

@push('scripts')
<script src="{{ asset('public/backend/js/file-input.js') }}" defer></script>
<script>
$(document).ready(function () {

    const reasonsUrl  = "{{ route('inventory_exit.reasons') }}";
    const lotsUrl     = "{{ route('inventory_exit.lots') }}";

    // ---------------------------------------------------------------
    // Cargar motivos al abrir modal
    // ---------------------------------------------------------------
    $('#createExitModal').on('show.bs.modal', function () {
        // Recargar motivos (puede haber nuevos creados en sesión anterior)
        $.get(reasonsUrl, function (reasons) {
            const sel = $('#exit_reason_id');
            const current = sel.val();
            sel.find('option:not(:first)').remove();
            reasons.forEach(r => {
                sel.append(`<option value="${r.id}" ${r.id == current ? 'selected' : ''}>${r.name}</option>`);
            });
        });
    });

    // ---------------------------------------------------------------
    // Inicializar nice-select-ajax del SKU selector
    // ---------------------------------------------------------------
    const skusByLocationUrl = "{{ route('inventory_exit.skus-by-location') }}";

    $('#productSkuSelect').niceSelect();

    // Al abrir el modal, deshabilitar el select de SKU
    $('#createExitModal').on('show.bs.modal', function () {
        resetSkuSelect();
    });

    $('#createExitModal').on('hidden.bs.modal', function () {
        clearItemsTable();
    });

    function resetSkuSelect() {
        $('#productSkuSelect').val('').find('option:not(:first)').remove();
        $('#productSkuSelect').prop('disabled', true).niceSelect('update');
    }

    function clearItemsTable() {
        $('#exitItemsBody').empty();
        $('#exitItemsTable').css('display', 'none');
        $('#lotPickerBox').remove();
    }

    function loadSkuOptions(location) {
        const $sel      = $('#productSkuSelect');
        const $niceWrap = $sel.next('.nice-select');

        $sel.find('option:not(:first)').remove();
        $sel.prop('disabled', true).niceSelect('update');
        $niceWrap.addClass('loading');

        $.get(skusByLocationUrl, { location }, function (items) {
            $niceWrap.removeClass('loading');

            if (!items.length) {
                toastr.warning('No hay productos con stock en esta ubicación');
                return;
            }

            items.forEach(item => {
                $sel.append(`<option value="${item.id}">${item.name}</option>`);
            });

            $sel.prop('disabled', false).niceSelect('update');
        }).fail(function () {
            $niceWrap.removeClass('loading');
            toastr.error('Error al cargar productos');
        }).always(function() {
            $('#pre-loader').addClass('d-none');
        });


    }

    // Al cambiar el CC, cargar SKUs disponibles en esa ubicación
    $('#exit_cost_center').on('change', function () {
        $('#pre-loader').removeClass('d-none');
        clearItemsTable();
        const location = $(this).val();
        if (!location) {
            resetSkuSelect();
            return;
        }
        loadSkuOptions(location);
    });

    // Al seleccionar un SKU cargar sus lotes
    $('#productSkuSelect').on('change', function () {
        const skuId = $(this).val();
        if (!skuId) { return; }
        clearItemsTable();
        const skuName = $(this).find('option:selected').text();
        loadLotSelector({ sku_id: skuId, sku: skuName, product_name: skuName });
    });

    // ---------------------------------------------------------------
    // Cargar lotes para el SKU seleccionado
    // ---------------------------------------------------------------
    function loadLotSelector(sku) {
        const ccId         = $('#exit_cost_center').val();
        const locationType = getLocationType(ccId);
        const locationId   = getLocationId(ccId);

        // Mostrar loader
        $('#loaderExitItems').show();

        $.get(lotsUrl, { location_type: locationType, location_id: locationId, sku_id: sku.sku_id }, function (lots) {
            clearItemsTable();

            if (!lots.length) {
                toastr.warning('No hay lotes con stock para este producto en la ubicación seleccionada');
                return;
            }

            // Si solo hay un lote, agregar directo
            if (lots.length === 1) {
                addItemRow(sku, lots[0]);
            } else {
                // Mostrar mini-modal de selección de lote (inline)
                showLotPicker(sku, lots);
            }
        })
        .fail(function () {
            toastr.error('Error al cargar lotes');
        })
        .always(function () {
            // Ocultar loader
            $('#loaderExitItems').hide();
        });
    }

    function showLotPicker(sku, lots) {
        // Remover picker anterior
        $('#lotPickerBox').remove();

        let optionsHtml = lots.map(l =>
            `<div class="lot-pick-item p-10 border-bottom" style="cursor:pointer"
                data-lot='${JSON.stringify(l)}'>
                <strong>${l.lot_number}</strong>
                <span class="text-muted ml-10">Venc: ${l.expiration_date ?? 'N/A'}</span>
                <span class="badge_1 ml-10">Stock: ${l.qty}</span>
            </div>`
        ).join('');

        const picker = $(`
            <div id="lotPickerBox" class="lot-picker-box">
                <div class="p-10 bg-light border-bottom"><strong>Seleccionar lote — ${sku.product_name}</strong></div>
                ${optionsHtml}
            </div>
        `);

        $('#exitItemsTable').after(picker);

        $('#exitItemsTable').css('display', '');

        $(document).off('click.lotpicker').on('click.lotpicker', '.lot-pick-item', function () {
            const lot = $(this).data('lot');
            $('#lotPickerBox').remove();
            $(document).off('click.lotpicker');
            addItemRow(sku, lot);
        });
    }

    // ---------------------------------------------------------------
    // Agregar fila a la grilla de productos
    // ---------------------------------------------------------------
    function addItemRow(sku, lot) {
        // Evitar duplicado sku+lote
        if ($(`#exitItemsBody tr[data-sku-id="${sku.sku_id}"][data-lot-id="${lot.lot_id}"]`).length) {
            toastr.warning('Este producto con ese lote ya fue agregado');
            return;
        }

        const expiry = lot.expiration_date ?? '—';
        const row = `
            <tr data-sku-id="${sku.sku_id}" data-lot-id="${lot.lot_id}" data-stock="${lot.qty}" style="text-align: center;">
                <td><span class="badge_1">${sku.sku ?? '—'}</span></td>
                <td>${lot.lot_number}</td>
                <td>${expiry}</td>
                <td class="stock-cell">${lot.qty}</td>
                <td>
                    <input type="number" class="primary_input_field qty-input"
                           min="1" max="${lot.qty}" step="1" value="1"
                           style="width:90px">
                </td>
                <td class="final-stock-cell">${(lot.qty - 1).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn-toolkit btn-danger btn-sm remove-exit-item">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;

        $('#exitItemsBody').append(row);
        $('#exitItemsTable').css('display', '');
    }


    // Prevenir ingreso de '-' (números negativos) en cantidad
    $(document).on('keydown', '.qty-input', function (e) {
        if (e.key === '-' || e.keyCode === 189) {
            e.preventDefault();
        }
    });
    // Prevenir pegado de valores negativos
    $(document).on('paste', '.qty-input', function (e) {
        const paste = (e.originalEvent || e).clipboardData.getData('text');
        if (paste.includes('-')) {
            e.preventDefault();
        }
    });

    // Actualizar stock final al cambiar cantidad
    $(document).on('input', '.qty-input', function () {
        const row     = $(this).closest('tr');
        const stock   = parseFloat(row.data('stock'));
        const qty     = parseFloat($(this).val()) || 0;
        const final   = stock - qty;

        row.find('.final-stock-cell').text(final.toFixed(2));
        if (qty > stock) {
            $(this).addClass('border-danger');
        } else {
            $(this).removeClass('border-danger');
        }
    });

    // Eliminar fila
    $(document).on('click', '.remove-exit-item', function () {
        $(this).closest('tr').remove();
        if ($('#exitItemsBody tr[data-sku-id]').length === 0) {
            $('#exitItemsTable').css('display', 'none');
        }
    });

    // ---------------------------------------------------------------
    // Helper: determinar location_type y location_id del valor del selector
    // ---------------------------------------------------------------
    function getLocationType(val) {
        return val === 'main' ? 'main' : 'cost_center';
    }

    function getLocationId(val) {
        return val === 'main' ? null : parseInt(val.replace('center-', ''));
    }

});
</script>
@endpush
