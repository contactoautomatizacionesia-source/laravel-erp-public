@php
    $bankFields = [
        ['label' => __('amazy.bank_name'), 'value' => $customerFinancialProfile?->bank?->name ?? '---'],
        ['label' => __('amazy.account_number'), 'value' => $customerFinancialProfile?->account_number ?? '---'],
        ['label' => __('amazy.account_type'), 'value' => $customerFinancialProfile?->bankAccountType?->name ?? '---'],
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', [
    'items' => $bankFields, 
    'colClass' => 'col-xl-6 col-lg-6 col-md-6'
])