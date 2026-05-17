<div class="dataTables_wrapper">
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table table-responsive">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th scope="col">{{__('common.brand')}}</th>
                        <th scope="col">{{__('common.user')}}</th>
                        <th scope="col">{{__('common.password')}}</th>
                        <th scope="col">{{__('general_settings.token')}}</th>
                        <th scope="col">{{__('common.status')}}</th>
                        <th scope="col">{{__('general_settings.api_connection')}}</th>
                        <th scope="col">{{__('common.action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brands as $brand)
                    <tr wire:key="brand-{{ $brand['brand_id'] }}" class="text-center">
                        <td>
                            <div class="d-flex align-items-center">
                                @if($brand['logo'])
                                    <div style="width: 50px; height: 50px; margin-right: 15px; border: 1px solid #efefef; border-radius: 5px; display: flex; align-items: center; justify-content: center; background: #fff; flex-shrink: 0;">
                                        <img src="{{ showImage($brand['logo']) }}" alt="{{ $brand['brand_name'] }}" style="max-width: 90%; max-height: 90%; object-fit: contain;">
                                    </div>
                                @else
                                    <div style="width: 50px; height: 50px; margin-right: 15px; background: #f4f4f4; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #888; font-weight: bold; flex-shrink: 0;">
                                        {{ substr($brand['brand_name'], 0, 1) }}
                                    </div>
                                @endif
                                <span style="font-weight: 600; font-size: 14px; color: #415094;">
                                    {{ $brand['brand_name'] }}
                                </span>
                            </div>
                        </td>
                        <td>{{ $brand['api_user'] ?? __('common.without_configuring') }}</td>
                        <td>
                            @if(empty($brand['api_password']))
                                <span class="text-muted">--</span>
                            @else
                                @if($visiblePasswordId === $brand['brand_id'])
                                    <span class="text-primary font-weight-bold" style="cursor: pointer;"
                                          wire:click="$set('visiblePasswordId', null)"
                                          title="Ocultar contraseña">
                                        {{ $brand['api_password'] }}
                                        <i class="fas fa-eye-slash ml-1 text-muted"></i>
                                    </span>
                                @else
                                    <span class="text-dark" style="cursor: pointer;"
                                          wire:click="$set('visiblePasswordId', {{ $brand['brand_id'] }})"
                                          title="Ver contraseña">
                                        ****** <i class="fas fa-eye ml-1 text-primary"></i>
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if(!empty($brand['api_token']))
                                <span class="badge_1" title="Token generado">{{__('common.generated')}}</span>
                            @else
                                <span class="badge_2">{{__('common.pending')}}</span>
                            @endif
                        </td>
                        <td>
                            <label class="switch_toggle" for="checkbox_{{ $brand['brand_id'] }}">
                                <input type="checkbox" class="check_status" id="checkbox_{{ $brand['brand_id'] }}"
                                    wire:click.prevent="attemptToggle({{ $brand['brand_id'] }})"
                                    onclick="$('#pre-loader').removeClass('d-none');"
                                    @if($brand['is_active']) checked @endif>
                                <div class="slider round"></div>
                            </label>
                        </td>
                        <td>
                            @if($brand['connection_status'] == 1)
                                <span class="badge_1">{{__('common.connected')}}</span>
                            @elseif(!empty($brand['api_user']))
                                <span style="background: #ffb100; color: #fff; padding: 5px 10px; border-radius: 15px; font-size: 10px; font-weight: 500;">
                                    {{__('common.without_test')}}
                                </span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown CRM_dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                    {{ __('common.select') }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <button href="#" class="dropdown-item"
                                        onclick="$('#pre-loader').removeClass('d-none');"
                                        wire:click.prevent="edit({{ $brand['brand_id'] }})">
                                        {{__('common.edit')}}
                                    </button>
                                    <button href="#"
                                    class="dropdown-item"
                                    onclick="$('#pre-loader').removeClass('d-none');"
                                    wire:click.prevent="testConnection({{ $brand['brand_id'] }})">
                                        {{__('general_settings.test_connection')}}
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('generalsetting::livewire.dian.partials.edit-modal')
    @include('generalsetting::livewire.dian.partials.confirm-modal')

</div>

@push('scripts')
<script>
    (function () {
        if (window._dianSettingsListenersRegistered) return;
        window._dianSettingsListenersRegistered = true;

        window.addEventListener('open-edit-modal', function () {
            var $modal = $('#editDianModal');
            $modal.one('shown.bs.modal', function () {
                $('#pre-loader').addClass('d-none');
            });
            $modal.modal('show');
        });

        window.addEventListener('close-edit-modal', function () {
            $('#editDianModal').modal('hide');
        });

        window.addEventListener('show-confirmation-modal', function () {
            $('#pre-loader').addClass('d-none');
            $('#confirmInactiveModal').modal('show');
        });

        window.addEventListener('close-confirm-modal', function () {
            $('#confirmInactiveModal').modal('hide');
        });

    })();
</script>
@endpush
