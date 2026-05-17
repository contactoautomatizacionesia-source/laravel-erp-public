<?php

namespace Modules\Incidents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resolution_party' => 'required|in:advisor,organization,voided',
            'resolution_notes' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'resolution_party.required' => 'Debe indicar quién responde por la novedad.',
            'resolution_party.in'       => 'El responsable debe ser "advisor", "organization" o "voided".',
            'resolution_notes.required' => 'La justificación del administrador es obligatoria.',
            'resolution_notes.min'      => 'La justificación debe tener al menos 10 caracteres.',
        ];
    }
}
