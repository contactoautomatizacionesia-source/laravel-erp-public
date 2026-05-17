@extends('backEnd.master')
@php
    $count = $audit->inventoryCount;
@endphp
@section('mainContent')
<x-admin.section class="ign-audit-list">
    <div class="row">
        <div class="col-12">
            <div class="box_header common_table_header ">
                <div class="main-title d-md-flex align-items-center ">
                    <x-backEnd.back-button :text="false" />
                    <h3 class="mb-0">{{ __('inventorycount::messages.audit_detail') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 px-2">

            <div class="form-card">
                <h3 class="mb-2">{{ __('inventorycount::messages.info_count') }}</h3>
                <div class="row mb-4">
                        <div class="col-12 mb-2">
                            <span class="primary_input_label">{{ __('inventorycount::messages.count_code') }}</span>
                            <p class="font-weight-bold"><a href="{{ route('inventory_count.show', $count->id) }}">{{ $count->count_code }} <i class="ti-arrow-top-right"></i></a></p>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="primary_input_label">{{ __('inventorycount::messages.cost_center') }}</span>
                            <p class="font-weight-bold">{{ optional($count->costCenter)->name ?? '-' }}</p>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="primary_input_label">{{ __('inventorycount::messages.asesor') }}</span>
                            <p class="font-weight-bold">{{ optional($count->user)->first_name }} {{ optional($count->user)->last_name }}</p>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="primary_input_label">{{ __('inventorycount::messages.auditor') }}</span>
                            <p class="font-weight-bold">{{ optional($audit->auditor)->first_name }} {{ optional($audit->auditor)->last_name }}</p>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="primary_input_label">{{ __('inventorycount::messages.audit_status') }}</span>
                            <p class="font-weight-bold">@include('inventorycount::audits.partials.status_badge', ['row' => $audit])</p>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="primary_input_label">{{ __('common.date') }}</span>
                            <p class="font-weight-bold" title="{{ optional($audit->created_at)->format('d/m/Y H:i') }}">{{ optional($audit->created_at)?->diffForHumans() ?? '-' }}</p>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="primary_input_label">{{ __('inventorycount::messages.audit_notes') }}</span>
                            <p class="">{{ $audit->notes }}</p>
                        </div>
                    </div>
            </div>
            
        </div>
        <div class="col-lg-8 px-2">
            <div class="form-card">
                <h3 class="mb-2">{{ __('inventorycount::messages.products_counted') }}</h3>
                @if($count->observation)
                    <div class="alert alert-light border mb-4">
                        <strong>{{ __('inventorycount::messages.observation') }}:</strong> {{ $count->observation }}
                    </div>
                @endif
                <div class="dataTables_wrapper">
                    <x-admin.table-container>
                        {{-- Tabla de detalles --}}
                        <div class="table-responsive ign-scrollbar">
                            <table class="table dataTable table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('common.sl') }}</th>
                                        <th>{{ __('inventorycount::messages.product') }}</th>
                                        <th class="text-center">{{ __('inventorycount::messages.physical_quantity') }}</th>
                                        <th class="text-center">{{ __('inventorycount::messages.system_stock') }}</th>
                                        <th class="text-center">{{ __('inventorycount::messages.difference') }}</th>
                                        <th>{{ __('inventorycount::messages.observation_type') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($count->details as $i => $detail)
                                    @php
                                        $name = json_decode($detail->product->product_name ?? '{}', true);
                                        $productName = is_array($name) ? ($name[app()->getLocale()] ?? reset($name) ?? '-') : ($detail->product->product_name ?? '-');
                                        $diff = $detail->physical_quantity - $detail->system_stock;
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $productName }}</td>
                                        <td class="text-center">{{ $detail->physical_quantity ?? '-' }}</td>
                                        <td class="text-center">{{ $detail->system_stock }}</td>
                                        <td class="text-center @if($diff < 0) text-danger @elseif($diff > 0) text-success @endif">
                                            {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                        </td>
                                        <td>{{ optional($detail->observationType)->name ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ __('common.no_data_found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-admin.table-container>
                </div>
            </div>
        </div>
    </div>

</x-admin.section>
@endsection
