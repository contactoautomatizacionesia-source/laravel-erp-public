<?php

namespace Modules\Customer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\GeneralSetting\Rules\CatalogExists;
use Modules\Customer\Rules\GlobalUniqueEmail;
use Modules\Customer\Rules\GlobalUniquePhone;

class RegisterCustomerRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            // --- PASO 1: Identity ---
            'first_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => ['required', 'email', 'max:191', new GlobalUniqueEmail()],
            'secondary_email' => ['nullable', 'email', 'max:191', new GlobalUniqueEmail() ],

            'password' => 'required|string|min:8|confirmed',

            // Validar que el tipo de documento exista en su tabla (ajusta 'type_documents' si tu tabla se llama distinto)
            'document_type_id' => 'required|integer|exists:type_documents,id',

            // Asegurarnos que no registren un documento que ya existe en los perfiles
            'document_number' => 'required|string|max:50|unique:customer_profiles,document_number',

            // Mayoría de edad: debe tener al menos 18 años
            'date_of_birth' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),

            'birth_city_id' => 'required|integer|exists:cities,id', // Ajusta el nombre de la tabla

            'nationality_id' => 'required|integer|exists:countries,id',

            // Lógica cronológica para expedición
            'issue_date' => 'required|date|before_or_equal:today|after:date_of_birth',
            'issue_city_id' => 'required|integer|exists:cities,id',

            // Lógica cronológica para vencimiento (vigente y posterior a la expedición)
            'expiration_date' => 'nullable|date|after:issue_date|after_or_equal:today',

            // --- PASO 2: Contact & Location ---
            // Recomiendo agregar exists:countries,id, exists:states,id, etc...
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

            // Catálogos
            'civil_status_id' => 'required|integer',
            'economic_activity_id' => 'required|integer',
            'profession_id' => 'required|integer',
            'product_id' => 'required|integer',
            'lead_source_id' => 'required|integer',
            'gender_id' => 'required|integer',

            // --- PASO 3: Files ---
            // max:2048 significa 2MB. Si tus clientes suben fotos desde celulares modernos, 
            // 2MB a veces se queda corto. Podrías subirlo a 4096 (4MB) o 5120 (5MB).
            'front_id_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'back_id_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',

            // --- Opcionales (Referidos) ---
            'referral_code' => ['nullable', 'string', Rule::exists('referral_codes', 'referral_code')->where('status', 1)],

            // --- Red / Representante ---
            'representative_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
