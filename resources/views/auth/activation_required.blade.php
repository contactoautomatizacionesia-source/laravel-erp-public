@extends('frontend.amazy.auth.layouts.app')

@section('content')
<div class="container-fluid amazy_login_area bg-custom-login d-block">
    <div class="row justify-content-center">
        <div class="col-md-5 mx-auto mt-5">
            <div class="amazy_login_form">

                <div class="text-center mb-3">
                    <h4>{{ __('common.Email Verified Successfully') }}</h4>
                </div>

                <div class=" text-center">

                    <div class="alert alert-success" role="alert">
                        {{ __('common.Your email has been successfully verified.') }}
                    </div>

                    <p class="mb-3">
                        {{ __('common.Next step account activation') }}
                    </p>

                    <p class="mb-3">
                        {{ __('common.Admin activation message') }}
                    </p>

                    <p class="text-muted mb-0">
                        {{ __('common.Activation notification email') }}
                    </p>

                    <a href="/" class="mt-5 amaz_primary_btn style2 radius_5px  w-100 text-uppercase  text-center mb_25" >{{__('common.go_to_shop')}}</a>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection