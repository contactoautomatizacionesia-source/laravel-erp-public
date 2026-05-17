@php
    $entrepreneurFields = [
        ['label' => __('amazy.entrepreneur_code'), 'value' => $customerProfile?->representative?->referralCode?->referral_code ?? '---'],
        ['label' => __('amazy.registration_date'), 'value' => showDate($customerProfile?->registration_date ?? $customer->created_at)],
        ['label' => __('amazy.contract_type'), 'value' => $customerProfile?->contractType?->name ?? 'EMPRESARIO'],
        // Ahora el representante es un campo normal más
        ['label' => __('amazy.representative'), 'value' => $customerProfile?->representative?->name ?? '---'],
    ];
@endphp

@include('customer::customers.components.detail-tabs.info_grid', [
    'items' => $entrepreneurFields,
    'colClass' => 'col-xl-6 col-lg-6 col-md-6' // Usamos col-6 para que queden 2 arriba y 2 abajo (si son 4 datos)
])