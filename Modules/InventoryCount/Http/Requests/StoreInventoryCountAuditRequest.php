<?php

namespace Modules\InventoryCount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryCountAuditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'count_id'     => 'required|exists:inventory_counts,id',
            'audit_status' => 'required|in:rejected,approved',
            'notes'        => 'required|string|max:1000',
        ];
    }
}
