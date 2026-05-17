<div class="modal fade admin-query" id="productDetails">
    <div class="modal-dialog modal_1000px modal-dialog-centered modal-lg">
        <div class="modal-content tk-modal-content">

            <div class="modal-header">
                <h4 class="tk-modal-title">{{ $product->product_name }} - {{ __('product.details') }}</h4>
                <button type="button" class="tk-btn-close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-5">
                        <div class="form-card">
                            <img class="mx-auto rounded" id="pk-main-image-{{ $product->id }}" src="{{ showImage($product->thumbnail_image_source) }}" alt="{{ $product->product_name }}">
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

                    <div class="col-lg-7">
                        <div class="form-card">
                            <h3 class="">{{__('common.general_info')}}</h3>
                            
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.product_name')}}:</span>
                                <span class="tk-detail-value">{{ $product->product_name }}</span>
                            </div>
                            
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.SKU')}}:</span>
                                <span class="tk-detail-value">{{ $product->skus->first()->sku }}</span>
                            </div>

                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.product_type')}}:</span>
                                <span class="tk-detail-value">{{$product->product_type == 1 ? __("product.physical_product") : __("product.digital_product") }}</span>
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
                                <span class="tk-detail-label">{{__('common.points')}}:</span>
                                <span class="tk-detail-value">{{ @$product->club_point ?? 'N/A' }}</span>
                            </div>

                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('common.price_from_points')}}:</span>
                                <span class="tk-detail-value">{{ $product->price_from_points ? __('common.yes') : __('common.no') }}</span>
                            </div>

                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.brand')}}:</span>
                                <span class="tk-detail-value">{{@$product->brand->name}}</span>
                            </div>

                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.unit')}}:</span>
                                <span class="tk-detail-value">{{@$product->unit_type->name}}</span>
                            </div>

                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.min_stock')}} / {{__('product.max_stock')}}:</span>
                                <span class="tk-detail-value">{{getNumberTranslate($product->min_stock)}} - {{getNumberTranslate($product->max_stock)}}</span>
                            </div>

                            <div class="tk-detail-row d-none">
                                <span class="tk-detail-label">{{__('product.unit_cost')}}:</span>
                                @php $purchase_price = $product->skus->first()->purchase_price ? $product->skus->first()->purchase_price : 0 @endphp
                                <span class="tk-detail-value">{{single_price($purchase_price)}}</span>
                            </div>

                            @if($product->tax != 0)
                                <div class="tk-detail-row">
                                    <span class="tk-detail-label">{{__('product.tax')}}:</span>
                                    <span class="tk-detail-value">{{ getNumberTranslate(($product->tax_type == 1) ? single_price($product->tax) : $product->tax. "%") }}</span>
                                </div>
                            @endif

                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.discount')}}:</span>
                                <span class="tk-detail-value" style="color: var(--toolkit_corporative-red-color);">
                                    {{ ($product->discount_type == 1) ? single_price($product->discount) : $product->discount. "%" }}
                                </span>
                            </div>

                            @if ($product->is_physical != 1 && $product->product_type == 1 && @$product->skus->first()->digital_file->file_source != null)
                            <div class="tk-detail-row">
                                <span class="tk-detail-label">{{__('product.download_file')}}:</span>
                                <span class="tk-detail-value">
                                    <a href="{{ asset(asset_path(@$product->skus->first()->digital_file->file_source)) }}" style="color: var(--toolkit_premium-brown-color); text-decoration: underline;">
                                        {{ __('product.click_on_it') }}
                                    </a>
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if (count($product->skus) > 0)
                    <div class="col-12">
                        <div class="form-card dataTables_wrapper">
                            <h3 class="">
                                {{__('product.variant_items')}}
                                <span class="badge_5" style="margin-left:10px;">{{ count($product->skus) }}</span>
                            </h3>
                            <x-admin.table-container>
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{__('product.attribute')}}</th>
                                                <th>{{__('product.product_sku')}}</th>
                                                <th>{{__('product.selling_price')}}</th>
                                                <th class="text-center">{{ __('product.stock') }}</th>
                                                <th>{{ __('common.status') }}</th>
                                                @if ($product->is_physical != 1 && $product->product_type == 2)
                                                <th>{{__('product.download_file')}}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($product->skus as $key => $sku)
                                            @php $skuMainStock = $mainStockBySkuId[$sku->id] ?? 0; @endphp
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
                                                        {{ getNumberTranslate((float) $skuMainStock) }}
                                                        @if(@$product->unit_type)
                                                            <span class="small text-muted font-weight-normal">({{ $product->unit_type->name }})</span>
                                                        @endif
                                                    </span>
                                                    @if($skuMainStock > 0)
                                                    <br>
                                                    <button type="button"
                                                        class="btn-toolkit btn-secondary-outline mt-1 btn-toggle-lots"
                                                        data-target="#lots-main-{{ $sku->id }}"
                                                        data-sku-id="{{ $sku->id }}"
                                                        data-location="main"
                                                        style="font-size:11px; padding: 2px 8px;">
                                                        <i class="ti-package"></i> {{ __('product.lot_stock_table_lot') }}
                                                    </button>
                                                    @endif
                                                </td>
                                                <td>
                                                    <label class="switch_toggle" for="checkboxy{{ $sku->id }}">
                                                        <input type="checkbox" id="checkboxy{{ $sku->id }}" @if($sku->status == 1) checked @endif value="{{ $sku->id }}" class="sku_status_change" data-id="{{ $sku->id }}">
                                                        <div class="slider round"></div>
                                                    </label>
                                                </td>
                                                @if ($product->is_physical != 1 && $product->product_type == 2 && @$sku->digital_file->file_source != null)
                                                <td>
                                                    <a href="{{ asset(asset_path(@$sku->digital_file->file_source)) }}" style="color: var(--toolkit_corporative-orange-color);">
                                                        <i class="ti-download"></i> {{ __('product.click_on_it') }}
                                                    </a>
                                                </td>
                                                @endif
                                            </tr>
                                            {{-- Fila desplegable de lotes --}}
                                            <tr id="lots-main-{{ $sku->id }}" class="d-none">
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
                                                        <div class="lot-empty text-muted small mt-1 d-none">{{ __('product.lot_stock_empty') }}</div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </x-admin.table-container>
                        </div>
                    </div>
                    @endif

                    @if(!empty($product->description))
                    <div class="col-12">
                        <div class="form-card">
                            <h3 class="">{{__('common.description')}}</h3>
                            <div class="text-black">
                                {!! $product->description !!}
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
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

        var modal = thumb.closest('#productDetails');
        if (!modal) return;

        var mainImage = modal.querySelector('#pk-main-image-{{ $product->id }}');
        if (!mainImage) return;

        var newSrc = thumb.getAttribute('data-large-src') || thumb.getAttribute('src');
        if (newSrc) mainImage.setAttribute('src', newSrc);
    });

    // Desplegable de lotes por SKU — bodega principal
    $(document).on('click', '#productDetails .btn-toggle-lots', function () {
        var skuId   = $(this).data('sku-id');
        var target  = $($(this).data('target'));
        var tbody   = target.find('.lot-tbody');
        var empty   = target.find('.lot-empty');

        if (!target.hasClass('d-none')) {
            target.addClass('d-none');
            return;
        }

        // Si ya está cargado, solo mostrar
        if (tbody.data('loaded')) {
            target.removeClass('d-none');
            return;
        }

        target.removeClass('d-none');
        tbody.html('<tr><td colspan="5" class="text-center text-muted small">{{ __("product.lot_stock_loading") }}</td></tr>');

        $.get('{{ route("product.lot-stock") }}', { product_sku_id: skuId }, function (resp) {
            var rows = '';
            if (resp && resp.data && resp.data.length) {
                resp.data.forEach(function (item) {
                    var statusHtml = item.status ? '<span class="' + item.status.class + '">' + item.status.label + '</span>' : 'N/A';
                    rows += '<tr>'
                        + '<td>' + (item.lot_number || 'Sin lote') + '</td>'
                        + '<td>' + (item.manufacture_date || 'N/A') + '</td>'
                        + '<td>' + (item.expiration_date || 'N/A') + '</td>'
                        + '<td>' + statusHtml + '</td>'
                        + '<td class="text-right">' + (item.qty || 0) + '</td>'
                        + '</tr>';
                });
                tbody.html(rows);
                empty.addClass('d-none');
            } else {
                tbody.html('');
                empty.removeClass('d-none');
            }
            tbody.data('loaded', true);
        }).fail(function () {
            tbody.html('');
            empty.removeClass('d-none').text('{{ __("product.lot_stock_error") }}');
        });
    });
</script>
