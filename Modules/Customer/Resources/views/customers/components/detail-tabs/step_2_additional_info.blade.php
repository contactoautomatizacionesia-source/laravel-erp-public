@php
    $additionalFields = [
        ['label' => __('common.country'), 'value' => $customer->customerAddress?->getCountry?->name ?? '---'],
        ['label' => __('common.state'), 'value' => $customer->customerAddress?->getState?->name ?? '---'],
        ['label' => __('common.city'), 'value' => $customer->customerAddress?->getCity->name ?? '---'],
        ['label' => __('common.address'), 'value' => $customer->customerAddress?->address ?? '---'],
        ['label' => __('amazy.whatsapp_number'), 'value' => $customerProfile?->whatsapp ?? '---'],
        ['label' => __('common.phone_number'), 'value' => $customerProfile?->phone_calls ?? '---'],
        ['label' => __('common.email'), 'value' => $customer->email ?? '---'],
        ['label' => __('amazy.civil_status_id'), 'value' => $customerProfile?->civilStatus?->name ?? '---'],
        ['label' => __('amazy.economic_activity'), 'value' => $customerProfile?->economicActivity?->name ?? '---'],
        ['label' => __('amazy.profession'), 'value' => $customerProfile?->profession?->name ?? '---'],
        ['label' => __('amazy.product_interest'), 'value' => $customerProfile?->product?->product_name ?? '---'],
        ['label' => __('amazy.how_did_you_find_us'), 'value' => $customerProfile?->leadSource?->name ?? '---'],
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', ['items' => $additionalFields])