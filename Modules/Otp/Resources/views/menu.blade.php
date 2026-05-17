    @php
        $otp = false;

        if(request()->is('otp/*'))
        {
            $otp = true;
        }
    @endphp
    @if(permissionCheck('otp.configuration'))
        <li class="{{ $otp ?'mm-active' : '' }} sortable_li" data-position="{{ menuManagerCheck(1,36)->position }}" data-status="{{ menuManagerCheck(1,36)->status }}">
            <a href="javascript:;" class="has-arrow" aria-expanded="{{ $otp ? 'true' : 'false' }}">
                <div class="nav_icon_small">
                    <span class="fas fa-users"></span>
                </div>
                <div class="nav_title">
                    <span>{{ __('otp.otp') }}</span>
                    @if (config('app.sync'))
                        <span class="demo_addons">Addon</span>
                    @endif
                </div>
            </a>
            <ul>
                @if(permissionCheck('opt.configuration_update') && menuManagerCheck(2,36,'opt.configuration_update')->status == 1)
                    <li data-position="{{ menuManagerCheck(2,36,'opt.configuration_update')->position }}">
                        <a href="{{ route('otp.configuration') }}" class="@if(request()->is('otp/configuration*')) active @endif">{{ __('otp.otp') }} {{ __('common.configuration') }}</a>
                    </li>
                @endif
            </ul>
        </li>
    @endif