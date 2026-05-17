@extends('backEnd.master')

@section('styles')
<link rel="stylesheet" href="{{asset(asset_path('backend/css/role_permission.css'))}}">
@endsection

@section('mainContent')
<section class="admin-visitor-area up_admin_visitor up_st_admin_visitor ign-role-wrapper">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-3">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-title">
                            <h3 class="mb-30">
                                @if(isset($role))
                                    @lang('common.edit')
                                @else
                                    @lang('common.add')
                                @endif
                                    @lang('hr.role')
                            </h3>
                        </div>
                        @if(isset($role))
                            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'url' => route('permission.roles.update',$role->id),'method' => 'PUT']) }}
                        @else
                            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'permission.roles.store', 'method' => 'POST']) }}
                        @endif
                        <div class="white-box" style="padding: 25px; border-radius: 12px; border: 1px solid var(--border-light); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                            <div class="add-visitor">
                                <div class="row">
                                    <div class="col-lg-12">
                                        @if(session()->has('message-success'))
                                        <div class="alert alert-success">
                                            {{ session()->get('message-success') }}
                                        </div>
                                        @elseif(session()->has('message-danger'))
                                        <div class="alert alert-danger">
                                            {{ session()->get('message-danger') }}
                                        </div>
                                        @endif
                                        <div class="input-effect">
                                            <label for="" style="color: var(--toolkit_premium-dark-color); font-weight: 600;">@lang('common.name') <span><span class="text-danger">*</span></span></label>
                                            <input class="primary_input_field form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="name"
                                                type="text" name="name" autocomplete="off" value="{{isset($role)? @$role->name: ''}}"
                                                {{-- BLOQUEO DE EDICIÓN PARA ROLES BASE --}}
                                                @if(isset($role) && ($role->id <= 3 || in_array($role->type, ['superadmin', 'admin', 'seller'])))
                                                    readonly
                                                @endif>
                                            {{-- Contenedor para el error de duplicidad Ajax --}}
                                            <span id="name_error" class="text-danger" style="display:none; font-size: 11px;"></span>
                                            <input type="hidden" name="id" id="role_id" value="{{isset($role)? @$role->id: ''}}">

                                            @if ($errors->has('name'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                                @php
                                    $tooltip = "";
                                @endphp
                                <div class="row mt-4">
                                    <div class="col-lg-12 text-center">
                                        @if(permissionCheck('permission.roles.edit') || permissionCheck('permission.roles.store'))
                                            <button class=" btn-toolkit btn-primary btn-icon" data-toggle="tooltip" title="{{@$tooltip}}">
                                                <span class="ti-check"></span>
                                                {{!isset($role)? __('common.save') : __('common.update')}}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="white_box_30px mb_30" style="padding: 25px; border-radius: 12px; border: 1px solid var(--border-light); box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <div class="row">
                        <div class="col-lg-4 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-0">@lang('hr.role') @lang('common.list')</h3>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="QA_section QA_section_heading_custom check_box_table">
                                <div class="QA_table ">
                                    <div class="mt-30">
                                    <table class="table Crm_table_active3">
                                            <thead style="background-color: var(--toolkit_secondary-blue-color); color: white;">
                                            @include('backEnd.partials._alertMessagePageLevelAll')
                                                <tr>
                                                    <th>@lang('common.sl')</th>
                                                    <th>@lang('hr.role')</th>
                                                    <th>@lang('common.action')</th>
                                                    <th>@lang('common.number_of_related_users')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($roleList as $key => $role)
                                                    @if(!$role->module or isModuleActive($role->module))
                                                        <tr>
                                                            <td>{{ getNumberTranslate($key + 1 )}}</td>
                                                            <td style="font-weight: 500;">{{@$role->name}}</td>
                                                            <td>
                                                                <div class="dropdown CRM_dropdown">
                                                                    <button class="btn btn-secondary dropdown-toggle"  type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        {{__('common.select')}}
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2" style="border-radius: 8px; border: 1px solid var(--border-light); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                                                                        @if(permissionCheck('permission.permissions.index'))
                                                                        <a class="dropdown-item" type="button" href="{{ route('permission.permissions.index', [ 'id' => @$role->id])}}">{{__('hr.assign_permission')}}</a>
                                                                        @endif

                                                                        @if(permissionCheck('role_work_schedule.index'))
                                                                        <a class="dropdown-item" type="button" href="{{ route('role_work_schedule.index', [ 'id' => @$role->id])}}">{{__('hr.assign_schedule')}}</a>
                                                                        @endif

                                                                        @if(permissionCheck('permission.roles.edit'))
                                                                            <a href="{{ route('permission.roles.edit',$role->id) }}" class="dropdown-item" type="button">@lang('common.edit')</a>
                                                                        @endif

                                                                       {{-- VALIDACIÓN DE PROTECCIÓN --}}
                                                                        @if(permissionCheck('permission.roles.destroy'))
                                                                            @if($role->id > 3 && !in_array($role->type, ['superadmin', 'admin', 'seller']))
                                                                                <a href="" class="dropdown-item delete_role"  type="button" data-value="{{route('permission.roles.destroy',$role->id)}}">@lang('common.delete')</a>
                                                                            @else
                                                                                <span class="dropdown-item disabled text-muted" title="Protegido por el sistema">
                                                                                    <i class="ti-lock mr-2"></i> @lang('common.delete')
                                                                                </span>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td style="font-weight: 500;">
                                                                {{ $role->users_count }}
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
    </div>
    {{-- Error modal message --}}
    @include('backEnd.partials.delete_modal',['item_name' => __('hr.role')])
</section>
@endsection

@push('scripts')
    <script>
        (function($){
            "use script";
            $(document).ready(function(){
                let typingTimer;
                let doneTypingInterval = 500;
                
                $('#name').on('input', function() {
                    let name = $(this).val();
                    let id = $('#role_id').val();
                    let submitBtn = $('.primary-btn.btn-toolkit-primary');
                    let errorSpan = $('#name_error');
                    
                    submitBtn.attr('disabled', true).css('opacity', '0.5');
                    errorSpan.hide();
                    clearTimeout(typingTimer);

                    if (name.length > 2) {
                        typingTimer = setTimeout(function() {
                            $.post("{{ route('permission.roles.check_duplicate') }}",
                                {
                                    _token: "{{ csrf_token() }}",
                                    name: name,
                                    id: id
                                },
                                function(data) {
                                    if (data.exists) {
                                        errorSpan.text(data.message).show();
                                        submitBtn.attr('disabled', true).css('opacity', '0.5');
                                        $('#name').addClass('is-invalid');
                                    } else {
                                        errorSpan.hide();
                                        submitBtn.attr('disabled', false).css('opacity', '1');
                                        $('#name').removeClass('is-invalid').addClass('is-valid');
                                    }
                                }
                            ).fail(function() {
                                submitBtn.attr('disabled', true);
                            });
                        }, doneTypingInterval);
                    } else {
                        $('#name').removeClass('is-valid is-invalid');
                        submitBtn.attr('disabled', true).css('opacity', '0.5');
                    }
                });

                $(document).on('click', '.delete_role', function(event){
                    event.preventDefault();
                    let route = $(this).data('value');
                    confirm_modal(route);
                });
            });

        })(jQuery);
    </script>
@endpush
