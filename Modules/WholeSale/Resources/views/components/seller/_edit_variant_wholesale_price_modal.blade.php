<div class="modal fade admin-query" id="variant_wholesale_price_modal_{{ $modalTargetId }}">
    <div class="modal-dialog modal_1000px modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('wholesale.Wholesale Price') }}</h4>
                <button type="button" class="close " data-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="row" id="repeat_{{$modalTargetId}}">
                    @php
                        $wholesalePriceInfo = null;
                        if (isset($sellerProductSkuInfo->id)){
                             $wholesalePriceInfo = @$sellerProductSkuInfo->wholeSalePrices;
                        }
                    @endphp

                    @if( $wholesalePriceInfo!=null )
                        @foreach($wholesalePriceInfo as $w_key=>$w_price)
                            <div class="col-lg-12 variant_whole_sale_price_list mt-2">
                                <div class="row">
                                    <div class="col">
                                        <input type="text" class="form-control primary_input_field" value="{{$w_price->min_qty}}" name="wholesale_min_qty_v_{{$incKey}}[]">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control primary_input_field" value="{{$w_price->max_qty}}" name="wholesale_max_qty_v_{{$incKey}}[]">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control primary_input_field" value="{{$w_price->selling_price}}" name="wholesale_price_v_{{$incKey}}[]">
                                    </div>
                                    <div class="col">
                                        <button type="button" class="float-left mt-2 style_plus_icon remove_variant_whole_sale border-0">
                                            <i class="ti-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-lg-12 variant_whole_sale_price_list mt-2">
                            <div class="row">
                                <div class="col">
                                    <input type="text" class="form-control primary_input_field" placeholder="Min QTY" name="wholesale_min_qty_v_{{$incKey}}[]">
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control primary_input_field" placeholder="Max QTY" name="wholesale_max_qty_v_{{$incKey}}[]">
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control primary_input_field" placeholder="Price per piece" name="wholesale_price_v_{{$incKey}}[]">
                                </div>
                                <div class="col">
                                    <button type="button" class="float-left mt-2 style_plus_icon remove_variant_whole_sale border-0">
                                        <i class="ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-10 text-right">
                        <button type="button" data-id="#repeat_{{$modalTargetId}}" incKey="{{$incKey}}" style="margin-right:22px" class="btn btn-sm style_plus_icon add_variant__whole_sale_price">
                            <i class="ti-plus"></i>
                        </button>
                    </div>
                    <div class="col-md-10 text-right">
                        <button type="button" class="primary_btn_2 mt-5 text-center wholesale_p_save_btn" append_w_priceId="{{$modalTargetId}}" w_incKey="{{$incKey}}" style="margin-right: 22px"><i class="ti-check"></i>Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
