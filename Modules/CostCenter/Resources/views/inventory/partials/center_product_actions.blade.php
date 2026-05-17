<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item center_product_detail" data-product-id="{{ $product_id }}" data-center-id="{{ $center_id }}">
            {{ __('common.view') }}
        </a>
        <a class="dropdown-item center_stock_alert"
           data-product-id="{{ $product_id }}"
           data-center-id="{{ $center_id }}"
           data-min="{{ $min_stock }}"
           data-max="{{ $max_stock }}">
            {{ __('costcenter::inventory.stock_alerts') }}
        </a>
    </div>
</div>
