<script>
$(document).ready(function() {
    const $form = $('#transferForm');
    const $origin = $('#origin_location');
    const $dest = $('#destination_location');
    const $dispBy = $('#dispatched_by');
    const $recvBy = $('#received_by');
    const $submitBtn = $('#submitBtn');
    const $btnAddProducts = $('#btnAddProducts');
    const $destError = $('#destError');
    const lotsUrl = "{{ route('cost_centers.inventory.location-lots') }}";
    
    // Selection state
    let selectedSkus = {}; // key (skuId_lotId) -> { key, skuId, lotId, sku, productName... }
    let allProductsCache = { origin: [], dest: [] };
    let destStockMap = {};
    let lotCache = {};
    let currentTransferPayload = null;
    let confirmTimer = null;

    $('[data-toggle="tooltip"]').tooltip();

    function toInt(value) {
        const n = parseFloat(value);
        return Number.isFinite(n) ? Math.trunc(n) : 0;
    }

    function formatInt(value) {
        return toInt(value).toString();
    }

    // 0. Initial Load & Sync
    if ($origin.val()) loadLocationUsers($origin.val(), $dispBy);
    if ($dest.val()) loadLocationUsers($dest.val(), $recvBy);
    validateSelectionsAndLocations();
    syncLocations();

    $.get("{{ route('cost_centers.inventory.carriers') }}", function(data) {
        data.forEach(function(c) { $('#carrier_id').append(new Option(c.name, c.id)); });
    });

    // 1. Logistics Change Handlers
    $origin.on('change', function() {
        loadLocationUsers($(this).val(), $dispBy);
        validateSelectionsAndLocations();
        syncLocations();
        resetSelection(); 
    });

    $dest.on('change', function() {
        loadLocationUsers($(this).val(), $recvBy);
        validateSelectionsAndLocations();
        syncLocations();
        resetSelection(); 
    });

    function syncLocations() {
        const originVal = $origin.val();
        const destVal = $dest.val();

        $origin.find('option').prop('disabled', false).show();
        $dest.find('option').prop('disabled', false).show();

        if (originVal) $dest.find(`option[value="${originVal}"]`).prop('disabled', true).hide();
        if (destVal) $origin.find(`option[value="${destVal}"]`).prop('disabled', true).hide();

        if ($.fn.niceSelect) {
            $origin.niceSelect('update');
            $dest.niceSelect('update');
        }
    }

    function resetSelection() {
        selectedSkus = {};
        allProductsCache = { origin: [], dest: [] };
        destStockMap = {};
        lotCache = {};
        renderSelectedTable();
        updateModalStats();
    }

    function loadLocationUsers(locationVal, $selectEl) {
        if (!locationVal) {
            $selectEl.html('<option value="">{{ __('common.select') }}...</option>').prop('disabled', true);
            return;
        }
        $selectEl.html('<option value="">{{ __('costcenter::inventory.loading_users') }}</option>').prop('disabled', true);
        const endPoint = `{{ route('cost_centers.inventory.get-location-users', '') }}/${locationVal}`;
        $.get(endPoint, function(res) {
            if(res.success) {
                $selectEl.html('<option value="">{{ __('costcenter::inventory.unassigned') }}</option>');
                res.users.forEach(u => $selectEl.append(new Option(`${u.name} (${u.username})`, u.id)));
                $selectEl.prop('disabled', false);
            }
        });
    }

    function validateSelectionsAndLocations() {
        const originVal = $origin.val();
        const destVal = $dest.val();

        if (originVal && destVal && originVal === destVal) {
            $destError.text('{{ __('costcenter::inventory.dest_cannot_be_same_as_origin') }}');
            $btnAddProducts.prop('disabled', true);
            return;
        }
        $destError.text('');
        $btnAddProducts.prop('disabled', !originVal || !destVal);
        checkFormReady();
    }

    // 2. Product Selection Modal Logic
    $btnAddProducts.on('click', function() {
        $('#productSelectionModal').modal('show');
        $('#modalOriginName').text($origin.find('option:selected').text());
        $('#modalDestinationName').text($dest.find('option:selected').text());
        fetchAndRenderModalProducts();
    });

    function fetchAndRenderModalProducts() {
        const originVal = $origin.val();
        const destVal = $dest.val();
        if (!originVal || !destVal) return;

        $('#modalLoadingState').removeClass('d-none').addClass('d-flex');
        $('#modalEmptyState, #modalProductsList').addClass('d-none').removeClass('d-flex');

        const endpointOrigin = originVal === 'main' ? "{{ route('cost_centers.inventory.warehouse-skus') }}" : `{{ route('cost_centers.inventory.center-inventory', '') }}/${originVal.split('-')[1]}`;
        const endpointDest = destVal === 'main' ? "{{ route('cost_centers.inventory.warehouse-skus') }}" : `{{ route('cost_centers.inventory.center-inventory', '') }}/${destVal.split('-')[1]}`;

        $.when(
            $.ajax({ url: endpointOrigin, method: 'GET' }),
            $.ajax({ url: endpointDest, method: 'GET' })
        ).done(function(resOrigin, resDest) {
            $('#modalLoadingState').addClass('d-none').removeClass('d-flex');

            let productsOrigin = originVal === 'main' ? resOrigin[0].data : (resOrigin[0].products || []);
            let productsDest = destVal === 'main' ? resDest[0].data : (resDest[0].products || []);
            
            destStockMap = {};
            productsDest.forEach(p => p.skus.forEach(s => destStockMap[s.product_sku_id] = s.qty));
            
            allProductsCache.origin = productsOrigin;

            if (productsOrigin.length > 0) {
                $('#modalProductsList').removeClass('d-none');
                renderModalProducts(productsOrigin);
            } else {
                $('#modalEmptyState').removeClass('d-none').addClass('d-flex');
            }
        }).fail(function() {
            $('#modalLoadingState').addClass('d-none').removeClass('d-flex');
            $('#modalEmptyState').removeClass('d-none').addClass('d-flex');
        });
    }

    function fetchLotsForSku(skuId) {
        const key = `${$origin.val() || 'none'}|${skuId}`;
        if (lotCache[key]) return $.Deferred().resolve(lotCache[key]).promise();
        
        return $.get(lotsUrl, { location: $origin.val(), sku_id: skuId }).then(function(res) {
            lotCache[key] = res?.lots || [];
            return lotCache[key];
        });
    }

    function renderModalProducts(products) {
        let html = '';

        // ==========================================
        // OPTIMIZACIÓN: Pre-calcular mapa de totales solicitados por SKU
        // Complejidad pasa de O(N*M*S) a O(1) en la búsqueda
        // ==========================================
        let requestedBySku = {};
        Object.values(selectedSkus).forEach(s => {
            requestedBySku[s.skuId] = (requestedBySku[s.skuId] || 0) + toInt(s.qty);
        });

        products.forEach(prod => {
            let skusHtml = '';
            let totalOrigin = 0;
            let totalDest = 0;
            let totalRequested = 0;

            prod.skus.forEach(skuObj => {
                let destQty = toInt(destStockMap[skuObj.product_sku_id] || 0);
                totalOrigin += toInt(skuObj.qty);
                totalDest += destQty;
                
                // Búsqueda instantánea O(1) en el diccionario
                let skuRequested = toInt(requestedBySku[skuObj.product_sku_id] || 0);
                totalRequested += skuRequested;

                skusHtml += `
                <div class="modal-sku-row mb-3" data-sku-id="${skuObj.product_sku_id}">
                    <div class="d-flex align-items-center mb-2 justify-content-between border-bottom pb-2">
                        <span class="mr-2 f_w_500 text-black">${skuObj.sku}</span>
                        <div>
                            <span class="badge_6"><i class="ti-home mr-1"></i> {{ __('costcenter::inventory.origin') }}: <strong>${formatInt(skuObj.qty)}</strong></span>
                            <span class="badge_6"><i class="ti-location-pin mr-1"></i> {{ __('costcenter::inventory.destination') }}: <strong>${formatInt(destQty)}</strong></span>
                        </div>
                    </div>
                    <div class="modal-lot-list bg-light-soft rounded p-2" 
                        data-sku-id="${skuObj.product_sku_id}"
                        data-product-name="${prod.product_name}"
                        data-brand="${prod.brand || ''}"
                        data-thumbnail="${prod.thumbnail}"
                        data-sku-text="${skuObj.sku}"
                        data-dest-stock="${destQty}">
                        <div class="text-center text-muted small py-2"><i class="fas fa-spinner fa-spin"></i> Cargando lotes...</div>
                    </div>
                </div>`;
            });

            html += `
            <div class="modal-product-card mb-4 ${totalRequested > 0 ? 'has-selection' : 'is-collapsed'}" data-product-id="${prod.id}">
                <div class="modal-product-header">
                    <img src="${prod.thumbnail}" class="product-thumb mr-3 shadow-sm" style="width:45px;height:45px;border-radius:10px;">
                    <div class="flex-grow-1 pr-3">
                        <h6 class="mb-0 font-weight-bold text-dark">${prod.product_name}</h6>
                        <small class="text-muted">{{ __('product.brand') }}: ${prod.brand || '---'}</small>
                    </div>
                    
                    <div class="header-badges-container d-none d-md-flex align-items-start">
                        <span class="badge_5 px-2 py-1">Origen: ${formatInt(totalOrigin)}</span>
                        <span class="badge_1 px-2 py-1">Destino: ${formatInt(totalDest)}</span>
                        <span class="badge_3 px-2 py-1 badge-req ${totalRequested > 0 ? 'has-val' : ''}">
                            Req: <span class="prod-total-req">${formatInt(totalRequested)}</span>
                        </span>
                    </div>
                    <i class="ti-angle-up toggle-icon"></i>
                </div>
                <div class="modal-skus-container px-3 pb-2">
                    ${skusHtml}
                </div>
            </div>`;
        });

        $('#modalProductsList').html(html);

        // 3. Cargar lotes expuestos para cada SKU de forma dinámica
        $('.modal-lot-list').each(function() {
            let $list = $(this);
            let skuId = $list.data('sku-id');
            let productName = $list.data('product-name');
            let brand = $list.data('brand');
            let thumbnail = $list.data('thumbnail');
            let skuName = $list.data('sku-text');
            let destStock = $list.data('dest-stock');

            fetchLotsForSku(skuId).then(function(lots) {
                if (!lots || lots.length === 0) {
                    $list.html('<div class="text-center text-danger small py-2">Sin lotes disponibles con stock</div>');
                    return;
                }

                // NUEVO: ORDENAMIENTO FEFO (Vencimiento más próximo primero)
                lots.sort(function(a, b) {
                    if (!a.expiration_date && !b.expiration_date) return 0;
                    if (!a.expiration_date) return 1; 
                    if (!b.expiration_date) return -1; 
                    return new Date(a.expiration_date) - new Date(b.expiration_date);
                });

                let lotsHtml = '';
                lots.forEach(lot => {
                    let key = `${skuId}_${lot.lot_id}`;
                    let existingQty = selectedSkus[key] ? toInt(selectedSkus[key].qty) : 0;
                    
                    lotsHtml += `
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom lot-exposed-row" style="border-color: #e2e8f0 !important;">
                        <div class="lot-info flex-grow-1">
                            <span class="badge bg-white border text-dark font-weight-bold" style="font-size: 11px;"><i class="ti-tag text-muted mr-1"></i> Lote: ${lot.lot_number}</span>
                            <small class="text-muted ml-2 d-inline-block" title="Vencimiento"><i class="ti-calendar"></i> ${lot.expiration_date || 'N/A'}</small>
                        </div>
                        <div class="lot-stock text-center px-3">
                            <small class="text-muted d-block" style="font-size: 9px; text-transform: uppercase;">Disponible</small>
                            <span class="font-weight-bold" style="color: var(--toolkit_corporative-orange-color);">${formatInt(lot.available_qty)}</span>
                        </div>
                        <div class="lot-input" style="width: 100px;">
                            <input type="number" class="form-control form-control-sm text-center modal-qty-input font-weight-bold" 
                                style="border-radius: 8px;"
                                data-key="${key}"
                                data-sku-id="${skuId}"
                                data-lot-id="${lot.lot_id}"
                                data-lot-number="${lot.lot_number}"
                                data-exp-date="${lot.expiration_date || ''}"
                                data-product-name="${productName}"
                                data-brand="${brand}"
                                data-thumbnail="${thumbnail}"
                                data-sku-name="${skuName}"
                                data-origin-stock="${formatInt(lot.available_qty)}"
                                data-dest-stock="${destStock}"
                                min="0" max="${formatInt(lot.available_qty)}" step="1" inputmode="numeric" value="${existingQty}">
                        </div>
                    </div>`;
                });
                $list.html(lotsHtml);
                $list.find('.lot-exposed-row:last').removeClass('border-bottom');
                
            }, function(error) {
                // MANEJO DE ERROR SI FALLA EL AJAX
                $list.html(`
                    <div class="text-center text-danger small py-3" style="background-color: #fef2f2; border-radius: 8px;">
                        <i class="ti-alert mr-1"></i> Error al cargar los lotes. 
                        <span class="text-muted d-block mt-1">Verifique su conexión a internet e intente cerrar y abrir este producto de nuevo.</span>
                    </div>
                `);
            });
        });
    }

    // 4. Múltiples Inputs Dinámicos (Lista Expuesta)
    function sanitizeIntInput($input) {
        let raw = String($input.val() ?? '');
        let cleaned = raw.replace(/[^\d]/g, '');
        if (cleaned !== raw) {
            $input.val(cleaned);
        }
        return toInt(cleaned);
    }

    $('#modalProductsList').on('keydown', '.modal-qty-input', function(e) {
        if (e.key === '.' || e.key === '-' || e.key === ',') {
            e.preventDefault();
        }
    });

    $('#selectedItemsBody').on('keydown', '.main-qty-input', function(e) {
        if (e.key === '.' || e.key === '-' || e.key === ',') {
            e.preventDefault();
        }
    });
    $('#modalProductsList').on('change input', '.modal-qty-input', function() {
        let key = $(this).data('key');
        let qty = sanitizeIntInput($(this));
        let max = toInt($(this).attr('max'));
        
        if (qty > max) { qty = max; $(this).val(max); }
        if (qty < 0) { qty = 0; $(this).val(0); }

        if (qty > 0) {
            selectedSkus[key] = {
                key: key,
                skuId: $(this).data('sku-id'),
                lotId: $(this).data('lot-id'),
                sku: $(this).data('sku-name'),
                productName: $(this).data('product-name'),
                brand: $(this).data('brand'),
                thumbnail: $(this).data('thumbnail'),
                lotNumber: $(this).data('lot-number'),
                expirationDate: $(this).data('exp-date'),
                originStock: $(this).data('origin-stock'),
                destStock: $(this).data('dest-stock'),
                qty: qty
            };
        } else {
            delete selectedSkus[key];
        }

        // Actualizar el Badge del total en la cabecera del producto
        let $card = $(this).closest('.modal-product-card');
        let totalProdReq = 0;
        $card.find('.modal-qty-input').each(function() {
            totalProdReq += toInt($(this).val());
        });
        
        let $reqBadge = $card.find('.badge-req');
        $reqBadge.find('.prod-total-req').text(totalProdReq);
        $reqBadge.toggleClass('has-val', totalProdReq > 0);
        $card.toggleClass('has-selection', totalProdReq > 0);

        updateModalStats();
        renderSelectedTable();
    });

    function updateModalStats() {
        let count = Object.keys(selectedSkus).length;
        let units = Object.values(selectedSkus).reduce((acc, s) => acc + toInt(s.qty), 0);
        $('#modalSelectedCount').text(count);
        $('#modalTotalUnits').text(formatInt(units));
    }

    // 5. Main View Render
    function renderSelectedTable() {
        const items = Object.values(selectedSkus);
        const $table = $('#selectedItemsTable');
        const $body = $('#selectedItemsBody');
        const $empty = $('#emptySelectionState');

        if (items.length === 0) {
            $table.addClass('d-none');
            $empty.removeClass('d-none').addClass('d-flex');
        } else {
            $table.removeClass('d-none');
            $empty.addClass('d-none').removeClass('d-flex');
            
            let html = '';
            items.forEach(item => {
                html += `
                <tr class="selected-item-row" data-key="${item.key}">
                    <td>${item.productName}</td>
                    <td><img src="${item.thumbnail}" width="35" height="35" class="rounded shadow-sm"></td>
                    <td class="small text-muted font-weight-bold">${item.sku}</td>
                    <td class="small text-muted">${item.brand}</td>
                    <td class="small text-muted"><span class="badge bg-light border text-dark">${item.lotNumber || 'N/A'}</span></td>
                    <td class="small text-muted">${item.expirationDate || 'N/A'}</td>
                    <td class="text-center font-weight-bold" style="color: var(--toolkit_corporative-orange-color)">${formatInt(item.originStock)}</td>
                    <td class="text-center font-weight-bold" style="color: var(--toolkit_corporative-green-color)">${formatInt(item.destStock)}</td>
                    <td class="text-center">
                        <input type="number" class="qty-pill-input main-qty-input"
                               data-key="${item.key}" min="1" max="${formatInt(item.originStock)}" step="1" inputmode="numeric" value="${formatInt(item.qty)}">
                    </td>
                    <td class="text-right">
                        <button type="button" class="btn-remove-item text-danger bg-transparent border-0" title="Eliminar">
                            <i class="ti-trash fs-18"></i>
                        </button>
                    </td>
                </tr>`;
            });
            $body.html(html);
        }
        updateTotals();
    }

    // Main Selection Table Events
    $('#selectedItemsBody').on('change input', '.main-qty-input', function() {
        let key = $(this).data('key');
        let qty = sanitizeIntInput($(this));
        let max = toInt($(this).attr('max'));
        if (qty > max) { qty = max; $(this).val(max); }
        if (qty < 1) { qty = 1; $(this).val(1); }
        
        if (selectedSkus[key]) {
            selectedSkus[key].qty = qty;
            $(`.modal-qty-input[data-key="${key}"]`).val(qty);
            updateTotals();
            
            // Actualizar el total de la card en la modal
            let $card = $(`.modal-qty-input[data-key="${key}"]`).closest('.modal-product-card');
            let totalProdReq = 0;
            $card.find('.modal-qty-input').each(function() { totalProdReq += toInt($(this).val()); });
            $card.find('.prod-total-req').text(formatInt(totalProdReq));
        }
    });

    $('#selectedItemsBody').on('click', '.btn-remove-item', function() {
        let key = $(this).closest('tr').data('key');
        delete selectedSkus[key];
        $(`.modal-qty-input[data-key="${key}"]`).val(0).trigger('change');
        renderSelectedTable();
    });

    function updateTotals() {
        let items = Object.keys(selectedSkus).length;
        let units = Object.values(selectedSkus).reduce((acc, s) => acc + toInt(s.qty), 0);
        
        $('#footerItemsCount').text(items);
        $('#footerTotalUnits').text(formatInt(units));
        $('#mobileItemsBadge').text(items).toggleClass('d-none', items === 0);

        checkFormReady();
    }

    function checkFormReady() {
        let totalUnits = toInt($('#footerTotalUnits').text());
        let validLocations = $origin.val() && $dest.val() && ($origin.val() !== $dest.val());
        $submitBtn.prop('disabled', !(totalUnits > 0 && validLocations));
    }

    // UI Interactions
    $('#modalProductSearch, #modalProductSearchMobile').on('keyup input', function() {
        let q = $(this).val().toLowerCase();
        $('#modalProductSearch, #modalProductSearchMobile').val($(this).val());
        $('.modal-product-card').each(function() {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    $('#modalProductsList').on('click', '.modal-product-header', function() {
        $(this).closest('.modal-product-card').toggleClass('is-collapsed');
    });

    $('#modalProductSearchMobile').on('focus', () => $('.mobile-tabs-nav').addClass('search-active'))
                                    .on('blur', () => $('.mobile-tabs-nav').removeClass('search-active'));

    // 6. Submit Handling
    $form.on('submit', function(e) {
        e.preventDefault();
        
        let itemsPayload = Object.values(selectedSkus).map(s => ({
            id: s.skuId,
            lot_id: s.lotId,
            qty: toInt(s.qty)
        }));
        if(itemsPayload.length === 0) return;

        let modalTableHtml = '';
        Object.values(selectedSkus).forEach(s => {
            modalTableHtml += `
                <tr>
                    <td>${s.sku}</td>
                    <td>${s.productName}</td>
                    <td>${s.lotNumber || 'N/A'}</td>
                    <td>${s.expirationDate || 'N/A'}</td>
                    <td class="font-weight-bold text-center">${formatInt(s.qty)}</td>
                </tr>`;
        });
        $('#summaryProductsTable').html(modalTableHtml);
        $('#summaryTotalQty').text(formatInt($('#footerTotalUnits').text()));
        $('#summaryOrigin').text($origin.find('option:selected').text());
        $('#summaryDestination').text($dest.find('option:selected').text());

        currentTransferPayload = {
            _token: $('input[name=_token]').val(),
            origin_id: $origin.val() === 'main' ? 'main' : $origin.val().split('-')[1],
            destination_id: $dest.val() === 'main' ? 'main' : $dest.val().split('-')[1],
            dispatched_by: $dispBy.val(),
            received_by: $recvBy.val(),
            movement_type_id: $('#movement_type_id').val(),
            shipping_guide: $('#shipping_guide').val(),
            carrier_id: $('#carrier_id').val(),
            guide_date: $('#guide_date').val(),
            reason: $('#reason').val(),
            items: itemsPayload
        };

        $('#summaryMovementType').text($('#movement_type_id option:selected').text() || '---');
        $('#summaryShippingGuide').text($('#shipping_guide').val() || '---');
        $('#summaryCarrier').text($('#carrier_id option:selected').text() && $('#carrier_id').val() ? $('#carrier_id option:selected').text() : '---');
        $('#summaryGuideDate').text($('#guide_date').val() || '---');
        $('#summaryReason').text($('#reason').val() || '---');

        $('#confirmTransferModal').modal('show');
        
        let countdown = 10;
        $('#confirmCountdown').text(countdown);
        $('#confirmStepIntro').removeClass('d-none');
        $('#confirmStepSummary').addClass('d-none');
        $('#confirmTransferBtn').html('<i class="ti-eye mr-2"></i> {{ __('costcenter::inventory.view_summary') }}').attr('data-mode', 'view');

        clearInterval(confirmTimer);
        confirmTimer = setInterval(function() {
            countdown--;
            $('#confirmCountdown').text(countdown);
            if (countdown <= 0) { clearInterval(confirmTimer); showConfirmSummary(); }
        }, 1000);
    });

    function showConfirmSummary() {
        clearInterval(confirmTimer);
        $('#confirmStepIntro').addClass('d-none');
        $('#confirmStepSummary').removeClass('d-none');
        $('#confirmTransferBtn').html('<i class="ti-check mr-2"></i> {{ __('costcenter::inventory.confirm_transfer') ?? 'Confirmar Traslado' }}').attr('data-mode', 'confirm');
    }

    $('#confirmTransferBtn').on('click', function() {
        if ($(this).attr('data-mode') === 'view') { showConfirmSummary(); return; }

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Procesando...');
        $('#confirmTransferModal').modal('hide');
        $('#pre-loader').removeClass('d-none');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: currentTransferPayload,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    let detailUrl = "{{ route('cost_centers.inventory.transaction-detail', ['id' => ':id']) }}?countdown=1".replace(':id', res.transfer_id);
                    $.get(detailUrl, function(html) {
                        $('#pre-loader').addClass('d-none');
                        $('#detailModalContainer').html(html);
                        $('#transferDetailModal').modal('show');
                    });
                } else {
                    toastr.error(res.message);
                    $('#pre-loader').addClass('d-none');
                    $('#confirmTransferBtn').prop('disabled', false).html('<i class="ti-check mr-2"></i> {{ __('costcenter::inventory.confirm_transfer') }}');
                }
            },
            error: function() {
                toastr.error('{{ __('costcenter::inventory.error_processing_request') }}');
                $('#pre-loader').addClass('d-none');
                $('#confirmTransferBtn').prop('disabled', false).html('<i class="ti-check mr-2"></i> {{ __('costcenter::inventory.confirm_transfer') }}');
            }
        });
    });
});
</script>
