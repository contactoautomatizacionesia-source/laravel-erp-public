<div class="modal fade admin-query" id="centerProductDetail">
    <div class="modal-dialog modal_1000px modal-dialog-centered modal-lg">
        <div class="modal-content tk-modal-content">
            
            <div class="modal-header">
                <h4 class="tk-modal-title">{{ $product->product_name }} - {{ __('product.details') }}</h4>
                <button type="button" class="tk-btn-close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body ">
                <div class="row">
                    {{-- COLUMNA IZQUIERDA: IMÁGENES --}}
                    <div class="col-lg-5 ">
                        <div class="form-card">
                            <img class="mx-auto rounded" id="tk-main-image-{{ $product->id }}" src="{{ showImage($product->thumbnail_image_source) }}" alt="{{ $product->product_name }}">
                        </div>
                        
                        @if (count($product->gallary_images) > 0)
                        <div class="col-12 mb-4 mt-4">
                            <div class="tk-gallery-grid">
                                @foreach ($product->gallary_images as $key => $gallary_image)
                                <div class="tk-gallery-item">
                                    <img src="{{showImage($gallary_image->images_source)}}" alt="{{$product->product_name}}" data-large-src="{{ showImage($gallary_image->images_source) }}" style="cursor: pointer;">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- COLUMNA DERECHA: INFORMACIÓN TÉCNICA --}}
                    <div class="col-lg-7">
                        
                        <div class="form-card">
                            <h3 class="">{{__('product.general_information')}}</h3>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.product_name')}}: </span>
                                <span class="tk-detail-value">{{ $product->product_name }}</span>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('common.status')}}: </span>
                                <span class="tk-detail-value">
                                    @if($product->status == 1)
                                        <span class="badge_1">{{ __('common.active') }}</span>
                                    @else
                                        <span class="badge_2">{{ __('common.inactive') }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.brand')}}: </span>
                                <span class="tk-detail-value">{{ @$product->brand->name ?? 'N/A' }}</span>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.category')}}:</span>
                                <div class="tk-detail-value">
                                    @foreach(@$product->categories as $category)
                                        <span class="tk-badge">{{@$category->name}}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.unit')}}: </span>
                                <span class="tk-detail-value">{{ @$product->unit_type->name ?? 'N/A' }}</span>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.barcode_type')}}: </span>
                                <span class="tk-detail-value">{{ @$product->skus->first()->barcode_type ?? 'N/A' }}</span>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('common.points')}}: </span>
                                <span class="badge_5">{{ @$product->club_point ?? 'N/A' }}</span>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.minimum_order_qty')}}: </span>
                                <span class="tk-detail-value">{{ $product->minimum_order_qty }}</span>
                            </div>
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.max_order_qty')}}: </span>
                                <span class="tk-detail-value">{{ $product->max_order_qty }}</span>
                            </div>
                            @if(count($product->tags) > 0)
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.tags')}}: </span>
                                <span class="tk-detail-value">
                                    @foreach ($product->tags as $tag)
                                        <span class="badge_6">{{ $tag->name }}</span>
                                    @endforeach
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FILA: INVENTARIO FILTRADO POR CENTRO DE COSTO --}}
                <div class="row ">
                    <div class="col-12">
                        <div class="form-card dataTables_wrapper">
                            <h3 class="">{{ __('product.stock_and_variants') }} ({{ __('costcenter::inventory.this_center') }})</h3>
                            <x-admin.table-container>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ __('product.variation') }}</th>
                                                <th>{{ __('product.sku') }}</th>
                                                <th>{{__('product.selling_price')}}</th>
                                                <th class="text-center">{{ __('product.stock') }}</th>
                                                @if ($product->is_physical != 1 && $product->product_type == 2)
                                                    <th class="text-center">Archivo</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Lógica estricta de Centro de Costo: Solo muestra si existe en $centerInventories --}}
                                            @foreach ($product->skus as $sku)
                                                @if(isset($centerInventories[$sku->id]))
                                                <tr>
                                                    <td>
                                                        @foreach ($sku->product_variations as $key => $variation)
                                                        <strong>{{ @$variation->attribute->name }}:</strong>
                                                        {{ $variation->attribute_value->color ? @$variation->attribute_value->color->name : @$variation->attribute_value->value }} <br>
                                                        @endforeach
                                                    </td>
                                                    <td><span class="tk-badge" style="background-color: var(--toolkit_secondary-gray-color);">{{ $sku->sku }}</span></td>
                                                    <td>
                                                        <span style="color: var(--toolkit_corporative-green-color); font-weight: bold;">
                                                            {{ single_price($sku->selling_price) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <span class="font-weight-bold" style="color: var(--toolkit_corporative-orange-color); font-size: 16px;">
                                                            {{ getNumberTranslate((float) $centerInventories[$sku->id]->qty) }}
                                                            @if(@$product->unit_type)
                                                                <span class="small text-muted font-weight-normal">({{ $product->unit_type->name }})</span>
                                                            @endif
                                                        </span>
                                                        @if($centerInventories[$sku->id]->qty > 0)
                                                        <br>
                                                        <button type="button"
                                                            class="btn-toolkit btn-secondary-outline mt-1 btn-toggle-lots-cc"
                                                            data-target="#lots-cc-{{ $sku->id }}"
                                                            data-sku-id="{{ $sku->id }}"
                                                            data-center-id="{{ $centerId }}"
                                                            style="font-size:11px; padding: 2px 8px;">
                                                            <i class="ti-package"></i> {{ __('product.lot_stock_table_lot') }}
                                                        </button>
                                                        @endif
                                                    </td>
                                                    @if ($product->is_physical != 1 && $product->product_type == 2 && @$sku->digital_file->file_source != null)
                                                    <td class="text-center align-middle">
                                                        <a href="{{ asset(asset_path(@$sku->digital_file->file_source)) }}" style="color: var(--toolkit_corporative-orange-color);" target="_blank">
                                                            <i class="ti-download"></i> {{ __('product.click_on_it') }}
                                                        </a>
                                                    </td>
                                                    @endif
                                                </tr>
                                                {{-- Fila desplegable de lotes --}}
                                                <tr id="lots-cc-{{ $sku->id }}" class="d-none">
                                                    <td colspan="6" class="p-0">
                                                        <div class="lot-inline-table px-3 pb-3">
                                                            <table class="table table-sm mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>{{ __('product.lot_stock_table_lot') }}</th>
                                                                        <th>{{ __('product.lot_stock_table_mfg') }}</th>
                                                                        <th>{{ __('product.lot_stock_table_exp') }}</th>
                                                                        <th>{{ __('product.lot_stock_table_status') }}</th>
                                                                        <th class="text-right">{{ __('product.lot_stock_table_qty') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="lot-tbody"></tbody>
                                                            </table>
                                                            <div class="lot-empty text-muted small mt-1 d-none">{{ __('costcenter::inventory.lot_stock_empty') }}</div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </x-admin.table-container>
                        </div>
                    </div>
                </div>

                {{-- FILA: DESCRIPCIÓN --}}
                @if(!empty($product->description))
                <div class="row">
                    <div class="col-12">
                        <div class="form-card">
                            <h3 class="">{{__('common.description')}}</h3>
                            <div class="text-black">
                                {!! $product->description !!}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toolkit btn-secondary-outline" data-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Cambio de imagen principal al click en galería
    document.addEventListener('click', function (event) {
        var thumb = event.target.closest('.tk-gallery-item img');
        if (!thumb) return;

        var modal = thumb.closest('#centerProductDetail');
        if (!modal) return;

        var mainImage = modal.querySelector('#tk-main-image-{{ $product->id }}');
        if (!mainImage) return;

        var newSrc = thumb.getAttribute('data-large-src') || thumb.getAttribute('src');
        if (newSrc) mainImage.setAttribute('src', newSrc);
    });

    // Desplegable de lotes por SKU — centro de costo
    $(document).on('click', '#centerProductDetail .btn-toggle-lots-cc', function () {
        var skuId    = $(this).data('sku-id');
        var centerId = $(this).data('center-id');
        var target   = $($(this).data('target'));
        var tbody    = target.find('.lot-tbody');
        var empty    = target.find('.lot-empty');

        if (!target.hasClass('d-none')) {
            target.addClass('d-none');
            return;
        }

        if (tbody.data('loaded')) {
            target.removeClass('d-none');
            return;
        }

        target.removeClass('d-none');
        tbody.html('<tr><td colspan="5" class="text-center text-muted small">{{ __("product.lot_stock_loading") }}</td></tr>');

        $.get('{{ route("cost_centers.inventory.location-lots") }}', { location: 'center-' + centerId, sku_id: skuId }, function (resp) {
            var rows = '';
            if (resp && resp.lots && resp.lots.length) {
                resp.lots.forEach(function (item) {
                    rows += '<tr>'
                        + '<td>' + (item.lot_number || '—') + '</td>'
                        + '<td>N/A</td>'
                        + '<td>' + (item.expiration_date || 'N/A') + '</td>'
                        + '<td>—</td>'
                        + '<td class="text-right">' + (item.available_qty || 0) + '</td>'
                        + '</tr>';
                });
                tbody.html(rows);
                empty.addClass('d-none');
            } else {
                tbody.html('');
                empty.removeClass('d-none').text('{{ __("costcenter::inventory.lot_stock_empty") }}');
            }
            tbody.data('loaded', true);
        }).fail(function () {
            tbody.html('');
            empty.removeClass('d-none').text('{{ __("product.lot_stock_error") }}');
        });
    });
</script>
