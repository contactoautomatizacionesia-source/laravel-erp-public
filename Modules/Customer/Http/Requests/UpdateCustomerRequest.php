<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\GeneralSetting\Rules\CatalogExists;
use Modules\Customer\Rules\GlobalUniqueEmail;
use Modules\Customer\Rules\GlobalUniquePhone;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        $userId = $this->route('id') ?? $this->id; 

        return [
            'contract_type_id' => ['required', 'integer', new CatalogExists('contract_type')],
            'representative' => 'required|integer|exists:users,id',
            'referral_code' => ['nullable', 'string', Rule::exists('referral_codes', 'referral_code')->where('status', 1)],
            
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',

            'email' => [
                'required', 
                'email', 
                'max:191', 
                new GlobalUniqueEmail($userId)
            ],            
            
            'whatsapp' => [
                'required', 
                'string', 
                'max:20', 
                new GlobalUniquePhone($userId)
            ],

            'document_type_id' => 'required|integer|exists:type_documents,id',
            'document_number' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('customer_profiles', 'document_number')->ignore($userId, 'user_id')
            ],

            'registration_date' => 'required|date|before_or_equal:today',
            'date_of_birth' => 'required|date|before:today',
            'birth_city_id' => 'required|integer|exists:cities,id',
            'issue_date' => 'required|date|before_or_equal:today|after:date_of_birth',
            'issue_city_id' => 'required|integer|exists:cities,id',
            'expiration_date' => 'nullable|date|after:issue_date|after_or_equal:today',

            'gender_id' => ['required', 'integer', new CatalogExists('gender')],
            'nationality_id' => 'required|integer|exists:countries,id',
            'country' => 'required|integer|exists:countries,id',
            'state' => 'required|integer|exists:states,id',
            'city' => 'required|integer|exists:cities,id',
            'address' => 'required|string|max:255',

            'whatsapp_country_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],
            'phone_calls' => ['nullable', 'string', 'max:20', new GlobalUniquePhone($userId)],
            'phone_calls_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],
            'phone_office' => ['nullable', 'string', 'max:20', new GlobalUniquePhone($userId)],
            'phone_office_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],
            'secondary_email' => ['nullable', 'email', 'max:191', new GlobalUniqueEmail($userId) ],

            'civil_status_id' => ['required', 'integer', new CatalogExists('civil_status')],
            'economic_activity_id' => ['required', 'integer', new CatalogExists('economic_activity')],
            'profession_id' => ['required', 'integer', new CatalogExists('profession')],
            'lead_source_id' => ['required', 'integer', new CatalogExists('lead_source')],
            'product_id' => 'required|integer|exists:products,id',

            'company_name' => 'nullable|string|max:191',
            'job_title' => 'nullable|string|max:191',
            'work_address' => 'nullable|string|max:191',

            'public_resources' => 'nullable|in:SI,NO',
            'marital_society' => 'nullable|in:SI,NO',
            'is_pep' => 'nullable|in:SI,NO',
            'pep_family' => 'nullable|in:SI,NO',

            'bank' => ['nullable', 'integer', new CatalogExists('bank')],
            'account_number' => 'nullable|string|required_with:bank',
            'account_type' => ['nullable', 'integer', 'required_with:bank', new CatalogExists('bank_account_type')],

            'monthly_income' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'nullable|numeric|min:0',
            'other_income' => 'nullable|numeric|min:0',
            'other_income_desc' => 'nullable|string|required_with:other_income',
            'total_assets' => 'nullable|numeric|min:0',
            'total_liabilities' => 'nullable|numeric|min:0',
            'total_equity' => 'nullable|numeric|min:0',

            'iva_responsibility' => 'nullable|in:SI,NO',
            'rent_retention_agent' => 'nullable|in:SI,NO',
            'ica_retention_agent' => 'nullable|in:SI,NO',
            'sales_tax_responsible' => 'nullable|in:SI,NO',
            'grand_contributor' => 'nullable|in:SI,NO',
            'self_withholder' => 'nullable|in:SI,NO',
            'source_retention' => 'nullable|in:SI,NO',
            'retention_reason' => 'nullable|string|required_if:source_retention,NO',
            'ica_tax' => 'nullable|in:SI,NO',
            'ica_rate' => 'nullable|numeric|min:0|required_if:ica_tax,SI',
            'declaration_city_id' => 'nullable|integer|exists:cities,id',
            'has_rut' => 'nullable|in:SI,NO',

            'ops_foreign_currency' => 'nullable|in:SI,NO',
            'ops_foreign_desc' => 'nullable|string|required_if:ops_foreign_currency,SI',
            'has_foreign_accounts' => 'nullable|in:SI,NO',
            'foreign_bank' => 'nullable|string|required_if:has_foreign_accounts,SI',
            'foreign_account_number' => 'nullable|string|required_if:has_foreign_accounts,SI',
            'foreign_currency' => 'nullable|string|required_if:has_foreign_accounts,SI',
            'foreign_country_id' => 'nullable|integer|exists:countries,id|required_if:has_foreign_accounts,SI',
            'foreign_city_id' => 'nullable|integer|exists:cities,id|required_if:has_foreign_accounts,SI',

            // --- CONTRASEÑA (nullable) ---
            // Solo se valida si el usuario envió algo en el campo
            'password' => 'nullable|string|min:8|confirmed',
            
            'status' => 'required|in:0,1',

            // --- ARCHIVOS (nullable) ---
            'front_id_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'back_id_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'rut_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }
}