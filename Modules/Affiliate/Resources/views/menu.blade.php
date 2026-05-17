@php
    $affiliate= false;

    if(request()->is('affiliate/*'))
    {
        $affiliate = true;
    }
@endphp
<li class="{{ $affiliate ?'mm-active' : '' }} sortable_li" data-position="{{ menuManagerCheck(1,37)->position }}" data-status="{{ menuManagerCheck(1,37)->status }}">
    <a href="javascript:;" class="has-arrow" aria-expanded="{{ $affiliate ? 'true' : 'false' }}">
        <div class="nav_icon_small">
            <span class="fas fa-money-bill"></span>
        </div>
        <div class="nav_title">
            <span>{{__('affiliate.Affiliate')}}</span>
            @if (config('app.sync'))
                <span class="demo_addons">Addon</span>
            @endif
        </div>
    </a>
    <ul>
        @if(permissionCheck('affiliate.my_affiliate.index') && menuManagerCheck(2,37,'affiliate.my_affiliate.index')->status == 1)
            <li data-position="{{ menuManagerCheck(2,37,'affiliate.my_affiliate.index')->position }}">
                <a href="{{route('affiliate.my_affiliate.index')}}" class="{{request()->routeIs('affiliate.my_affiliate.index') ? 'active' : ''}}"> {{__('affiliate.My Affiliate')}}</a>
            </li>
        @endif
        @if(permissionCheck('affiliate.pending_withdraw') && menuManagerCheck(2,37,'affiliate.pending_withdraw')->status == 1)
            <li data-position="{{ menuManagerCheck(2,37,'affiliate.pending_withdraw')->position }}">
                <a href="{{route('affiliate.pending_withdraw')}}" class="{{request()->routeIs('affiliate.pending_withdraw') ? 'active' : ''}}"> {{__('affiliate.Pending Withdrawn')}}</a>
            </li>
        @endif
        @if(permissionCheck('affiliate.complete_withdraw') && menuManagerCheck(2,37,'affiliate.complete_withdraw')->status == 1)
            <li data-position="{{ menuManagerCheck(2,37,'affiliate.complete_withdraw')->position }}">
                <a href="{{route('affiliate.complete_withdraw')}}" class="{{request()->routeIs('affiliate.complete_withdraw') ? 'active' : ''}}"> {{__('affiliate.Complete Withdrawn')}}</a>
            </li>
        @endif
        @if(permissionCheck('affiliate.users.index') && menuManagerCheck(2,37,'affiliate.users.index')->status == 1)
            <li data-position="{{ menuManagerCheck(2,37,'affiliate.users.index')->position }}">
                <a href="{{route('affiliate.users.index')}}" class="{{request()->routeIs('affiliate.users.index') ? 'active' : ''}}"> {{__('affiliate.Users')}}</a>
            </li>
        @endif
        @if(permissionCheck('affiliate.configurations.update') && menuManagerCheck(2,37,'affiliate.configurations.update')->status == 1)
            <li data-position="{{ menuManagerCheck(2,37,'affiliate.configurations.update')->position }}">
                <a href="{{route('affiliate.configurations.index')}}" class="{{request()->routeIs('affiliate.configurations.index') ? 'active' : ''}}"> {{__('affiliate.Configurations')}}</a>
            </li>
        @endif
    </ul>
</li>

