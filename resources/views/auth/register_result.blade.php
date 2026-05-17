@extends('frontend.amazy.auth.layouts.app')

@section('content')
<div class="container-fluid amazy_login_area bg-custom-login d-block">
    <div class="row justify-content-center">
        <div class="col-md-5 mx-auto mt-5">
            <div class="amazy_login_form py-3">

                <div class="text-center mb-3">
                    <h4>{{ __('common.Register Successfully') }}</h4>
                </div>

                <div class=" text-center">

                    <p class="mb-3">
                        {{ __('common.please') }}, {{ __('defaultTheme.verify_your_email') }}.
                    </p>

                </div>

            </div>
        </div>
    </div>
</div>
@endsection