<li class="notification_warp_pop mt-2">
    <a class="popUP_clicker gredient_hover" href="#">
        <!-- plus     -->
        <i class="fas fa-plus-square fs-20 mx-2"></i>
        <!--/ plus      -->
    </a>
    <div class="menu_popUp_list_wrapper">
        <!-- popUp_single_wrap  -->
        <div class="popUp_single_wrap">
            @if (permissionCheck('appearance.slider.index') || permissionCheck('menu.manage'))
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('appearance.appearance') }}</h4>
                </div>
                <ul>
                    @if (permissionCheck('appearance.slider.index'))
                    <li><a href="{{ route('appearance.slider.index') }}"> <i class="ti-plus"></i> {{__('appearance.slider')}}</a></li>
                    @endif
                    @if (permissionCheck('menu.manage'))
                    <li><a href="{{ route('menu.manage') }}"><i class="ti-plus"></i>{{ __('appearance.menus') }}</a></li>
                    @endif
                </ul>
            </div>
            @endif
            @if (permissionCheck('blog.tags.index') || permissionCheck('blog.posts.create'))
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('blog.blog') }}</h4>
                </div>
                <ul>
                    @if (permissionCheck('blog.posts.index'))
                    <li><a href="{{ route('blog.posts.index') }}"><i class="ti-plus"></i>{{ __('common.list') }}</a></li>
                    @endif
                    @if(permissionCheck('blog.posts.create'))
                    <li><a href="{{ route('blog.posts.create') }}"><i class="ti-plus"></i>{{ __('common.create') }}</a></li>
                    @endif
                </ul>
            </div>
            @endif
            @if(isModuleActive('MultiVendor'))
                @if (permissionCheck('admin.merchants_list.get-data') || permissionCheck('admin.merchants_create'))
                <div class="popup_single_item">
                    <div class="main-title2 mb_10">
                        <h4 class="mb_15">{{ __('common.seller') }}</h4>
                    </div>
                    <ul>
                        @if (permissionCheck('admin.merchants_list.get-data'))
                        <li><a href="{{ route('admin.merchants_list') }}"> <i class="ti-plus"></i> {{ __('common.list') }}</a></li>
                        @endif
                        @if (permissionCheck('admin.merchants_create'))
                        <li><a href="{{ route('admin.merchants_create') }}"><i class="ti-plus"></i>{{ __('common.create') }}</a></li>
                        @endif
                    </ul>
                </div>
                @endif
            @endif
        </div>
        <!-- popUp_single_wrap  -->
        <div class="popUp_single_wrap">
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('common.order') }}</h4>
                </div>
                <ul>
                    @if (permissionCheck('order_manage.total_sales_get_data'))
                    <li><a href="{{route('order_manage.total_sales_index')}}"> <i class="ti-plus"></i>{{ __('order.total_order') }}</a></li>
                    @endif
                    @if(isModuleActive('MultiVendor'))
                        <li><a href="{{route('order_manage.my_sales_index')}}"><i class="ti-plus"></i>{{ __('order.my_order') }}</a></li>
                    @endif
                </ul>
            </div>
            @if (permissionCheck('admin.inhouse-order.get-data') || permissionCheck('admin.inhouse-order.create'))
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('order.in_house_order') }}</h4>
                </div>
                <ul>
                    @if (permissionCheck('admin.inhouse-order.get-data'))
                    <li><a href="{{route('admin.inhouse-order.index')}}"> <i class="ti-plus"></i>{{ __('common.list') }}</a></li>
                    @endif
                    @if (permissionCheck('admin.inhouse-order.create'))
                    <li><a href="{{ route('admin.inhouse-order.create') }}"><i class="ti-plus"></i>{{ __('common.create') }}</a></li>
                    @endif
                </ul>
            </div>
            @endif
            @if (permissionCheck('product.index') || permissionCheck('product.create'))
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('common.product') }}</h4>
                </div>
                <ul>
                    @if (permissionCheck('product.index'))
                    <li><a href="{{ route('product.index') }}"> <i class="ti-plus"></i>{{ __('common.list') }}</a></li>
                    @endif
                    @if (permissionCheck('product.create'))
                    <li><a href="{{route("product.create")}}"><i class="ti-plus"></i>{{ __('common.create') }}</a></li>
                    @endif
                </ul>
            </div>
            @endif
        </div>
        <!-- popUp_single_wrap  -->
        <div class="popUp_single_wrap">
            @if(isModuleActive('MultiVendor'))
                @if (permissionCheck('admin.my-product.index') || permissionCheck('admin.my-product.create'))
                    <div class="popup_single_item">
                        <div class="main-title2 mb_10">
                            <h4 class="mb_15">{{ __('common.inhouse_product') }}</h4>
                        </div>
                        <ul>
                            @if (permissionCheck('admin.my-product.index'))
                            <li><a href="{{ route('admin.my-product.index') }}"> <i class="ti-plus"></i>{{ __('common.list') }}</a></li>
                            @endif
                            @if (permissionCheck('admin.my-product.create'))
                            <li><a href="{{ route('admin.my-product.create') }}"><i class="ti-plus"></i>{{ __('common.create') }}</a></li>
                            @endif
                        </ul>
                    </div>
                @endif
            @endif
            @if (permissionCheck('review.seller.index') || permissionCheck('review.product.index'))
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('review.review') }}</h4>
                </div>
                <ul>
                    @if (permissionCheck('review.seller.index'))
                    <li><a href="{{route('review.seller.index')}}"> <i class="ti-plus"></i>{{ __('review.seller_review') }}</a></li>
                    @endif
                    @if (permissionCheck('review.product.index'))
                    <li><a href="{{ route('review.product.index') }}"><i class="ti-plus"></i>{{ __('review.product_review') }}</a></li>
                    @endif
                </ul>
            </div>
            @endif
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('common.refund') }}</h4>
                </div>
                <ul>
                    @if (permissionCheck('refund.total_refund_list'))
                    <li><a href="{{route('refund.total_refund_list')}}"> <i class="ti-plus"></i>{{ __('common.pending_request') }}</a></li>
                    @endif
                    @if(isModuleActive('MultiVendor'))
                    <li><a href="{{route('refund.my_refund_list')}}"><i class="ti-plus"></i>{{ __('common.list') }}</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <!-- popUp_single_wrap  -->
        @if (Auth::user()->role->type == "superadmin")
        <div class="popUp_single_wrap">
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('ticket.support_ticket') }}</h4>
                </div>
                <ul>
                    <li><a href="{{route('ticket.tickets.index')}}"> <i class="ti-plus"></i>{{ __('ticket.all_ticket') }}</a></li>
                    <li><a href="{{ route('ticket.my_ticket') }}"><i class="ti-plus"></i>{{ __('customer_panel.my_ticket') }}</a>
                    </li>
                </ul>
            </div>
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('common.flash_deals') }}</h4>
                </div>
                <ul>
                    <li><a href="{{ route('marketing.flash-deals') }}"> <i class="ti-plus"></i>{{ __('common.list') }}</a></li>
                    <li><a href="{{ route('marketing.flash-deals.create') }}"><i class="ti-plus"></i>{{ __('common.create') }}</a></li>
                </ul>
            </div>
            <div class="popup_single_item">
                <div class="main-title2 mb_10">
                    <h4 class="mb_15">{{ __('common.others') }}</h4>
                </div>
                <ul>
                    <li><a href="{{route('marketing.coupon')}}"> <i class="ti-plus"></i>{{ __('common.coupon') }}</a></li>
                    <li><a href="{{ route('marketing.new-user-zone.create') }}"><i class="ti-plus"></i>{{ __('common.user_zone') }}</a></li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</li>