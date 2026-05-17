@extends('backEnd.master')
@section('styles')
    <link rel="stylesheet" href="{{ asset(asset_path('modules/customer/css/show_details.css')) }}" />
    <link rel="stylesheet" href="{{ asset('/public/css/tree.css') }}" />
    <style>
        .white-color {
            color: #FFF !important;
        }

        .white_box {
            box-shadow: 0 8px 25px rgba(0, 0, 0, .08);
            border: 1px solid var(--border_color);
            margin-bottom: 50px;
        }
    </style>
@endsection
@section('mainContent')
    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid white_box_30px box_shadow_white">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="">
                        <div class="box_header">
                            <div class="main-title d-flex">
                                <x-backEnd.back-button :text="false"/>
                                <h3 class="mb-0 mr-30">{{ __('common.customer_profile') }}</h3>
                            </div>
                        </div>
                        <div class="row">
                            @php
                                $customerProfile = $customer->customerProfile;
                                $customerFinancialProfile = $customer->customerFinancialProfile;
                                $planChild = $customer->customerProfile?->planChild;
                                $plan = $planChild?->plan;
                            @endphp
                            
                            <div class="col-12">
                                <div class="form-card">
                                    <div class="row">
                                        <div class="col-md-auto">
                                            <div class="d-inline-block rounded-circle mb_10"
                                                style="border: 3px solid var(--border_color);">
                                                <img class="rounded-circle"
                                                    src="{{ showImage($customer->avatar ??'frontend/default/img/avatar.jpg') }}"
                                                    alt="avatar"
                                                    style="width: 110px; height: 110px; object-fit: cover;">
                                            </div>
                                        </div>
                                        <div class="col-md-auto flex-1">
                                            <div class="row align-items-center">
                                                <div class="col-auto flex-1">
                                                    <h2 class="mb-2 fs-18 text-dark-green font-weight-bold">{{ $customer->first_name }} {{ $customer->last_name }}</h2>
                                                    <span class="badge_1">{{ __('amazy.entrepreneur') }}</span>
                                                    @if ($customer->is_active == 1)
                                                        <span class="badge_1 ml_5">{{ __('common.active') }}</span>
                                                    @elseif ($customer->is_active == 0)
                                                        <span class="badge_4 ml_5">{{ __('common.disabled') }}</span>
                                                    @else
                                                        <span class="badge_4 ml_5">{{ __('common.in-active') }}</span>
                                                    @endif
                                                    <span class="badge_5">
                                                        <div class="d-flex gap-2">
                                                            {{__('marketing.referral_code')}}: {{$referralCode}}
                                                            <input type="hidden" id="referralCode" value="{{getNumberTranslate($referralCode)}}">
                                                            <button id="copyBtn" class="amaz_primary_btn d-flex align-items-center justify-content-center" title="{{__('defaultTheme.copy_code')}}">
                                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                                            </button>
                                                        </div>
                                                    </span>
                                                    <p class="mt-2">{{$customerProfile?->document_number}} • {{$customer->email}} • {{getNumberTranslate($customer->phone) ?? $customer->username}}</p>
                                                    <p class="text-muted">{{__('common.member_since')}} {{dateConvert($customer->created_at)}} </p>
                                                </div>
                                                <div class="col-md-auto col-12 mt-2 mt-md-0">
                                                    @if($customer->getPersonalFolder())
                                                        <a class="btn-toolkit btn-primary btn-sm btn-icon " href="{{ route('admin.file-explorer.index') }}?folder={{$customer->getPersonalFolder()->id}}" id="digital_folder">
                                                            <i class="fas fa-folder-open"></i>
                                                            {{__('amazy.digital_folder')}}
                                                        </a>
                                                    @endif
                                                    <a href="{{route('admin.customer.edit', $customer->id)}}" id="edit-button" class="btn-toolkit btn-secondary btn-sm btn-icon"><i class="ti-pencil"></i>{{__('common.edit')}}</a>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-8 mx-auto">
                                @if(!empty($planContext))
                                    <x-plan-card :planContext="$planContext">
                                        <x-slot name="header">
                                            @if(data_get($planContext, 'current_explicit_discount.discount_quantity') !== null)
                                            <p><span class="badge_5">{{ $planContext['current_explicit_discount']['discount_quantity'] }}% {{ __('common.discount') }}</span></p>
                                            @endif
                                        </x-slot>

                                    </x-plan-card>
                                @else
                                    <x-plan-card-empty :showChangePlan="true" />
                                @endif

                                <div class="form-card">
                                    <h3 class="mb-3">
                                        {{ __('common.plans_history') }}
                                    </h3>
                                    <x-plan-timeline :history="$customerProfile?->planHistory" type="customer" :empty-text="__('common.no_results_found')" />

                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-card animate from-bottom delay-3">
                                    {{-- Inclusión de las Tabs con la NUEVA ruta --}}
                                    @include('customer::customers.components.detail-tabs.tabs_navigation')

                                    <div class="tab-content mt_20" id="pills-tabContent">
                                        
                                        {{-- Tab: Puntaje --}}
                                        <div class="tab-pane fade show active " id="pills-score" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.score_summary')
                                        </div>

                                        {{-- Tab: Mi Red --}}
                                        <div class="tab-pane fade" id="pills-network" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.network')
                                        </div>

                                        {{-- Tab: Paso 1 (Básicos) --}}
                                        <div class="tab-pane fade" id="pills-basic" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_1_basic_data')
                                        </div>

                                        {{-- Tab: Paso 2 (Adicional) --}}
                                        <div class="tab-pane fade" id="pills-additional" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_2_additional_info')
                                        </div>

                                        {{-- Tab: Paso 3 (Documentos) --}}
                                        <div class="tab-pane fade" id="pills-documents" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_3_documents')
                                        </div>

                                        {{-- NUEVAS TABS --}}
                                        
                                        {{-- Tab: Paso 4 (Bancos) --}}
                                        <div class="tab-pane fade" id="pills-bank" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_4_bank_info')
                                        </div>

                                        {{-- Tab: Paso 5 (Laboral/PEP) --}}
                                        <div class="tab-pane fade" id="pills-work" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_5_work_pep')
                                        </div>

                                        {{-- Tab: Paso 6 (Financiera) --}}
                                        <div class="tab-pane fade" id="pills-financial" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_6_financial_info')
                                        </div>

                                        {{-- Tab: Paso 7 (Moneda Extranjera) --}}
                                        <div class="tab-pane fade" id="pills-foreign" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_7_foreign_currency')
                                        </div>

                                        {{-- Tab: Paso 8 (Tributaria) --}}
                                        <div class="tab-pane fade" id="pills-tax" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_8_tax_info')
                                        </div>

                                        {{-- Tab: Paso 9 (Representante) --}}
                                        <div class="tab-pane fade" id="pills-entrepreneur" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.step_9_entrepreneur_data')
                                        </div>

                                        {{-- NUEVO: Tab de Historial de Auditoría KYC --}}
                                        <div class="tab-pane fade" id="pills-kyc_history" role="tabpanel">
                                            @include('customer::customers.components.detail-tabs.kyc_history')
                                        </div>

                                    </div>
                                </div>
                                <div class="form-card">
                                    <h3 class="">{{ __('common.order_summary') }}</h3>

                                        {{-- ORDER + WALLET --}}
                                        @php
                                            $orderSummary = [
                                                // Usamos number_format(valor, decimales, separador_decimal, separador_miles)
                                                __('common.total_orders') => number_format(count($customer->orders), 0, '', '.'),

                                                __('common.confirmed_orders') => number_format(count(
                                                    $customer->orders->where('is_confirmed', 1)->where('is_completed', 0),
                                                ), 0, '', '.'),

                                                __('common.pending_orders') => number_format(count(
                                                    $customer->orders->where('is_confirmed', 0),
                                                ), 0, '', '.'),

                                                __('common.completed_orders') => number_format(count(
                                                    $customer->orders->where('is_completed', 1),
                                                ), 0, '', '.'),

                                                __('common.cancelled_orders') => number_format(count(
                                                    $customer->orders->where('is_cancelled', 1),
                                                ), 0, '', '.'),
                                            ];
                                        @endphp

                                        {{-- BODY --}}
                                        <div class="row mx-0">
                                            @foreach ($orderSummary as $label => $value)
                                                <div class="col-12 col-lg-6 mb_10">
                                                    <span class="primary_input_label">{{ $label }}</span>
                                                    <span class="badge_6">{{ getNumberTranslate($value) }}</span>
                                                </div>
                                            @endforeach
                                        </div>

                                        

                                </div>
                                <div class="form-card">
                                     <h3 class="">{{ __('common.wallet_summary') }}</h3>
                                    @php
                                        $walletSummary = [
                                            // Aquí agregamos el signo $ manualmente y formateamos el número
                                            // Formato: 2 decimales (,), miles (.) -> Ej: $ 1.500,00
                                            
                                            __('common.total_recharge') => '$ ' . number_format(
                                                $customer->wallet_balances->where('type', 'Deposite')->sum('amount'),
                                            2, ',', '.'),

                                            __('common.pending_balance_approval') => '$ ' . number_format(
                                                $customer->CustomerCurrentWalletPendingAmounts,
                                            2, ',', '.'),

                                            __('common.total_balance') => '$ ' . number_format(
                                                $customer->CustomerCurrentWalletAmounts,
                                            2, ',', '.'),
                                        ];
                                    @endphp


                                    {{-- BODY --}}
                                    <div class="row mx-0">
                                        @foreach ($walletSummary as $label => $value)
                                            <div class="col-12 col-lg-6 mb_10">
                                                <span class="primary_input_label" >{{ $label }}</span>
                                                <strong class="badge_6">{{ $value }}</strong>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <div class="white_box_30px box_shadow_white">
                        <div class="col-lg-12 student-details">
                            <ul class="nav ign-scrollbar flex-nowrap w-100 overflow-auto pb-0 custom_nav_details">
                                <li class="nav-item">
                                    <a class="nav-link capitalize active" href="#Order" role="tab"
                                        data-toggle="tab">{{ __('common.orders') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link capitalize" href="#Wallet" role="tab"
                                        data-toggle="tab">{{ __('common.wallet_histories') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link capitalize" href="#Address" role="tab"
                                        data-toggle="tab">{{ __('common.addresses') }}</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link capitalize" href="#login_ip" role="tab"
                                        data-toggle="tab">{{ __('common.login_ip') }}</a>
                                </li>
                            </ul>
                            <div class="tab-content pt-30">

                                <div role="tabpanel" class="tab-pane fade show active" id="Order">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="QA_section QA_section_heading_custom check_box_table">
                                                <div class="QA_table ">
                                                    <div class="table-responsive ign-scrollbar">
                                                        <table class="table" id="orderTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ __('common.sl') }}</th>
                                                                    <th>{{ __('common.date') }}</th>
                                                                    <th>{{ __('common.order_id') }}</th>
                                                                    <th>{{ __('order.total_product_qty') }}</th>
                                                                    <th>{{ __('common.total_amount') }}</th>
                                                                    <th>{{ __('order.order_status') }}</th>
                                                                    <th>{{ __('order.is_paid') }}</th>
                                                                    <th>{{ __('common.action') }}</th>
                                                                </tr>
                                                            </thead>

                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="Wallet">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="QA_section QA_section_heading_custom check_box_table">
                                                <div class="QA_table ">

                                                    <div class="table-responsive ign-scrollbar">
                                                        <table class="table Crm_table_active3" id="walletTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ __('common.sl') }}</th>
                                                                    <th>{{ __('common.date') }}</th>
                                                                    <th>{{ __('common.user') }}</th>
                                                                    <th>{{ __('order.txn_id') }}</th>
                                                                    <th>{{ __('common.amount') }}</th>
                                                                    <th>{{ __('common.type') }}</th>
                                                                    <th>{{ __('common.payment_method') }}</th>
                                                                    <th>{{ __('common.approval') }}</th>
                                                                </tr>
                                                            </thead>

                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="Address">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="QA_section QA_section_heading_custom check_box_table">
                                                <div class="QA_table ">
                                                    <div class="table-responsive ign-scrollbar">
                                                        <table class="table Crm_table_active3">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ __('common.sl') }}</th>
                                                                    <th>{{ __('common.full_name') }}</th>
                                                                    <th>{{ __('common.address') }}</th>
                                                                    <th>{{ __('common.region') }}</th>
                                                                    <th>{{ __('common.email') }}</th>
                                                                    <th>{{ __('common.phone_number') }}</th>
                                                                    <th>{{ __('common.postcode') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($customer->customerAddresses as $key => $address)
                                                                    <tr>
                                                                        <td
                                                                            class="{{ $address->is_updated == 0 ? 'white-color' : '' }}">
                                                                            {{ getNumberTranslate($key + 1) }}</td>
                                                                        <td
                                                                            class="{{ $address->is_updated == 0 ? 'white-color' : '' }}">
                                                                            {{ $address->name }}</td>
                                                                        <td
                                                                            class="{{ $address->is_updated == 0 ? 'white-color' : '' }}">
                                                                            {{ $address->address }}</td>
                                                                        <td
                                                                            class="{{ $address->is_updated == 0 ? 'white-color' : '' }}">
                                                                            {{ !empty($address->getCity) ? $address->getCity->name : '' }}
                                                                            {{ !empty($address->getState) ? ', ' . $address->getState->name : '' }}
                                                                            {{ !empty($address->getCountry) ? ', ' . $address->getCountry->name : '' }}

                                                                        </td>
                                                                        <td
                                                                            class="{{ $address->is_updated == 0 ? 'white-color' : '' }}">
                                                                            {{ $address->email }}</td>
                                                                        <td
                                                                            class="{{ $address->is_updated == 0 ? 'white-color' : '' }}">
                                                                            {{ getNumberTranslate($address->phone) }}</td>
                                                                        <td
                                                                            class="{{ $address->is_updated == 0 ? 'white-color' : '' }}">
                                                                            {{ getNumberTranslate($address->postal_code) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="login_ip">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="QA_section QA_section_heading_custom check_box_table">
                                                <div class="QA_table ">
                                                    <div class="table-responsive ign-scrollbar">
                                                        <table class="table Crm_table_active3">
                                                            <thead>
                                                                <tr>
                                                                    <th>{{ __('common.sl') }}</th>
                                                                    <th>{{ __('common.IP') }}</th>
                                                                    <th>{{ __('common.agent') }}</th>
                                                                    <th>{{ __('common.login_time') }}</th>
                                                                    <th>{{ __('common.logout_time') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($logins as $key => $login)
                                                                    <tr>
                                                                        <td>{{ $key + 1 }}</td>
                                                                        <td>{{ $login->ip }}</td>
                                                                        <td>{{ $login->agent }}</td>
                                                                        <td>{{ showDate($login->login_time) . ' ' . date('h:i a', strtotime($login->login_time)) }}
                                                                        </td>
                                                                        <td>{{ showDate($login->logout_time) . ' ' . date('h:i a', strtotime($login->logout_time)) }}
                                                                        </td>
                                                                    </tr>
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
                </div>
            </div>
        </div>
        {{-- Modales de historial de cambios KYC --}}
        @include('customer::customers.components.modals._kyc_logs_modals')

        {{-- Modales de cambio de plan --}}
        @include('customer::customers.components.modals._change_plan_modal')
    </section>
@endsection
@push('scripts')
<script src="{{ asset('/public/js/copy.js') }}"></script>
<script src="{{ asset('/public/js/modal_rule_followed.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        let baseUrl = $('#url').val();
        let urlForOrders = baseUrl + '/customer/profile/details/' + "{{ $customer->id }}" + '/get-orders';
        $('#orderTable').DataTable({
            processing: true,
            serverSide: true,
            "stateSave": true,
            "ajax": ({
                url: urlForOrders
            }),
            "initComplete": function(json) {},
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    render: function(data) {
                        return numbertrans(data)
                    }
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'order_number',
                    name: 'order_number'
                },
                {
                    data: 'number_of_product',
                    name: 'number_of_product'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'order_status',
                    name: 'order_status'
                },
                {
                    data: 'is_paid',
                    name: 'is_paid'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
            bLengthChange: false,
            "bDestroy": true,
            language: {
                search: "<i class='ti-search'></i>",
                searchPlaceholder: trans('common.quick_search'),
                paginate: {
                    next: "<i class='ti-arrow-right'></i>",
                    previous: "<i class='ti-arrow-left'></i>"
                }
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    title: $("#header_title").text(),
                    titleAttr: 'Copy',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    title: $("#header_title").text(),
                    margin: [10, 10, 10, 0],
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    },

                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    title: $("#header_title").text(),
                    titleAttr: 'PDF',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    },
                    pageSize: 'A4',
                    margin: [0, 0, 0, 0],
                    alignment: 'center',
                    header: true,

                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: $("#header_title").text(),
                    exportOptions: {
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    postfixButtons: ['colvisRestore']
                }
            ],
            columnDefs: [{
                visible: false
            }],
            responsive: true,
        });

        let urlForWallet = baseUrl + '/customer/profile/details/' + "{{ $customer->id }}" +
            '/get-wallet-history';
        $('#walletTable').DataTable({
            processing: true,
            serverSide: true,
            "stateSave": true,
            "ajax": ({
                url: urlForWallet
            }),
            "initComplete": function(json) {

            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'id',
                    render: function(data) {
                        return numbertrans(data)
                    }
                },
                {
                    data: 'date',
                    name: 'date'
                },
                {
                    data: 'user',
                    name: 'user'
                },
                {
                    data: 'txn_id',
                    name: 'txn_id'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'payment_method',
                    name: 'payment_method'
                },
                {
                    data: 'approval',
                    name: 'approval'
                }
            ],

            bLengthChange: false,
            "bDestroy": true,
            language: {
                search: "<i class='ti-search'></i>",
                searchPlaceholder: trans('common.quick_search'),
                paginate: {
                    next: "<i class='ti-arrow-right'></i>",
                    previous: "<i class='ti-arrow-left'></i>"
                }
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'copyHtml5',
                    text: '<i class="fa fa-files-o"></i>',
                    title: $("#header_title").text(),
                    titleAttr: 'Copy',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    titleAttr: 'Excel',
                    title: $("#header_title").text(),
                    margin: [10, 10, 10, 0],
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    },
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fa fa-file-text-o"></i>',
                    titleAttr: 'CSV',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    title: $("#header_title").text(),
                    titleAttr: 'PDF',
                    exportOptions: {
                        columns: ':visible',
                        columns: ':not(:last-child)',
                    },
                    pageSize: 'A4',
                    margin: [0, 0, 0, 0],
                    alignment: 'center',
                    header: true,
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    title: $("#header_title").text(),
                    exportOptions: {
                        columns: ':not(:last-child)',
                    }
                },
                {
                    extend: 'colvis',
                    text: '<i class="fa fa-columns"></i>',
                    postfixButtons: ['colvisRestore']
                }
            ],
            columnDefs: [{
                visible: false
            }],
            responsive: true,
        });

        $(document).on('click', '#copyBtn', function () {
            copyToClipboard('referralCode');
        });
    });
</script>

@endpush

<style>

    @media(min-width: 768px) {
        .entrepreneur-detail{
            max-width: 49.5% !important;
        }
    }

    /* AJUSTE RESPONSIVO: Rango Laptop/Tablet (992px - 1350px) */
    @media (min-width: 992px) and (max-width: 1320px) {
        /* Reducir fuente de los valores para evitar saltos de línea */
        .card-value {
            font-size: 13px !important;
        }
        .card-label {
            font-size: 10px !important;
        }
        /* Reducir el padding de las tarjetas para ganar espacio */
        .customer-info-card {
            padding: 10px !important;
        }
        /* Ajustar icono del Score */
        .icon-circle-box {
            width: 35px !important;
            height: 35px !important;
            margin-right: 10px !important;
        }
        .icon-circle-box i {
            font-size: 16px !important;
        }
    }

    /* Solución para las tabs en pantallas pequeñas */
    .custom_nav_details {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none; /* Firefox */
    }
    .custom_nav_details::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }

    /* REGLA PARA DOCUMENTOS: */
    /* Por defecto son col-12 (stacked). */
    /* Solo cuando la pantalla supera los 1320px, las convertimos en col-6 (lado a lado) */
    @media (min-width: 1321px) {
        .col-document-custom {
            -ms-flex: 0 0 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    /* Estilo base de la tarjeta */
    /* Estilo base de la tarjeta */
    .customer-info-card {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, 0.04); /* Borde sutil para definición */
        border-radius: 16px; /* Radio un poco más suave */
        height: 100%;
        padding: 20px 25px; /* Más aire interno (padding) se ve más elegante */
        position: relative;
        
        /* Sombra inicial muy suave (casi imperceptible) */
        box-shadow: 0 4px 10px #00000024;
        
        /* Transición con curva 'cubic-bezier' para que se sienta más natural/elástica */
        transition: all 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    /* Efecto Hover: La tarjeta "flota" hacia arriba */
    .customer-info-card:hover {
        border-color: rgba(var(--base_color_rgb), 0.2);
    }

    /* OPCIONAL: Decoración lateral para darle toque de "Dashboard" */
    /* Esto pone una línea de color a la izquierda al hacer hover */
    .customer-info-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%) scaleY(0); /* Oculto inicialmente */
        width: 4px;
        height: 70%;
        background: var(--base_color);
        border-radius: 0 4px 4px 0;
        transition: transform 0.8s ease;
    }

    .customer-info-card:hover::before {
        transform: translateY(-50%) scaleY(1); /* Aparece suavemente al hacer hover */
    }

    .partial-card{
        padding-bottom: 30px !important;
    }


    /* Opcional: un efecto hover suave */
    .customer-info-card:hover, #digital_folder:hover, #edit-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    /* Estilo para el título pequeño (Label) */
    .card-label {
        color: #6c757d; /* text-muted */
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        display: block;
    }

    /* Estilo para el valor principal */
    .card-value {
        font-weight: 500;
        font-size: 15px;
        color: var(--text_black); /* Toma el color base del tema */
        margin-bottom: 0;
        line-height: 1.2;
    }

    /* Estilo para el contenedor del icono (Score) */
    .icon-circle-box {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 15px;
    }
</style>


