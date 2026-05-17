@extends('backEnd.master')

@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('modules/product/css/brand.css'))}}" />
@endsection

@section('mainContent')
<section class="admin-visitor-area up_st_admin_visitor">
    <div class="container-fluid white_box_30px mb_30">
        
        {{-- Pestañas de Navegación y Acciones Estilo ERP --}}
        <div class="row">
            <div class="col-md-12 mb-10">
                <div class="box_header_right">
                    <div class="pos_tab_btn justify-content-end">
                        <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2" role="tablist" id="main_settings_nav">
                            
                            {{-- ACCIONES DE MARCA (Movidas aquí según el estándar) --}}
                            @if (permissionCheck('product.brand.create'))
                                <li class="nav-item action_btn">
                                    <a class="nav-link action" href="{{route('product.brand.create')}}">
                                        <i class="ti-plus mr-2"></i>{{__('product.add_new_brand')}}
                                    </a>
                                </li>
                            @endif
                            @if (permissionCheck('product.bulk_brand_upload_page'))
                                <li class="nav-item action_btn">
                                    <a class="nav-link action" href="{{ route('product.bulk_brand_upload_page') }}">
                                        <i class="ti-upload mr-2"></i>{{ __('product.bulk_brand_upload') }}
                                    </a>
                                </li>
                            @endif
                            @if (permissionCheck('product.csv_brand_download'))
                                <li class="nav-item action_btn">
                                    <a class="nav-link action" href="{{ route('product.csv_brand_download') }}">
                                        <i class="ti-download mr-2"></i>{{ __('product.brand_csv') }}
                                    </a>
                                </li>
                            @endif

                            {{-- ESPACIADOR --}}
                            <li class="nav-item flex-grow-1"></li>

                            {{-- NAVEGACIÓN (Pestaña Principal) --}}
                            <li class="nav-item">
                                <a class="nav-link active show" href="javascript:void(0)" role="tab" data-toggle="tab" aria-selected="true">
                                    {{ __('product.brand_list') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-12">
                {{-- Encabezado Principal y Buscador --}}
                <div class="box_header common_table_header">
                    <div class="main-title d-md-flex">
                        <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{ __('product.brand_list') }}</h3>
                    </div>
                </div>

                <div class="QA_section QA_section_heading_custom check_box_table">
                    <div class="QA_table ">
                        <div class="table-responsive ign-scrollbar">
                            <table class="table Crm_table_active3">
                                <thead>
                                <tr>
                                    <th width="20%" scope="col">{{ __('common.name') }}</th>
                                    <th width="20%" scope="col">{{ __('common.logo') }}</th>
                                    <th width="15%" scope="col">{{ __('common.status') }}</th>
                                    <th width="15%" scope="col">{{ __('common.featured') }}</th>
                                    <th width="20%" scope="col" class="text-right">{{ __('common.action') }}</th>
                                </tr>
                                </thead>
                                <tbody id="tablecontents">
                                @foreach($brands as $key => $brand)
                                    <tr class="row1 text-center" data-id="{{ $brand->id }}">
                                        <td><strong>{{ $brand->name }}</strong></td>
                                        <td>
                                            <div class="logo_div">
                                                @if ($brand->logo != null)
                                                    <img src="{{showImage($brand->logo)}}" alt="{{$brand->name}}" style="max-height: 40px; border-radius: 4px;">
                                                @else
                                                    <img src="{{showImage('frontend/default/img/brand_image.png')}}" alt="{{$brand->name}}" style="max-height: 40px; border-radius: 4px;">
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <label class="switch_toggle" for="checkbox{{ $brand->id }}">
                                                <input type="checkbox" id="checkbox{{ $brand->id }}" @if ($brand->status == 1) checked @endif @if (permissionCheck('product.brand.update_active_status')) value="{{ $brand->id }}" data-id="{{$brand->id}}" class="status_change" @else disabled @endif>
                                                <div class="slider round"></div>
                                            </label>
                                        </td>
                                        <td>
                                            <label class="switch_toggle" for="active_checkbox{{ $brand->id }}">
                                                <input type="checkbox" id="active_checkbox{{ $brand->id }}" @if ($brand->featured == 1) checked @endif @if (permissionCheck('product.brand.update_active_feature')) value="{{ $brand->id }}" data-id="{{$brand->id}}" class="featured_change" @else disabled @endif>
                                                <div class="slider round"></div>
                                            </label>
                                        </td>
                                        <td class="text-right">
                                            <div class="dropdown CRM_dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    {{ __('common.select') }}
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                    <a data-id="{{$brand->id}}" class="dropdown-item copy_id">{{ __('product.Copy ID') }}</a>
                                                    @if (permissionCheck('product.brand.edit'))
                                                        <a class="dropdown-item edit_brand" href="{{ route('product.brand.edit', $brand->id) }}">{{__('common.edit')}}</a>
                                                    @endif
                                                    @if (permissionCheck('product.brand.destroy'))
                                                        <a class="dropdown-item delete_brand" data-value="{{route('product.brand.destroy', $brand->id)}}">{{__('common.delete')}}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row justify-content-center mt-5">
                        {{ $brands->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('backEnd.partials.delete_modal',['item_name' => __('product.brand')])
@endsection

@push('scripts')
<script type="text/javascript">
    (function($){
        "use strict";
        $(document).ready(function(){
            $(document).on('change', '.status_change', function(event){
                let id = $(this).data('id');
                let status = 0;
                if($(this).prop('checked')){
                    status = 1;
                }
                else{
                    status = 0;
                }
                $.post("{{ route('product.brand.update_active_status') }}", {_token:'{{ csrf_token() }}', id:id, status:status}, function(data){
                    if(data == 1){
                        toastr.success("{{__('common.updated_successfully')}}","{{__('common.success')}}");
                    }
                    else{
                        toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                    }
                })
                .fail(function(response) {
                if(response.responseJSON.error){
                        toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                        $('#pre-loader').addClass('d-none');
                        return false;
                    }

                });
            });
            $(document).on('change', '.featured_change', function(event){
                let id = $(this).data('id');
                let featured = 0;

                if(this.checked){
                    featured = 1;
                }
                else{
                    featured = 0;
                }
                $.post('{{ route('product.brand.update_active_feature') }}', {_token:'{{ csrf_token() }}', id:id, featured:featured}, function(data){
                    if(data == 1){
                        toastr.success("{{__('common.updated_successfully')}}","{{__('common.success')}}");
                    }
                    else{
                        toastr.error("{{__('common.error_message')}}","{{__('common.error')}}");
                    }
                }).fail(function(response) {
                    if(response.responseJSON.error){
                        toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                        $('#pre-loader').addClass('d-none');
                        return false;
                    }
                });
            })
            $(function () {
                // Initialize Sortable
                $( "#tablecontents" ).sortable({
                    items: "tr",
                    cursor: 'move',
                    opacity: 0.6,
                    update: function() {
                        sendOrderToServer();
                    }
                });
                
                function sendOrderToServer() {
                    var order = [];
                    var token = $('meta[name="_token"]').attr('content');
                    $('tr.row1').each(function(index,element) {
                        order.push({
                        id: $(this).attr('data-id'),
                        position: index+1
                        });
                    });
                    $.ajax({
                        type: "POST",
                        url: "{{ route('product.abc') }}",
                            data: {
                        order: order,
                        _token: token
                        },
                        success: function(response) {
                        }
                    });
                }
            });
            $(document).on('click', '.loadmore_btn', function () {
                var totalCurrentResult = $('#tablecontents tr').length;
                $("#pre-loader").removeClass('d-none');
                $.ajax({
                    url: "{{ route('product.load_more_brands') }}",
                    method: "POST",
                    data: {
                        skip: totalCurrentResult,
                        _token: "{{csrf_token()}}",
                    },
                    success: function (response) {
                        $("#tablecontents").append(response.brands);
                        $("#pre-loader").addClass('d-none');
                    },
                    error: function(response) {
                        $("#pre-loader").addClass('d-none');
                    }
                })
            });
            $(document).on('click', '.delete_brand', function(event){
                event.preventDefault();
                let route = $(this).data('value');
                confirm_modal(route);
            });
        });
    })(jQuery);
</script>
@endpush