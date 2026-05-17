<?php

namespace Modules\OrderManage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryProcessRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    /**
     * Reglas de validación para el proceso de entrega.
     * Soporta formatos de texto simple y multi-idioma.
     */
    public function rules()
    {
        $id = $this->id;
        // Idioma por defecto (es, en, etc.)
        $code = app()->getLocale();

        // 1. Verificamos si el módulo multi-idioma está activo
        if (isModuleActive('FrontendMultiLang')) {
            // --- Escenario Multi-idioma (Ej: name[es]) ---
            return [
                // Valida el contenido de cada idioma (español, inglés, etc.)
                'name.*' => 'required|max:255',

                // Regla unique sobre la ruta JSON (name->es)
                'name' => [
                    'array',
                    Rule::unique('delivery_processes', "name->{$code}")->ignore($id)
                ],

                // Valida la descripción de cada idioma
                'description.*' => 'required|string',
            ];
        } else {
            // --- Escenario Idioma Único (Ej: name="proceso") ---
            return [
                // Valida el campo 'name' como texto simple
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('delivery_processes', 'name')->ignore($id)
                ],

                // Valida el campo 'description' como texto simple
                'description' => 'required|string',
            ];
        }
    }

    public function messages()
    {
        // Solo necesitamos los mensajes base. Laravel sabe aplicar el sufijo .*
        return [
            'name.required' => __('common.required_field'),
            'name.unique' => __('common.name_taken'),
            'description.required' => __('common.required_field'),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
