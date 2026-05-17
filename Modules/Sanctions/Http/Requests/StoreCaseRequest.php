<?php

namespace Modules\Sanctions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'investigated_eui_code'         => ['required', 'string', 'max:20', 'exists:users,username'],
            'investigation_start_date'      => ['nullable', 'date'],
            'case_instructor'               => ['required', 'exists:users,id'],
            'case_complaint_source'         => ['required', 'exists:cat_complaint_sources,id'],
            'facts_description'             => ['required', 'string', 'min:10'],
            'incident_date'                 => ['required', 'date'],
            'sanction_additional_reference' => ['nullable', 'string', 'max:255'],
            'sanction_policie_broken'       => ['required', 'string', 'max:255'],
            'offence_scale_id'              => ['required', 'exists:cat_offense_types,id'],
            'mitigating_circumstances'      => ['nullable', 'array'],
            'mitigating_circumstances.*'    => ['exists:cat_mitigating_factors,id'],
            'observations'                  => ['nullable', 'string'],

            // Limit the number of uploaded files so the total request size stays bounded.
            'evidences'                     => ['nullable', 'array', 'max:5'],
            // Limit each file to 10 MB (10240 KB); with at most 5 files, the theoretical total is 50 MB per request.
            'evidences.*'                   => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,mp4,mp3,doc,docx,xls,xlsx'], // NOSONAR
        ];
    }

    public function attributes(): array
    {
        return [
            'investigated_eui_code'   => __('sanctions.investigated_eui_code'),
            'case_instructor'         => __('sanctions.case_instructor'),
            'case_complaint_source'   => __('sanctions.case_complaint_source'),
            'facts_description'       => __('sanctions.facts_description'),
            'incident_date'           => __('sanctions.incident_date'),
            'sanction_policie_broken' => __('sanctions.policie_broken'),
            'offence_scale_id'        => __('sanctions.offence_scale'),
        ];
    }

    // Agregamos este metodo para mantener tu mensaje de error personalizado
    public function messages(): array
    {
        return [
            'investigated_eui_code.exists' => __('sanctions.eui_not_found'),
        ];
    }
}
