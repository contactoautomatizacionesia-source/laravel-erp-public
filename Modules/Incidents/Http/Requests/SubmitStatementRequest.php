<?php

namespace Modules\Incidents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statement_type' => 'required|in:acknowledged,rejected',
            'notes'          => 'nullable|string|max:2000',
            'file'           => 'required_if:statement_type,acknowledged|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'statement_type.required' => 'El tipo de pronunciamiento es obligatorio.',
            'statement_type.in'       => 'El tipo de pronunciamiento debe ser "acknowledged" o "rejected".',
            'file.required_if'        => 'Debe adjuntar una evidencia al reconocer el error.',
            'file.mimes'              => 'La evidencia debe ser una imagen (jpg, png) o PDF.',
            'file.max'                => 'La evidencia no debe superar los 10 MB.',
        ];
    }
}
