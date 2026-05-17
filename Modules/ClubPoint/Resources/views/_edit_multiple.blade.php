@extends('backEnd.master')
@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('backend/css/backend_page_css/staff_create.css'))}}" />
<style>
    .swal-button--confirm-orange {
        background-color: var(--toolkit_corporative-orange-color) !important;
        border-color: var(--toolkit_corporative-orange-color) !important;
    }
    .swal-button--confirm-orange:hover {
        background-color: var(--btn-orange-hover) !important;
        border-color: var(--btn-orange-hover) !important;
    }
</style>
@endsection

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="box_header">
                    <div class="main-title d-md-flex align-items-center">
                        <x-backEnd.back-button :text="false" />
                        <h3 class="mb-0 mr-30">{{ __('common.update') }} {{ __('common.Set Point for Product') }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="white_box_30px box_shadow_white">
                    <h3 class="mb-3 fs-20 font-weight-bold">{{$clubpoint->product_name}}</h3>
                    <form action="{{route('clubpoint.multiple.update', $clubpoint->id) }}" method="POST" id="update-points-form">
                        @csrf
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="primary_input mb-25">
                                    <label class="primary_input_label" for="">{{ __('common.Set Point') }} <span
                                            class="text-danger">*</span></label>
                                    <input name="multiple" class="primary_input_field name"
                                        placeholder="{{ __('common.number') }}" type="number"
                                        value="{{$clubpoint->club_point}}" step="0.01">
                                    <span class="text-danger">{{$errors->first('multiple')}}</span>
                                </div>
                                <div class="primary_input mb-25 d-flex aling-items-enter gap-2">
                                    <input type="checkbox" name="update_product_price" id="update_product_price">
                                    <label for="update_product_price" class="mb-0">{{__('common.update_product_price')}}</label>
                                    
                                </div>
                                <div>
                                    <div class="alert alert-warning" role="alert">
                                        {{__('common.alert_update_product_points')}}
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center pt_20">
                                    <button type="submit" class=" btn-toolkit btn-primary btn-icon"
                                        id="save_button_parent"><i class="ti-check"></i>{{ __('common.update') }}</button>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-card w-md-50 mt-3 mx-auto text-center">
                                    <h3 class="mb-3 fs-20">{{__('common.estimated_price_sale')}}</h3>
                                    <h2 class="mb-3 fs-20 text-black" id="estimated_price">{{single_price(club_point_price($clubpoint->club_point))}}</h2>
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
    const walletPoint = {{ \Modules\ClubPoint\Entities\ClubPointWallet::first()?->wallet_point ?? 0 }};
    const currencySymbol = '{{ getCurrency() }}';

    $('input[name="multiple"]').on('input', function () {
        const points = parseFloat($(this).val()) || 0;
        const price = points * walletPoint;
        $('#estimated_price').text(currencySymbol + number_format(price));
    });

    function number_format(n) {
        return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Modal de confirmación de cambio
    $(document).on("click", "#save_button_parent", function(e){
        e.preventDefault();
        swal({
            title: "{{ __('common.confirm_change_title') }}",
            text: "{{ __('common.confirm_change_text') }}",
            icon: "warning",
            buttons: {
                cancel: "{{ __('common.cancel') }}",
                confirm: {
                    text: "{{ __('common.confirm') }}",
                    value: true,
                    className: "swal-button--confirm-orange"
                }
            }
        })
        .then((willConfirm) => {
            if (willConfirm) {
                $('#update-points-form').submit();
            } else {
                console.log('No Confirma');
            }
        });
    });
</script>
@endpush

