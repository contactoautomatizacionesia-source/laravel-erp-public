@if($row->paymentForm)
    <span class="">{{ e($row->paymentForm->name) }}</span>
@else
    <span class="text-muted">N/A</span>
@endif
