<div class="form-card  " style="border-color: var(--base_color)">
    <div class="">
        <div class="mb-2">
            <span class="badge_1">{{ __('common.selected_product') }}</span>
        </div>
        <div class="row align-items-center">

            <!-- Imagen -->
            <div class="col-auto">
                <img
                    src="{{ showImage($product->thumbnail_image_source) }}"
                    alt="{{ $product->product_name }}"
                    class="rounded"
                    style="width: 60px; height: 60px; object-fit: cover;"
                >
            </div>

            <!-- Info principal -->
            <div class="col">
                <div class="d-flex flex-column">

                    <!-- Nombre + SKU -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <strong class="text-dark">
                            {{ $product->product_name }}
                        </strong>

                        <small class="text-muted">
                            ({{ $product->skus->first()->sku ?? 'N/A' }})
                        </small>
                    </div>

                    <!-- Categorías -->
                    <div class="mt-1">
                        @foreach($product->categories ?? [] as $category)
                            <span class="badge bg-light text-dark border">
                                {{@$category->name}}
                            </span>
                        @endforeach
                    </div>

                    <!-- Meta info -->
                    <div class="mt-1 small text-muted d-flex flex-wrap gap-3">
                        <span>
                            {{__('product.brand')}}: <strong>{{@$product->brand->name}}</strong>
                        </span>

                        <span>
                            {{__('product.unit')}}: <strong>{{@$product->unit_type->name}}</strong>
                        </span>

                        <span>
                            {{ __('inventory.stock') }}: <strong>{{getNumberTranslate($product->min_stock)}} - {{getNumberTranslate($product->max_stock)}}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Precio / puntos / estado -->
            <div class="col-auto text-end">
                <div class="d-flex flex-column align-items-end">

                    <!-- Precio -->
                    <strong class="text-dark">
                        {{ single_price(@$product->skus->first()->selling_price) ?? 'N/A' }}
                    </strong>

                    <!-- Puntos -->
                    <small class="text-muted">
                        {{__('common.points')}}: {{ @$product->club_point ?? 'N/A' }}
                    </small>

                </div>
            </div>

        </div>
    </div>
</div>
