<!-- sidebar part here -->
<nav id="sidebar" class="sidebar">
    <div class="sidebar-header update_sidebar">
        <a class="large_logo" href="{{ url('/login') }}">
            <img src="{{showImage(app('general_setting')->logo)}}">
        </a>
        <a class="mini_logo" href="{{ url('/login') }}">
            <img src="{{showImage(app('general_setting')->favicon)}}">
        </a>
        <a id="close_sidebar" class="d-lg-none">
            <i class="ti-close"></i>
        </a>
    </div>
    <ul id="sidebar_menu">
        <li>
            <a href="{{route('affiliate.my_affiliate.index')}}" class="{{request()->routeIs('affiliate.my_affiliate.index') ? 'active' : ''}}"> {{__('affiliate.My Affiliate')}}</a>
        </li>
    </ul>
</nav>
<!-- sidebar part end -->
