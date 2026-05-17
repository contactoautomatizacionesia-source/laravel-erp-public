<?php

namespace Modules\InventoryExit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveInventoryExitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'                  => ['required', 'in:approved,rejected'],
            'approval_note'           => ['required', 'string', 'max:1000'],

            // Cantidades ajustadas por el admin (opcionales, solo al aprobar)
            'items'                   => ['nullable', 'array'],
            'items.*.id'              => ['required_with:items', 'integer', 'exists:inventory_exit_items,id'],
            'items.*.qty_approved'    => ['required_with:items.*.id', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'               => __('inventoryexit::validation.status_required'),
            'status.in'                     => __('inventoryexit::validation.status_invalid'),
            'approval_note.required'        => __('inventoryexit::validation.approval_note_required'),
            'items.*.qty_approved.min'      => __('inventoryexit::validation.qty_positive'),
        ];
    }
}
