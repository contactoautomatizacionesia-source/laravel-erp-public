<table class="table" id="mainProductTable">
    <thead>
        @php
            $user = auth()->user();
            $type = $user->role->type;
        @endphp
    <tr>
        <th scope="col">{{ __('common.sl') }}</th>
        <th scope="col">{{ __('product.sku') }}</th>
        <th scope="col">{{ __('product.category') }}</th>
        <th scope="col">{{ __('common.name') }}</th>
        <th scope="col">{{ __('common.image') }}</th>
        <th scope="col">{{ __('common.product_type') }}</th>
        <th scope="col">{{ __('product.brand') }}</th>
        <th scope="col">{{ __('product.variant_sku_prefix') }}</th>
        <th scope="col">{{ __('product.min_stock') }}</th>
        <th scope="col">{{ __('product.max_stock') }}</th>
        @if(!isModuleActive('MultiVendor'))
        <th scope="col">{{ __('product.stock') }}</th>
        @endif
        <th scope="col">{{ __('common.unit_price') }}</th>
        <th scope="col">{{ __('common.points') }}</th>
        <th scope="col">{{ __('common.updated_by') }}</th>
        @if($type == "superadmin" || $type == "admin" || $type == "staff")
        <th scope="col">{{ __('common.status') }}</th>
        @else
        <th scope="col">{{ __('common.approval') }}</th>
        @endif
        <th scope="col">{{ __('common.action') }}</th>
    </tr>
    </thead>

</table>
