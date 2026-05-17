<link rel="stylesheet" href="{{asset(asset_path('backend/css/carousel/owl.carousel.min.css'))}}" />
<style>
    
/* Contenedor de navegación */
.product-carousel .owl-nav {
    position: absolute;
    top: 50%;
    width: 100%;
    transform: translateY(-50%);
}

/* Botones */
.product-carousel .owl-prev,
.product-carousel .owl-next {
    position: absolute;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ffffffcc !important;
    border: 1px solid #ddd !important;
    color: #333 !important;
    font-size: 20px !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

/* Posiciones */
.product-carousel .owl-prev {
    left: -10px;
}

.product-carousel .owl-next {
    right: -10px;
}

/* Hover */
.product-carousel .owl-prev:hover,
.product-carousel .owl-next:hover {
    background: var(--base_color) !important;
    color: #fff !important;
    border-color: var(--base_color) !important;
}

/* Quitar outline feo */
.product-carousel .owl-prev:focus,
.product-carousel .owl-next:focus {
    outline: none;
}
</style>
<div class="modal fade theme_modal" id="theme_modal" tabindex="-1"  aria-labelledby="theme_modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('defaultTheme.add_to_cart')}}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="product_quick_view ">
                    
                    <div class="product_details_img">
                            <div class="owl-carousel product-carousel">

                                @if($product->product->gallary_images->count())
                                    @foreach($product->product->gallary_images as $image)
                                        <div class="item">
                                            <img src="{{ showImage($image->images_source) }}" 
                                                class="img-fluid"
                                                alt="product">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="item">
                                        <img alt="product"
                                            @if ($product->thum_img != null)
                                                src="{{ showImage($product->thum_img) }}"
                                            @else
                                                src="{{ showImage($product->product->thumbnail_image_source) }}"
                                            @endif
                                            class="img-fluid"
                                            >
                                    </div>
                                @endif

                            </div>

                    </div>
                    <div class="product_details_wrapper">
                        
                        <div class="product_details">
                            <div class="form-card">
                                @foreach($product->product->categories as $key => $category)
                                <a href="{{route('frontend.category-product',['slug' => $category->slug, 'item' =>'category'])}}" class="product_details_btn_iner">{{$category->name}}</a>
                                @endforeach
                                <div class="tittle">
                                    <h2>{{$product->product->product_name}}</h2>
                                </div>
                                <div class="product_details_review d-flex">
                                        <div class="review_icon">
                                            @php
                                                $fullStars = floor($rating);
                                                $halfStar = ($rating - $fullStars) >= 0.5;
                                            @endphp

                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $fullStars)
                                                    <i class="fas fa-star"></i>
                                                @elseif ($i == $fullStars + 1 && $halfStar)
                                                    <i class="fas fa-star-half-alt"></i>
                                                @else
                                                    <i class="fas fa-star non_rated"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    <p>{{sprintf("%.2f",$rating)}}/5 ({{$total_review<10?'0':''}}{{$total_review}} {{__('defaultTheme.review')}})</p>
                                </div>
                                <div class="details_product_price d-flex">
                                    <h2 id="main_price">
                                        @if($product->hasDeal)
                                            @if ($product->product->product_type == 1)
                                                {{single_price(selling_price($product->skus->first()->selling_price,$product->hasDeal->discount_type,$product->hasDeal->discount))}}
                                            @else
                                                @if (selling_price($product->skus->min('selling_price'),$product->hasDeal->discount_type,$product->hasDeal->discount) === selling_price($product->skus->max('selling_price'),$product->hasDeal->discount_type,$product->hasDeal->discount))
                                                    {{single_price(selling_price($product->skus->min('selling_price'),$product->hasDeal->discount_type,$product->hasDeal->discount))}}
                                                @else
                                                    {{single_price(selling_price($product->skus->min('selling_price'),$product->hasDeal->discount_type,$product->hasDeal->discount))}} - {{single_price(selling_price($product->skus->max('selling_price'),$product->hasDeal->discount_type,$product->hasDeal->discount))}}
                                                @endif
                                            @endif
                                        @else

                                            @if ($product->product->product_type == 1)
                                                @if($product->hasDiscount == 'yes')
                                                    {{single_price(selling_price($product->skus->first()->selling_price,$product->discount_type,$product->discount))}}
                                                @else
                                                    {{single_price($product->skus->first()->selling_price)}}
                                                @endif
                                            @else
                                                @if($product->hasDiscount == 'yes')
                                                    @if (selling_price($product->skus->min('selling_price'),$product->discount_type,$product->discount) === selling_price($product->skus->max('selling_price'),$product->discount_type,$product->discount))
                                                        {{single_price(selling_price($product->skus->min('selling_price'),$product->discount_type,$product->discount))}}
                                                    @else
                                                        {{single_price(selling_price($product->skus->min('selling_price'),$product->discount_type,$product->discount))}} - {{single_price(selling_price($product->skus->max('selling_price'),$product->discount_type,$product->discount))}}
                                                    @endif
                                                @else
                                                    @if ($product->skus->min('selling_price') === $product->skus->max('selling_price'))
                                                        {{single_price($product->skus->min('selling_price'))}}
                                                    @else
                                                        {{single_price($product->skus->min('selling_price'))}} - {{single_price($product->skus->max('selling_price'))}}
                                                    @endif
                                                @endif
                                            @endif
                                        @endif
                                    </h2>
                                    <span>{{$product->discount>0?single_price($product->skus->max('selling_price')):''}}</span>

                                    <input type="hidden" name="product_sku_id" id="product_sku_id" value="{{$product->product->product_type == 1?$product->skus->first()->id : $product->skus->first()->id}}">
                                    <input type="hidden" name="seller_id" id="seller_id" value="{{$product->user_id}}">
                                    <input type="hidden" name="stock_manage_status" id="stock_manage_status" value="{{$product->stock_manage}}">
                                    <input type="hidden" id="product_id" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" id="maximum_order_qty" value="{{@$product->product->max_order_qty}}">
                                    <input type="hidden" id="minimum_order_qty" value="{{@$product->product->minimum_order_qty}}">
                                </div>
                                @if ($product->stock_manage == 0)
                                    <p id="availability" class="d-none">{{__('defaultTheme.unlimited')}}</p>
                                @endif
                                @if(isModuleActive('MultiVendor'))
                                <div class="single_details_content d-flex mb-2">
                                    <h5 class="mb-0">{{__('defaultTheme.sold_by')}}:</h5>
                                    @if ($product->seller->slug)
                                    <a href="{{route('frontend.seller',$product->seller->slug)}}" class="ml-2 text-secondary">
                                        @if (@$product->seller->SellerAccount->seller_shop_display_name)
                                            {{ @$product->seller->SellerAccount->seller_shop_display_name }}
                                        @else
                                            {{$product->seller->first_name .' '.$product->seller->last_name}}
                                        @endif
                                    </a>
                                    @else
                                    <a href="{{route('frontend.seller',base64_encode($product->seller->id))}}" class="ml-2 text-secondary">
                                        @if (@$product->seller->SellerAccount->seller_shop_display_name)
                                            {{ @$product->seller->SellerAccount->seller_shop_display_name }}
                                        @else
                                            {{$product->seller->first_name .' '.$product->seller->last_name}}
                                        @endif
                                    </a>
                                    @endif

                                </div>
                                @endif
                                <div class="product_details_content mb-2">
                                    <ul>
                                        @php
                                            $stock = 0;
                                        @endphp
                                        @if ($product->stock_manage == 1)
                                            <li>{{__('defaultTheme.availability')}} : <span id="availability">{{ $product->skus->first()->product_stock }}</span></li>
                                        @endif
                                        <li>{{__('common.points')}}:
                                                <span>{{ $product->product->club_point ?? '-' }}</span>
                                            
                                        </li>
                                        <li>{{__('defaultTheme.sku')}}: <span id="sku_id_li">{{$product->skus->first()->sku->sku}}</span></li>
                                        
                                        <li>{{__('common.tag')}} : <span>
                                            @php
                                                $total_tag = count($product->product->tags);
                                            @endphp
                                            @foreach($product->product->tags as $key => $tag)
                                                <a class="tag_link" target="_blank" href="{{route('frontend.category-product',['slug' => $tag->name, 'item' =>'tag'])}}">{{$tag->name}}</a>
                                                @if($key + 1 < $total_tag), @endif
                                            @endforeach
                                            </span>
                                        </li>
                                        
                                    </ul>
                                </div>
                                @if($product->product->product_type == 2)
                                    @foreach (session()->get('item_details') as $key => $item)
                                        @if ($item['attr_id'] != 1)
                                            <div class="single_details_content d-md-flex mb-1">
                                                <h5 class="primary_input_label">{{$item['name']}}:</h5>
                                                <input type="hidden" class="attr_value_name" name="attr_val_name[]" value="{{$item['value'][0]}}">
                                                <input type="hidden" class="attr_value_id" name="attr_val_id[]" value="{{$item['id'][0]}}-{{$item['attr_id']}}">
                                                <div class="size_bt ml-3">
                                                    @foreach ($item['value'] as $m => $value_name)
                                                        <a class="attr_val_name border rounded px-2 py-1 cursor-pointer d-inline-block @if ($m === 0) selected_btn @endif" color="not" data-value-key="{{$item['attr_id']}}" data-value="{{ $item['id'][$m] }}">{{ $value_name }}</a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    
                                @endif
                                @php
                                    $variant_images = [];
                                    $variant_skus = [];
                                    foreach($product->skus->where('status',1) as $sku){
                                        if(@$sku->sku->variant_image){
                                            $variant_images[] = $sku->sku->variant_image;
                                            $variant_skus[] = $sku->sku->sku;
                                        }
                                    }
                                @endphp
                                <div class="single_details_content d-md-flex">
                                    <div class="details_text d-flex">
                                        <h5 class="primary_input_label mb-0">{{__('common.quantity')}}:</h5>
                                        <div class="product_count">
                                            <input type="text" name="qty" class="qty" id="qty" readonly value="{{@$product->product->minimum_order_qty}}"/>
                                            <div class="button-container">
                                                <button class="cart-qty-plus qtyChangePlus" type="button" value="+">
                                                    <i class="ti-plus"></i>
                                                </button>
                                                <button class="cart-qty-minus qtyChangeMinus" type="button" value="-">
                                                    <i class="ti-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="product_type" class="product_type" value="{{ $product->product->product_type }}">


                                @if($product->product->product_type == 2)
                                    @foreach (session()->get('item_details') as $key => $item)

                                        @if ($item['attr_id'] == 1)
                                        <div class="single_details_content d-md-flex pl-0">
                                        <div class="details_text d-flex">
                                            <h4>{{ $item['name'] }}:</h4>
                                            <div class="cs_color_btn">
                                                <div class="cs_radio_btn">
                                                    <input type="hidden" class="attr_value_name" name="attr_val_name[]" value="{{$item['value'][0]}}">
                                                    <input type="hidden" class="attr_value_id" name="attr_val_id[]" value="{{$item['id'][0]}}-{{$item['attr_id']}}">
                                                    @foreach ($item['value'] as $k => $value_name)
                                                        <div class="radio modal_colors_{{$k}} class_color_{{ $item['code'][$k] }}">
                                                            <input id="radio-{{$k}}_modal" name="radio" type="radio" color="color" class="attr_val_name attr_clr @if ($k === 0) selected_btn @endif" data-value="{{ $item['id'][$k] }}" data-value-key="{{$item['attr_id']}}" value="{{ $value_name }}"/>
                                                            <label for="radio-{{$k}}_modal" class="radio-label"></label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        @endif
                                    @endforeach
                                @endif
                                <div class="single_details_content mt_30 d-md-flex">
                                    <h5 class="primary_input_label">{{__('defaultTheme.shipping')}}:</h5>
                                    <div class="details_content_iner">
                                        <select name="shipping_type" id="shipping_type" class="w-100">
                                            <option value="">{{__('common.please_select')}}</option>
                                            @foreach(@$product->product->shippingMethods as $key => $method)
                                            <option value="{{@$method->shippingMethod->id}}">
                                                {{@$method->shippingMethod->method_name}} - {{single_price(@$method->shippingMethod->cost)}} ({{@$method->shippingMethod->shipment_time}})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @if(isset($customer) && $customer)
                                <div class="border rounded p-2 mt-2 mb-2">
                                    <p class="font-weight-bold mb-2 pb-1 border-bottom" style="font-size:14px;">{{__('common.representative')}}</p>
                                    <div class="row no-gutters">
                                        <div class="col-6 mb-2 pr-2">
                                            <small class="text-muted d-block">{{__('common.name')}}</small>
                                            <span class="font-weight-bold" style="font-size:13px; word-break:break-word;">{{ trim("{$customer->first_name} {$customer->last_name}") }}</span>
                                        </div>
                                        <div class="col-6 mb-2 pl-2">
                                            <small class="text-muted d-block">{{__('common.email')}}</small>
                                            <span style="font-size:13px; word-break:break-all;">{{ $customer->email }}</span>
                                        </div>
                                        @if($customer->customerProfile?->whatsapp ?? $customer->phone)
                                        <div class="col-6 pr-2">
                                            <small class="text-muted d-block">{{__('common.phone')}}</small>
                                            <span style="font-size:13px;">{{ $customer->customerProfile?->whatsapp ?? $customer->phone }}</span>
                                        </div>
                                        @endif
                                        @if($customer->customerShippingAddress?->address)
                                        <div class="col-6 pl-2">
                                            <small class="text-muted d-block">{{__('common.address')}}</small>
                                            <span style="font-size:13px; word-break:break-word;">{{ $customer->customerShippingAddress->address }}@if($customer->customerShippingAddress->getCity?->name), {{ $customer->customerShippingAddress->getCity->name }}@endif</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                
                                <div class="single_details_content d-flex">
                                    <input type="hidden" name="base_sku_price" id="base_sku_price" value="
                                    @if(@$product->hasDeal)
                                        {{ selling_price($product->skus->first()->selling_price,$product->hasDeal->discount_type,$product->hasDeal->discount) }}
                                    @else
                                        @if($product->hasDiscount == 'yes')
                                            {{ selling_price($product->skus->first()->selling_price,$product->discount_type,$product->discount) }}
                                        @else
                                            {{ $product->skus->first()->selling_price }}
                                        @endif
                                    @endif
                                    ">
                                    <input type="hidden" name="final_price" id="final_price" value="
                                    @if(@$product->hasDeal)
                                        {{ selling_price($product->skus->first()->selling_price,$product->hasDeal->discount_type,$product->hasDeal->discount) }}
                                    @else
                                        @if($product->hasDiscount == 'yes')
                                            {{ selling_price($product->skus->first()->selling_price,$product->discount_type,$product->discount) }}
                                        @else
                                            {{ $product->skus->first()->selling_price }}
                                        @endif
                                    @endif
                                    ">
                                    <h5 class="mb-0 primary_input_label">{{__('common.total')}}:</h5>
                                    <h2 id="total_price">
                                        @if(@$product->hasDeal)
                                            {{single_price(selling_price(@$product->skus->first()->selling_price,@$product->hasDeal->discount_type,@$product->hasDeal->discount) * $product->product->minimum_order_qty)}}
                                        @else
                                            @if($product->hasDiscount == 'yes')
                                                {{single_price(selling_price(@$product->skus->first()->selling_price,@$product->discount_type,@$product->discount) * $product->product->minimum_order_qty)}}
                                            @else
                                                {{single_price(@$product->skus->first()->selling_price * $product->product->minimum_order_qty)}}
                                            @endif
                                        @endif
                                    </h2>
                                </div>
                                <div class="product_details_btn">
                                    <span id="add_to_cart_div">
                                    @if ($product->stock_manage == 1 && $product->skus->first()->product_stock >= $product->product->minimum_order_qty)
                                        <button type="button" id="add_to_cart_btn" class="btn-toolkit btn-primary">{{__('defaultTheme.add_to_cart')}}</button>
                                    @elseif($product->stock_manage == 0)
                                        <button type="button" id="add_to_cart_btn" class="btn-toolkit btn-primary">{{__('defaultTheme.add_to_cart')}}</button>
                                    @else
                                        <p class="out_of_stock">{{__('defaultTheme.out_of_stock')}}</p>
                                    @endif
                                    </span>
                                </div>
                            </div>
                        
                        
                        
                        
                        
                        
                        
                            {{-- @if ($product->product->product_type == 2)
                                <div class="single_details_content variant_image d-md-flex">
                                    <h5>{{__('common.image')}}:</h5>
                                    <div class="img_div_width owl-carousel">
                                        <input type="hidden" class="productQtyCount" value="{{$product->skus->count()}}">
                                        @foreach($product->skus as $sku)
                                            @if ($sku->sku->variant_image)
                                                <div class="variant_img_div sku_img_div @if($loop->first) active @endif " id="{{$sku->sku->sku }}">
                                                    <img src="{{showImage($sku->sku->variant_image)}}" class="img-fluid p-1 var_img_source " title="{{ $sku->sku->sku }}" alt="{{ $sku->sku->sku  }}" data-id="{{$sku->sku->sku }}"/>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif --}}
                        
                        
                        
                        
                    
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(@$product->hasDeal)
    <input type="hidden" id="discount_type" value="{{@$product->hasDeal->discount_type}}">
    <input type="hidden" id="discount" value="{{@$product->hasDeal->discount}}">
