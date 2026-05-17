<?php

namespace Modules\InventoryEntry\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteInventoryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'audit_notes' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'audit_notes.required' => __('inventoryentry::inventory.audit_note_required'),
        ];
    }
}
