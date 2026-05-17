@php
    $langs = app('langs');
    $locale = app('general_setting')->language_code;
    if(\Session::has('locale')){
        $locale = \Session::get('locale');
    }
    if(auth()->check()){
        $locale = auth()->user()->lang_code;
    }
@endphp
<div class="container-fluid no-gutters px-0 ign-admin-top-menu">
    <div class="row pt-lg-3 pt-2 pb-lg-4 pb-2 align-items-center header_iner mx-0">
        <div class="col-xl-5 col-6 px-0">
            <div class="d-flex align-items-end">
                <div id="sidebarCollapse" class="sidebar_icon  d-lg-none">
                    <i class="ti-menu fs-20"></i>
                </div>
                <div class="small_logo_crm d-lg-none">
                    <a href="{{url('/login')}}"> <img src="{{showImage(app('general_setting')->logo)}}" alt="{{app('general_setting')->company_name}}" title="{{app('general_setting')->company_name}}"></a>
                </div>
                
            </div>
            <div class="search-admin-wrap d-lg-flex d-none align-items-center pl-4 pr-3 py-3">
                <div class="collaspe_icon open_miniSide">
                    <i class="ti-menu fs-20 text-white"></i>
                </div>
                <div class="flex-1">
                    <div class="serach_field-area ml-xl-50">
                        <div class="search_inner">
                            <form action="#" class="mb-0">
                                <div class="search_field">
                                    <input type="text" class="" autocomplete="off" placeholder="{{__('common.search')}}" id="search" onkeyup="showResult(this.value)">
                                </div>
                                <button type="submit"><i class="ti-search"></i></button>
                            </form>
                        </div>
                        <div id="livesearch" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-7 col-6 px-0">
            <div class="d-flex aling-items-center justify-content-end pr-2">
                <div class="select_style d-xl-flex d-none align-items-center">
                    <a target="_blank" class="text-black mx-2" href="{{url('/')}}">{{__('common.website')}} <i class="fa fa-external-link text-color"></i></a>
                    <select class="nice_Select bgLess mb-0" id="language_select">
                        @foreach($langs as $lang)
                            <option @selected($locale == $lang->code) value="{{ $lang->code }}">{{ $lang->native }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="header_notification_warp d-flex align-items-center mx-4">
                    @if(auth()->user()->role->type != 'affiliate' && auth()->user()->role->type != 'customer')
                    <x-backEnd.admin_quick_actions />
                    @endif
                    <li class="scroll_notification_list position-relative mt-2">
                        <a class="pulse theme_color bell_notification_clicker" href="javascript:void(0)">
                            <!-- bell   -->
                            <i class="fa fa-bell fs-20 mx-2"></i>
                            <!--/ bell   -->
                            @if (count($notifications) > 0)
                            <span class="notification_count">{{getNumberTranslate(count($notifications))}} </span>
                            <span class="pulse-ring notification_count_pulse"></span>
                            @endif
                        </a>
                        <!-- Menu_NOtification_Wrap  -->
                        <link rel="stylesheet" href="{{ asset(asset_path('backend/css/ign_admin_menu_styles.css')) }}" />
                        <div class="Menu_NOtification_Wrap">
                            <div class="notification_Header">
                                <h4>{{ __('common.notifications') }}</h4>
                            </div>
                            <div class="Notification_body ign-scrollbar">
                                @forelse ($notifications as $notification)
                                <div class="single_notify">
                                    <div class="notify_content">
                                        <a href="javascript:void(0)" class="notification-content notification_read_btn"
                                           data-id="{{$notification->id}}"
                                           data-url="{{url($notification->url)}}">
                                           <i class="ti-bell mr-2" style="font-size: 12px; color: var(--toolkit_corporative-orange-color);"></i>
                                           {{ucfirst($notification->title)}}
                                        </a>
                                    </div>
                                </div>
                                @empty
                                <div class="single_notify">
                                    <div class="notify_content text-center py-3">
                                        <p class="mb-0 text-muted">{{__('common.no_notification_found') }}.</p>
                                    </div>
                                </div>
                                @endforelse
                            </div>
                            <div class="nofity_footer">
                                <div class="submit_button text-center">
                                    <a href="{{route('user.notificationsetting.index')}}"
                                        class="primary-btn radius_30px text_white fix-gr-bg">
                                        <i class="ti-settings"></i> {{ __('common.setting') }}
                                    </a>
                                    @if (count($notifications) > 0)
                                        <div class="d-flex gap-2 w-100">
                                            <a href="{{route('frontend.mark_as_read')}}"
                                                class="primary-btn radius_30px text_white fix-gr-bg flex-fill">
                                                <i class="ti-check-box"></i> {{ __('common.read_all') }}
                                            </a>
                                            <a href="{{route('frontend.notifications')}}"
                                                class="primary-btn radius_30px text_white fix-gr-bg flex-fill">
                                                <i class="ti-view-list"></i> {{ __('common.view') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!--/ Menu_NOtification_Wrap  -->
                    </li>
                </div>
                <div class="profile_info d-flex aling-items-center">
                    <div class="mx-3 d-lg-block d-none">
                        <p> 
                            @if(auth()->user()->role->type == 'superadmin') {{__('hr.super_admin')}}
                            @elseif(auth()->user()->role->type == 'admin') {{__('hr.admin')}}
                            @elseif(auth()->user()->role->type == 'seller') {{__('hr.seller')}}
                            @elseif(auth()->user()->role->type == 'staff') {{__('hr.staff')}} @endif!</p>
                        <h5 class="text-black">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h5>
                    </div>
                    <div class="user_avatar_div">
                        <img id="profile_pic" src="{{showImage(auth()->user()->avatar !=null?auth()->user()->avatar:'backend/img/avatar.png')}}" alt="">
                    </div>

                    <div class="profile_info_iner">
                        <p> {{__('common.welcome')}}
                            @if(auth()->user()->role->type == 'superadmin') {{__('hr.super_admin')}}
                            @elseif(auth()->user()->role->type == 'admin') {{__('hr.admin')}}
                            @elseif(auth()->user()->role->type == 'seller') {{__('hr.seller')}}
                            @elseif(auth()->user()->role->type == 'staff') {{__('hr.staff')}} @endif!</p>
                        <h5>{{ auth()->user()->first_name }}</h5>
                        <div class="profile_info_details">
                            @if(auth()->user()->role->type == 'superadmin' || auth()->user()->role->type == 'admin' || auth()->user()->role->type == 'staff')
                            <a href="{{url('/profile')}}">{{__('customer_panel.my_profile') }}<i class="ti-user"></i></a>
                            @if(permissionCheck('company_info'))
                            <a href="{{url('/generalsetting/company-info')}}">{{__('customer_panel.company_info') }}<i class="ti-user"></i></a>
                            @endif
                            @if(permissionCheck('generalsetting.index'))
                            <a href="{{url('/generalsetting')}}">{{ __('common.settings') }}<i class="ti-settings"></i></a>
                            @endif
                            @endif
                            @if(auth()->user()->role->type == 'seller')
                            <a href="{{url('/profile')}}">{{ __('customer_panel.customer_profile') }}<i class="ti-user"></i></a>
                            <a href="{{url('/seller/profile')}}">{{ __('seller.seller_profile') }}<i class="ti-user"></i></a>
                            <a href="{{url('/seller/setting')}}">{{ __('common.setting') }}<i class="ti-user"></i></a>
                            @endif
                            @if(auth()->user()->role->type == 'customer')
                                <a href="{{url('/profile/dashboard')}}">{{ __('common.dashboard') }}<i class="ti-dashboard"></i></a>
                            @endif
                            @if (auth()->user()->secret_login)
                            <a href="{{ route('secret_logout') }}">{{ __('common.log_out') }}<i class="ti-shift-left"></i></a>
                            @else
                            <a href="{{ route('logout') }}" class="log_out" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('common.log_out') }}<i class="ti-shift-left"></i>
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
</div>
