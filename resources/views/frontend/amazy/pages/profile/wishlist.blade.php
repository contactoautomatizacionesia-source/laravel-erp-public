@extends('frontend.amazy.pages.profile.layouts._profile_layout')

@push('styles')
    <style>
        .payment_modal_wallet.style2 {
            padding:20px 45px 10px 25px;
        }
        .modal-footer .amaz_primary_btn3{
            margin-left: -15px;
        }
    </style>
@endpush

@section('profile_content')
    <div id="productShow">
        @include('frontend.amazy.pages.profile.partials._wishlist_with_paginate')
    </div>
    
    @include('frontend.amazy.partials._delete_modal_for_ajax',['item_name' => __('defaultTheme.wishlist_product'),'form_id' => 'wishlist_delete_form','modal_id' => 'wishlist_delete_modal'])
@endsection

@push('scripts')
    <script>
        (function($){
            "use strict";

            $(document).ready(function(){
                $(document).on('click', '.page_link', function(event) {
                    event.preventDefault();
                    let page = $(this).attr('href').split('page=')[1];
                    fetch_filter_data(page);
                });

                function fetch_filter_data(page){
                    $('#pre-loader').show();
                    var paginate = $('#paginate_by').val();
                    var sort_by = $('#product_short_list').val();
                    
                    let params = new URLSearchParams({page: page});
                    if (sort_by) {
                        params.append('sort_by', sort_by);
                    }
                    if (paginate) {
                        params.append('paginate', paginate);
                    }
                    var url = "{{route('frontend.my-wishlist.paginate-data')}}?" + params.toString();
                    
                    if(page != 'undefined'){
                        $.ajax({
                            url:url,
                            success:function(data)
                            {
                                $('#productShow').html(data);
                                $('#product_short_list').niceSelect();
                                $('#paginate_by').niceSelect();
                                $('#pre-loader').hide();
                            }
                        });
                    }else{
                        toastr.warning("{{__('common.error_message')}}");
                    }
                }

                $(document).on('click', '.removeWishlist', function(event){
                    event.preventDefault();
                    let id = $(this).data('id');
                    $('#delete_item_id').val(id);
                    $('#wishlist_delete_modal').modal('show');
                });

                $(document).on('click', '#wishlist_delete_form', function(event){
                    event.preventDefault();
                    $('#pre-loader').show();
                    $('#wishlist_delete_modal').modal('hide');
                    let formData = new FormData();
                    formData.append('_token', "{{ csrf_token() }}");
                    formData.append('id', $('#delete_item_id').val());
                    formData.append('sort_by', $('#product_short_list').val());
                    formData.append('paginate', $('#paginate_by').val());
                    $.ajax({
                        url: "{{ route('frontend.wishlist.remove') }}",
                        type: "POST",
                        cache: false,
                        contentType: false,
                        processData: false,
                        data: formData,
                        success: function(response) {
                            toastr.success("{{__('common.deleted_successfully')}}","{{__('common.success')}}");
                            $('#productShow').html(response.page);
                            $('#product_short_list').niceSelect();
                            $('#paginate_by').niceSelect();
                            $('#pre-loader').hide();
                            $('.wishlist_count').text(response.totalItems);
                        },
                        error: function(response) {
                            toastr.error('{{__("common.error_message")}}', "{{__('common.error')}}");
                            $('#pre-loader').hide();
                        }
                    });
                });

                $(document).on('change', '.amaz_select4', function(event){
                    getFilterUpdateByIndex();
                });

                function getFilterUpdateByIndex(){
                    let paginate = $('#paginate_by').val();
                    let sort_by = $('#product_short_list').val();
                    $('#pre-loader').show();
                    $.get("{{ route('frontend.my-wishlist.paginate-data') }}", {sort_by:sort_by, paginate:paginate}, function(data){
                        $('#productShow').html(data);
                        $('#product_short_list').niceSelect();
                        $('#paginate_by').niceSelect();
                        $('#pre-loader').hide();
                    });
                }

                $(document).on('click', ".add_to_cart_gift_thumnail", function(event) {
                    event.preventDefault();
                    let prod_info = $(this).data('prod_info');
                    addToCart($(this).attr('data-gift-card-id'),$(this).attr('data-seller'),1,$(this).attr('data-base-price'),1,'gift_card', prod_info);
                });
            });
        })(jQuery);
    </script>
@endpush

@include(theme('partials.add_to_cart_script'))
@include(theme('partials.add_to_compare_script'))