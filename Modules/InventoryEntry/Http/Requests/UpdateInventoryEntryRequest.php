<?php

namespace Modules\InventoryEntry\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'manufacture_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:manufacture_date'],
            'audit_notes' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => __('inventoryentry::inventory.quantity') . ' ' . __('common.field_required'),
            'quantity.min' => __('inventoryentry::inventory.quantity') . ': debe ser mayor a 0.',
            'expiration_date.after_or_equal' => __('inventoryentry::inventory.expiration_date') . ': debe ser igual o posterior a la fecha de fabricación.',
            'audit_notes.required' => __('inventoryentry::inventory.audit_note_required'),
        ];
    }
}
