<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\GeneralSetting\Rules\CatalogExists;
use Modules\Customer\Rules\GlobalUniqueEmail;
use Modules\Customer\Rules\GlobalUniquePhone;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = auth()->id();
        $block = $this->input('validate_block');

        // Regla base para saber qué estamos validando
        $rules = [
            'validate_block' => ['required', 'string', Rule::in(['admin_update','basic_info', 'personal_info', 'financial_info', 'labor_info', 'documents_info'])],
        ];

        switch ($block) {
            case 'basic_info':
                $rules = array_merge($rules, [
                    'first_name'  => 'required|string|max:191',
                    'last_name'   => 'required|string|max:191',
                    'middle_name' => 'nullable|string|max:191',
                    'email' => ['required', 'email', 'max:191', new GlobalUniqueEmail($userId)],
                    'avatar'      => 'nullable|image|mimes:jpeg,jpg,png,bmp|max:2048',

                    'document_type_id' => 'required|integer|exists:type_documents,id',
                    'document_number'  => [
                        'required', 'string', 'max:50',
                        Rule::unique('customer_profiles', 'document_number')->ignore($userId, 'user_id')
                    ],
                    
                    'issue_date'      => 'required|date|before_or_equal:today|after:date_of_birth',
                    'issue_city_id'   => 'required|integer|exists:cities,id',
                    'expiration_date' => 'nullable|date|after:issue_date|after_or_equal:today', 

                    'date_of_birth'   => 'required|date|before:today',
                    'birth_city_id'   => 'required|integer|exists:cities,id',
                    
                    'gender_id'       => ['required', 'integer', new CatalogExists('gender')],
                    'nationality_id'  => 'required|integer|exists:countries,id',
                    'civil_status_id' => ['required', 'integer', new CatalogExists('civil_status')],

                    'country' => 'required|integer|exists:countries,id',
                    'state'   => 'required|integer|exists:states,id',
                    'city'    => 'required|integer|exists:cities,id',
                    'address' => 'required|string|max:255',
                    'whatsapp' => ['required', 'string', 'max:20', new GlobalUniquePhone($userId)],

                    'whatsapp_country_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],
                    'phone_calls' => ['nullable', 'string', 'max:20', new GlobalUniquePhone($userId)],

                    'phone_calls_code_id'  => ['nullable', 'integer', new CatalogExists('country_phone_code')],
                    'phone_office' => ['nullable', 'string', 'max:20', new GlobalUniquePhone($userId)],

                    'phone_office_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],
                    'secondary_email' => ['nullable', 'email', 'max:191', new GlobalUniqueEmail($userId) ],
                ]);
                break;
            case 'personal_info':
                $rules = array_merge($rules, [
                    'economic_activity_id' => ['required', 'integer', new CatalogExists('economic_activity')],
                    'profession_id'        => ['required', 'integer', new CatalogExists('profession')],
                    'product_id'           => 'required|integer|exists:products,id',
                    'lead_source_id'       => ['required', 'integer', new CatalogExists('lead_source')],
                ]);
                break;
            case 'financial_info':
                $rules = array_merge($rules, [
                    'bank' => ['nullable', 'integer', new CatalogExists('bank')],
                    
                    'account_type' => [
                        'nullable', 
                        'integer', 
                        'required_with:bank',
                        new CatalogExists('bank_account_type')
                    ],
                    
                    'account_number' => 'nullable|string|max:50|required_with:bank',
                    'monthly_income'    => 'required|numeric|min:0',
                    'monthly_expenses'  => 'required|numeric|min:0',
                    'total_assets'      => 'required|numeric|min:0',
                    'total_liabilities' => 'required|numeric|min:0',
                    'total_equity'      => 'required|numeric|min:0',
                    'other_income'      => 'nullable|numeric|min:0',
                    'other_income_desc' => 'nullable|string|max:191|required_if:other_income,>,0',
                    'ops_foreign_currency' => ['required', Rule::in(['SI', 'NO'])],
                    'ops_foreign_desc'     => 'nullable|string|max:255|required_if:ops_foreign_currency,SI',
                    'has_foreign_accounts'   => ['required', Rule::in(['SI', 'NO'])],
                    'foreign_bank'           => 'nullable|string|max:191|required_if:has_foreign_accounts,SI',
                    'foreign_account_number' => 'nullable|string|max:50|required_if:has_foreign_accounts,SI',
                    'foreign_currency'       => 'nullable|string|max:10|required_if:has_foreign_accounts,SI',
                    'foreign_country_id'     => 'nullable|integer|exists:countries,id|required_if:has_foreign_accounts,SI',
                    'foreign_city_id'        => 'nullable|integer|exists:cities,id|required_if:has_foreign_accounts,SI',
                    'iva_responsibility'    => ['nullable', Rule::in(['SI', 'NO'])],
                    'rent_retention_agent'  => ['nullable', Rule::in(['SI', 'NO'])],
                    'ica_retention_agent'   => ['nullable', Rule::in(['SI', 'NO'])],
                    'sales_tax_responsible' => ['nullable', Rule::in(['SI', 'NO'])],
                    'grand_contributor'     => ['nullable', Rule::in(['SI', 'NO'])],
                    'self_withholder'       => ['nullable', Rule::in(['SI', 'NO'])],
                    'source_retention' => ['nullable', Rule::in(['SI', 'NO'])],
                    'retention_reason' => 'nullable|string|max:255|required_if:source_retention,NO',
                    'ica_tax' => ['nullable', Rule::in(['SI', 'NO'])],
                    'ica_rate'            => 'nullable|numeric|min:0|required_if:ica_tax,SI',
                    'declaration_city_id' => 'nullable|integer|exists:cities,id|required_if:ica_tax,SI',
                    'has_rut'  => ['required', Rule::in(['SI', 'NO'])],
                    'rut_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
                ]);
                break;
            case 'labor_info':
                $rules = array_merge($rules, [
                    'company_name' => 'nullable|string|max:191',
                    'job_title'    => 'nullable|string|max:191',
                    'work_address' => 'nullable|string|max:191',
                    'public_resources' => ['required', Rule::in(['SI', 'NO'])],
                    'marital_society'  => ['required', Rule::in(['SI', 'NO'])],
                    'is_pep'     => ['required', Rule::in(['SI', 'NO'])],
                    'pep_family' => ['required', Rule::in(['SI', 'NO'])],
                ]);
                break;
            case 'documents_info':
                $rules = array_merge($rules, [
                    'front_id_image' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',
                    'back_id_image'  => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:4096',
                ]);
                break;
            case 'admin_update':
                $rules = array_merge($rules, [
                    'first_name'  => 'required|string|max:191',
                    'last_name'   => 'required|string|max:191',
                    'email' => ['required', 'email', 'max:191', new GlobalUniqueEmail($userId)],
                    'phone' => ['required', 'string', 'max:20', new GlobalUniquePhone($userId)],
                    'date_of_birth'   => 'required|date|before:today',
                    'avatar'      => 'nullable|image|mimes:jpeg,jpg,png,bmp|max:2048',

                ]);
                break;
        }

        return $rules;
    }
}