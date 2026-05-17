<?php

namespace Modules\CashManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\GeneralSetting\Entities\Catalogs\CashDiscrepancyType;

class CloseCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_id'      => 'required|uuid|exists:cash_sessions,id',

            // Tipo de novedad (obligatorio solo cuando hay discrepancia)
            'discrepancy_type_id' => 'nullable|integer|exists:system_catalogs,id',

            // Justificación breve
            'justification'   => 'nullable|string|max:1000',

            // Nota libre (obligatoria cuando el tipo es 'other')
            'notes'           => ['nullable', 'string', 'max:2000'],

            // Medios de pago declarados (al menos uno)
            'payments'                        => 'required|array|min:1',
            'payments.*.payment_form_id'      => 'required|integer|exists:system_catalogs,id',
            'payments.*.total_amount'         => 'required|numeric|min:0',
            'payments.*.transaction_count'    => 'required|integer|min:0',
            'payments.*.reference_data'       => 'nullable|string|max:500',

            // Desglose de denominaciones
            'denominations'                        => 'required|array|min:1',
            'denominations.*.denomination_id'      => 'required|uuid|exists:cat_denominations,id',
            'denominations.*.quantity'             => 'required|integer|min:0',
        ];
    }
}
