@extends('backEnd.master')
@section('mainContent')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <section class="admin-visitor-area up_st_admin_visitor ign-staff-list">
        <div class="container-fluid p-0">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="white_box_30px mb_30">
                        <div class="box_header common_table_header">
                            <div class="pos_tab_btn w-100">
                            
                                <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-2">
                                    {{-- Botón Añadir (Este sí mantiene fix-gr-bg para destacar) --}}
                                    @if(permissionCheck('staffs.store'))
                                    <li class="nav-item">
                                        <a class="nav-item action" href="{{ route('staffs.create') }}">
                                            <i class="ti-plus mr-2"></i>{{ __('common.add_new') }} {{ __('hr.staff') }}
                                        </a>
                                    </li>
                                    @endif
                                    {{-- Botón Activos --}}
                                    <li class="nav-item">
                                        <a class="nav-link {{ request('status') != 'trashed' ? 'active' : '' }}" 
                                        href="{{ route('staffs.index') }}">
                                            {{ __('hr.active_staff') }}
                                        </a>
                                    </li>

                                    {{-- Botón Eliminados --}}
                                    <li class="nav-item">
                                        <a class="nav-item {{ request('status') == 'trashed' ? 'active' : '' }}" 
                                        href="{{ route('staffs.index', ['status' => 'trashed']) }}">
                                            <i class="ti-trash"></i> {{ __('hr.deleted_staff') }}
                                        </a>
                                    </li>
                                    
                                </ul>
                            </div>
                        </div>
                        <div>
                            <div class="box_header common_table_header mb-md-10">
                                <div class="main-title d-md-flex">
                                    <h3 class="mb-0 mr-30 mb_xs_15px mb_sm_20px">{{__('hr.staff_list')}}</h3>
                                </div>
                            </div>
                        
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table ">
                                    <!-- table-responsive -->
                                    <div class="table-responsiv">
                                        <table class="table Crm_table_active3">
                                            <thead>
                                            <tr>
                                                <th scope="col">{{ __('common.sl') }}</th>
                                                <th scope="col">{{ __('common.name') }}</th>

                                                <th scope="col">{{ __('common.email') }}</th>
                                                <th scope="col">{{ __('common.phone') }}</th>
                                                <th scope="col">{{ __('hr.role') }}</th>
                                                @if ($view_type == 'active')
                                                    <th scope="col">{{ __('common.status') }}</th>
                                                @endif
                                                <th scope="col">{{ __('hr.department') }}</th>
                                                <th scope="col">{{ __('hr.cost_center') }}</th>
                                                <th scope="col">{{ __('common.document_type') }}</th>
                                                <th scope="col">{{ __('common.document_number') }}</th>
                                                <th scope="col">{{ __('common.registered_date') }}</th>
                                                <th scope="col">{{ __('common.action') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($staffs as $key => $staff)
                                                @if ($staff->user != null)
                                                    <tr>
                                                        <th>{{ getNumberTranslate($key+1) }}</th>
                                                        <td><a href="{{ route('staffs.view', $staff->id) }}">{{ucwords( @$staff->user->getFullNameAttribute() ) }}</a></td>


                                                        <td><a href="mailto:{{ @$staff->user->email }}">{{ @$staff->user->email }}</a></td>
                                                        <td><a href="tel:{{ @$staff->phone }}">{{ @getNumberTranslate($staff->phone) }}</a></td>
                                                        <td>{{ @$staff->user->role->name }}</td>
                                                        @if ($view_type == 'active')
                                                            <td>
                                                                @if (@$staff->user->role_id != 1)
                                                                    <label class="switch_toggle" for="active_checkbox{{ $staff->id }}">
                                                                        <input class="update_status_staff" type="checkbox" id="active_checkbox{{ $staff->id }}" {{ permissionCheck('staffs.edit') ? '' : 'disabled' }} {{$staff->user->is_active == 1 ? 'checked' : ''}}
                                                                        value="{{ $staff->id }}" data-id="{{$staff->user->id}}">
                                                                        <div class="slider round"></div>
                                                                    </label>
                                                                @endif
                                                            </td>
                                                        @endif
                                                        <td>{{ @$staff->department->name }}</td>
                                                        <td>
                                                            @if($staff->user->costCenter)
                                                                {{ $staff->user->costCenter->code }} - {{ $staff->user->costCenter->name }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{ @$staff->typeDocument->name ?? '-' }}</td>
                                                        <td>{{ @$staff->document_number ?? '-' }}</td>
                                                    <td>{{ dateConvert($staff->created_at)  }}</td>

                                                        <td>
                                                            <!-- shortby  -->
                                                            <div class="dropdown CRM_dropdown">
                                                                <button class="btn btn-secondary dropdown-toggle" type="button"
                                                                        id="dropdownMenu2" data-toggle="dropdown"
                                                                        aria-haspopup="true"
                                                                        aria-expanded="false">
                                                                    {{ __('common.select') }}
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                                    @if(isset($view_type) && $view_type == 'trashed')
                                                                        <a data-value="{{route('staffs.restore', $staff->id)}}" class="dropdown-item restore_staff"
                                                                            type="button">
                                                                            {{__('common.restore')}}
                                                                        </a>
                                                                    @else
                                                                        @if(permissionCheck('staffs.view'))
                                                                        <a href="{{ route('staffs.view', $staff->id) }}" class="dropdown-item">{{__('common.view')}}</a>
                                                                        @endif

                                                                        @if(permissionCheck('staffs.edit'))
                                                                        <a href="{{ route('staffs.edit', $staff->id) }}" class="dropdown-item">{{__('common.edit')}}</a>
                                                                        @endif

                                                                        @if(permissionCheck('staffs.destroy'))
                                                                        <a data-value="{{route('staffs.destroy.get', $staff->user->id)}}" class="dropdown-item delete_staff">{{__('common.delete')}}</a>
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <!-- shortby  -->
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </section>
@include('backEnd.partials.delete_modal')
@include('backEnd.partials.restore_modal',['item_name' => __('common.staff')])
@include('backEnd.partials.update_staff_modal')
@endsection
@push('scripts')
    <script>
        (function($) {
        "use strict";
            $(document).ready(function(){
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('.Crm_table_active3')) {
                    let settingsOnReady = $('.Crm_table_active3').DataTable().settings()[0];

                    const hasLocalizedButtons = !!_.get(settingsOnReady, 'oLanguage.buttons.copyTitle');
                    if (!hasLocalizedButtons) {
                        $('.Crm_table_active3').DataTable().destroy();
                        initGlobalDataTable('.Crm_table_active3');
                    }
                }
                $(document).on('change','.payrollPayment', function(){
                    if(this.checked){
                        var status = 1;
                    }
                    else{
                        var status = 0;
                    }
                    $.post('{{ route('staffs.update_active_status') }}', {_token:'{{ csrf_token() }}', id:this.value, status:status}, function(data){
                        if(data.success){
                            toastr.success(data.success);
                        }
                        else{
                            toastr.error(data.error);
                        }
                    }).fail(function(response) {
                    if(response.responseJSON.error){
                            toastr.error(response.responseJSON.error ,"{{__('common.error')}}");
                            $('#pre-loader').addClass('d-none');
                            return false;
                        }

                    });
                });

                // Eliminación de usuario

                $(document).on('click', '.delete_staff', function(event){
                    event.preventDefault();
                    let value = $(this).data('value');
                    confirm_modal(value);
                });

                $(document).on('click', '.restore_staff', function(event){
                    event.preventDefault();
                    let value = $(this).data('value');
                    confirm_restore_modal(value);
                });

                // Proceso de cambio de estado
                let pendingStatusChange = null;
                let statusConfirmed = false;


                // Detectamos el cambio en el switch
                $(document).on('change', '.update_status_staff', function (e) {
                    e.preventDefault();

                    const checkbox = $(this);
                    const status = checkbox.prop('checked') ? 1 : 0;
                    const id = checkbox.data('id');

                    // 🔴 INACTIVAR → lanzar modal para pedir causal
                    if (status === 0) {
                        pendingStatusChange = { id, status, checkbox };

                        $('#status-causal').val('');
                        statusConfirmed = false;
                        $('#confirm-status-modal').modal('show');
                        return;
                    }


                    // 🟢 ACTIVAR → directo
                    submitStatusChange({ id, status, checkbox });
                });
                
                // Detectamos la confirmación desde la modal
                $('#confirm-status-btn').on('click', function () {

                    let causal = $('#status-causal').val().trim();

                    // Causal obligatoria
                    if (!causal) {
                        toastr.error('Debe ingresar una causal');
                        return;
                    }

                    statusConfirmed = true;
                    $('#confirm-status-modal').modal('hide');


                    // Llamado a función de actualizar estado
                    submitStatusChange({
                        id: pendingStatusChange.id,
                        status: pendingStatusChange.status,
                        checkbox: pendingStatusChange.checkbox,
                        causal: causal
                    });

                });

                function submitStatusChange({ id, status, checkbox, causal = null }) {

                    $('#pre-loader').removeClass('d-none');

                    let formData = new FormData();
                    formData.append('_token', "{{ csrf_token() }}");
                    formData.append('id', id);
                    formData.append('status', status);

                    if (causal !== null) {
                        formData.append('causal', causal);
                    }

                    $.ajax({
                        url: "{{ route('staffs.update_active_status') }}",
                        type: "POST",
                        contentType: false,
                        processData: false,
                        data: formData,

                        success: function () {
                            toastr.success("{{ __('common.updated_successfully') }}");
                            $('#pre-loader').addClass('d-none');
                        },

                        error: function (response) {
                            toastr.error("{{ __('common.error_message') }}");

                            // rollback visual
                            checkbox.prop('checked', !status);
                            pendingStatusChange = null;
                            statusConfirmed = false;

                            $('#pre-loader').addClass('d-none');
                        }
                    });
                }

                // rollback visual
                $('#confirm-status-modal').on('hidden.bs.modal', function () {
                    if (!statusConfirmed && pendingStatusChange) {
                        pendingStatusChange.checkbox.prop(
                            'checked',
                            !pendingStatusChange.status
                        );
                        pendingStatusChange = null;
                        statusConfirmed = false;
                    }
                });

            });
        })(jQuery);
    </script>
@endpush
