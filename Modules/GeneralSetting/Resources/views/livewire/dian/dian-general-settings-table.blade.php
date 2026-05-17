<div class="dataTables_wrapper">
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table table-responsive">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th scope="col">{{__('common.brand')}}</th>
                        <th scope="col">{{__('general_settings.resolution_number')}}</th>
                        <th scope="col">{{__('general_settings.resolution_date')}}</th>
                        <th scope="col">{{__('general_settings.invoice_number_from')}}</th>
                        <th scope="col">{{__('general_settings.invoice_number_to')}}</th>
                        <th scope="col">{{__('common.action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brands as $brand)
                    <tr wire:key="brand-{{ $brand['brand_id'] }}">
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
                                    {{ $brand['brand_name'] ?? __('common.without_configuring') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span style="font-size: 14px;">
                                    {{ $brand['resolution_number'] ?? __('common.without_configuring') }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span style="font-size: 14px;">
                                    {{ $brand['resolution_date'] ?? "--" }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span style="font-size: 14px;">
                                    {{ $brand['invoice_number_from'] ?? "--" }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span style="font-size: 14px;">
                                    {{ $brand['invoice_number_to'] ?? "--" }}
                                </span>
                            </div>
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
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('generalsetting::livewire.dian.partials.edit-general-settings-modal')
</div>

@push('scripts')
<script>
    (function () {
        if (window._dianGeneralSettingsListenersRegistered) return;
        window._dianGeneralSettingsListenersRegistered = true;

        window.addEventListener('open-edit-general-settings-modal', function () {
            var $modal = $('#editDianGeneralSettingsModal');
            $modal.one('shown.bs.modal', function () {
                $('#pre-loader').addClass('d-none');
            });
            $modal.modal('show');
        });

        window.addEventListener('close-edit-modal', function () {
            $('#editDianGeneralSettingsModal').modal('hide');
        });

    })();
</script>
@endpush
