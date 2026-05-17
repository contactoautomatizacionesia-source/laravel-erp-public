<div class="modal fade" id="lot_stock_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('product.lot_stock_modal_title') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-card">
                    <h3>{{ __('common.details') }}</h3>
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <div class="text-black">
                            <strong id="lot_stock_sku_label">{{ __('product.lot_stock_dash') }}</strong>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('product.lot_stock_table_lot') }}</th>
                                    <th>{{ __('product.lot_stock_table_mfg') }}</th>
                                    <th>{{ __('product.lot_stock_table_exp') }}</th>
                                    <th>{{ __('product.lot_stock_table_status') }}</th>
                                    <th class="text-right">{{ __('product.lot_stock_table_qty') }}</th>
                                </tr>
                            </thead>
                            <tbody id="lot_stock_tbody"></tbody>
                        </table>
                        <div class="text-black text-right">
                            {{ __('product.lot_stock_modal_total') }}: <strong id="lot_stock_total">0</strong>
                        </div>
                    </div>
                    <div id="lot_stock_empty" class="text-muted small d-none mt-2">{{ __('product.lot_stock_empty') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
