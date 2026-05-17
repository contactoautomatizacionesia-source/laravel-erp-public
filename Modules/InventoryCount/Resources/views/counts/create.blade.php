@extends('backEnd.master')

@section('mainContent')
@livewire('inventory-count.create-count', [
    'costCenterId' => $costCenterId,
    'countCode'    => $countCode,
])
@endsection
