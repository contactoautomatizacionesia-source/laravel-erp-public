<!-- wallet_modal::start  -->
<div class="modal fade theme_modal2" id="cart_add_modal" tabindex="-1"  aria-labelledby="theme_modal" aria-hidden="true">
    <div class="modal-dialog max_width_430 modal-dialog-centered" >
        <div class="modal-content rounded-20">
            <div class="modal-body p-0">
                <div class="add_cart_modalAdded">
                    <div id="points_badge">
                        <span class="start"><x-public-point-icon /></span>
                         <span id="cart_suceess_points"></span>
                    </div>
                    <div class="product_checked_box d-flex gap-2  align-items-center justify-content-between">
                        
                        <h4>{{__('defaultTheme.item_added_to_your_cart')}}</h4>
                        <button type="button" class="close_modal_icon" data-bs-dismiss="modal">
                            <i class="ti-close"></i>
                        </button>
                    </div>
                    <div class="cart_added_box">
                        <a id="cart_suceess_url" class="cart_added_box_item d-flex align-items-center gap_25 flex-sm-wrap flex-md-nowrap">
                            <div class="thumb">
                                <img class="img-fluid rounded" id="cart_suceess_thumbnail" src="{{url('/')}}/public/frontend/amazy/img/cart_added_thumb.png" alt="" title="">
                            </div>
                            <div class="cart_added_content">
                                <h4 id="cart_suceess_name" class="mb-0">-</h4>
                                <h5 id="cart_suceess_price">-</h5>
                            </div>
                        </a>
                    </div>
                    <div class="d-flex flex-column gap_10">
                        <a href="{{url('/cart')}}" class="amaz_primary_btn style3 text-uppercase rounded-3">{{__('common.view_cart')}}</a>
                        @if(!app('general_setting')->seller_wise_payment && !isModuleActive('MultiVendor'))
                            <a href="{{url('/checkout')}}" class="amaz_primary_btn3 justify-content-center style3 text-uppercase rounded-3">{{__('common.process_to_checkout')}}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- wallet_modal::end  -->
