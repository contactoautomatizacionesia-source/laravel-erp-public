<?php

namespace Modules\Incidents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncidentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statement_deadline_hours'  => 'required|integer|min:1|max:8760',
            'auto_escalate_on_deadline' => 'required|boolean',
            'send_email_notifications'  => 'required|boolean',
            'send_system_notifications' => 'required|boolean',
            'send_deadline_reminder'    => 'required|boolean',
            'reminder_hours_before'     => 'required|integer|min:1|max:8760',
            'price_reference'           => 'required|in:public_price,cost_price,transfer_price',
        ];
    }

    public function messages(): array
    {
        return [
            'statement_deadline_hours.required' => 'El plazo de pronunciamiento es obligatorio.',
            'statement_deadline_hours.min'      => 'El plazo debe ser al menos 1 hora.',
            'reminder_hours_before.min'         => 'El recordatorio debe ser al menos 1 hora antes.',
            'price_reference.in'                => 'El precio de referencia debe ser público, costo o transferencia.',
        ];
    }
}