@else
    @if(@$product->hasDiscount == 'yes')
        <input type="hidden" id="discount_type" value="{{$product->discount_type}}">
        <input type="hidden" id="discount" value="{{$product->discount}}">
    @else
        <input type="hidden" id="discount_type" value="{{$product->discount_type}}">
        <input type="hidden" id="discount" value="0">
    @endif
@endif
<input type="hidden" id="currency_symbol" value="@if(session()->get('currency')) {{session()->get('currency')}} @else {{app('general_setting')->currency_symbol}} @endif">
<input type="hidden" id="currency_rate" value="@if(session()->get('currency')) {{session()->get('convert_rate')}} @else 1 @endif">

<script src="{{asset(asset_path('backend/css/carousel/owl.carousel.min.js'))}}"></script>
<script>
    (function($){
        "use strict";

        $(document).ready(function(){
            $(".product-carousel").owlCarousel({
        loop: true,
        items: 1,
        autoplay: false,
        margin: 10,
        navText: [
            '<i class="ti-arrow-left"></i>',
            '<i class="ti-arrow-right"></i>',
        ],
        nav: true,
        dots: false,
        touchDrag  : true,
        mouseDrag  : true,
    });
    $(document).on('click', '.var_img_source', function(event){
        var logo = $(this).attr("src");
        // var color_id = $(this).data("id");
        // var attr_id = $( '.attr_val_name' ).data("value-key");
        $(".sku_img_div").removeClass('active');
        $(this).addClass('active')

        $('.var_img_show').attr("src", logo);

    });
            var productType = $('.product_type').val();
            if (productType == 2) {
                '@if (session()->has('item_details'))'+
                    '@foreach (session()->get('item_details') as $key => $item)'+
                        '@if ($item['attr_id'] === 1)'+
                            '@foreach ($item['value'] as $k => $value_name)'+
                                $(".modal_colors_{{$k}}").css("background-color", "{{ $item['code'][$k]}}");
                            '@endforeach'+
                        '@endif'+
                    '@endforeach'+
                '@endif'
            }
        });
    })(jQuery);
</script>

