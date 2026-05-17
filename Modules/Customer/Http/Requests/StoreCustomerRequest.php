<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\GeneralSetting\Rules\CatalogExists;
use Modules\Customer\Rules\GlobalUniqueEmail;
use Modules\Customer\Rules\GlobalUniquePhone;

class StoreCustomerRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'contract_type_id' => ['required', 'integer', new CatalogExists('contract_type')],

            // 1. FECHAS LÓGICAS
            'registration_date' => 'required|date|before_or_equal:today',
            'representative' => 'required|integer|exists:users,id',
            'referral_code' => ['nullable', 'string', Rule::exists('referral_codes', 'referral_code')->where('status', 1)],
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',

            // 2. INTEGRIDAD DE CATÁLOGOS FALTANTES
            'document_type_id' => 'required|integer|exists:type_documents,id', // Ajusta si la tabla se llama diferente
            'document_number' => 'required|string|max:50|unique:customer_profiles,document_number',

            // Fechas de identidad
            'date_of_birth' => 'required|date|before:today',
            'birth_city_id' => 'required|integer|exists:cities,id',
            'issue_date' => 'required|date|before_or_equal:today|after:date_of_birth',
            'issue_city_id' => 'required|integer|exists:cities,id',
            'expiration_date' => 'nullable|date|after:issue_date|after_or_equal:today',

            'gender_id' => ['required', 'integer', new CatalogExists('gender')],
            'nationality_id' => 'required|integer|exists:countries,id', // Agregado exists
            'country' => 'required|integer|exists:countries,id',
            'state' => 'required|integer|exists:states,id',
            'city' => 'required|integer|exists:cities,id',
            'address' => 'required|string|max:255',

            'whatsapp' => ['required', 'string', 'max:20', new GlobalUniquePhone()],
            'whatsapp_country_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],

            'phone_calls' => ['nullable', 'string', 'max:20', new GlobalUniquePhone()],
            'phone_calls_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],

            'phone_office' => ['nullable', 'string', 'max:20', new GlobalUniquePhone()],
            'phone_office_code_id' => ['nullable', 'integer', new CatalogExists('country_phone_code')],

            'email' => ['required', 'email', 'max:191', new GlobalUniqueEmail()],
            'secondary_email' => ['nullable', 'email', 'max:191', new GlobalUniqueEmail() ],
            

            'civil_status_id' => ['required', 'integer', new CatalogExists('civil_status')],
            'economic_activity_id' => ['required', 'integer', new CatalogExists('economic_activity')],
            'profession_id' => ['required', 'integer', new CatalogExists('profession')],
            'lead_source_id' => ['required', 'integer', new CatalogExists('lead_source')],

            'product_id' => 'required|integer|exists:products,id', // Agregado exists

            'company_name' => 'nullable|string|max:191',
            'job_title' => 'nullable|string|max:191',
            'work_address' => 'nullable|string|max:191',

            'public_resources' => 'nullable|in:SI,NO',
            'marital_society' => 'nullable|in:SI,NO',
            'is_pep' => 'nullable|in:SI,NO',
            'pep_family' => 'nullable|in:SI,NO',

            'bank' => ['nullable', 'integer', new CatalogExists('bank')],

            // Si envía un banco, debe enviar el número y tipo de cuenta
            'account_number' => 'nullable|string|required_with:bank',
            'account_type' => ['nullable', 'integer', 'required_with:bank', new CatalogExists('bank_account_type')],

            'monthly_income' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'nullable|numeric|min:0',
            'other_income' => 'nullable|numeric|min:0',

            // Si hay otros ingresos, la descripción es obligatoria
            'other_income_desc' => 'nullable|string|required_with:other_income',

            'total_assets' => 'nullable|numeric|min:0',
            'total_liabilities' => 'nullable|numeric|min:0',
            'total_equity' => 'nullable|numeric|min:0',

            // --- 7. TRIBUTARIA ---
            'iva_responsibility' => 'nullable|in:SI,NO',
            'rent_retention_agent' => 'nullable|in:SI,NO',
            'ica_retention_agent' => 'nullable|in:SI,NO',
            'sales_tax_responsible' => 'nullable|in:SI,NO',
            'grand_contributor' => 'nullable|in:SI,NO',
            'self_withholder' => 'nullable|in:SI,NO',
            'source_retention' => 'nullable|in:SI,NO',

            // Si tiene retención, pedir el motivo
            'retention_reason' => 'nullable|string|required_if:source_retention,NO',

            'ica_tax' => 'nullable|in:SI,NO',

            // Si declara ICA, exigir la tarifa
            'ica_rate' => 'nullable|numeric|min:0|required_if:ica_tax,SI',
            'declaration_city_id' => 'nullable|integer|exists:cities,id', // Agregado exists
            'has_rut' => 'nullable|in:SI,NO',

            // --- 8. MONEDA EXTRANJERA ---
            'ops_foreign_currency' => 'nullable|in:SI,NO',

            // Exigir descripción si opera en moneda extranjera
            'ops_foreign_desc' => 'nullable|string|required_if:ops_foreign_currency,SI',

            'has_foreign_accounts' => 'nullable|in:SI,NO',

            // Exigir detalles si tiene cuenta en el exterior
            'foreign_bank' => 'nullable|string|required_if:has_foreign_accounts,SI',
            'foreign_account_number' => 'nullable|string|required_if:has_foreign_accounts,SI',
            'foreign_currency' => 'nullable|string|required_if:has_foreign_accounts,SI',
            'foreign_country_id' => 'nullable|integer|exists:countries,id|required_if:has_foreign_accounts,SI',
            'foreign_city_id' => 'nullable|integer|exists:cities,id|required_if:has_foreign_accounts,SI',

            // --- 9. DOCUMENTACIÓN Y SEGURIDAD ---
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:0,1',

            // Aumenté a 4096 (4MB) para prevenir errores con fotos de celulares
            'front_id_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'back_id_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'rut_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }
}