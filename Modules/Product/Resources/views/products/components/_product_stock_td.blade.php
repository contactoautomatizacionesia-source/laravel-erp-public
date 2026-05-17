
@if ($products->stock_manage == 1)
    @php
        $stock = 0;
    @endphp
    @php
        $skuIds = $products->skus->pluck('id')->filter()->values();
        if ($skuIds->isNotEmpty()) {
            $stock = \Modules\CostCenter\Entities\CostCenterInventoryLot::where('location_type', 'main')
                ->whereNull('location_id')
                ->whereIn('product_sku_id', $skuIds)
                ->sum('qty');
        }
    @endphp
@else
    @php
        $stock = __("common.not_manage");
    @endphp
@endif

{{ is_numeric($stock) ? (int) round($stock) : $stock }}
@if ($products->unit_type_id != null)
    ({{ @$products->unit_type->name }})
@endif
