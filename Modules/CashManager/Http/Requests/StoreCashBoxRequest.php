<?php

namespace Modules\CashManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\CashManager\Entities\CashBox;

class StoreCashBoxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vaultExists = CashBox::where('type', 'VAULT')->exists();

        return [
            // CC requerido solo cuando ya existe VAULT (para PRINCIPAL/AUXILIARY)
            'cost_center_id'  => $vaultExists ? 'required|integer|exists:cost_centers,id' : 'nullable|integer|exists:cost_centers,id',
            'name'            => 'required|string|max:255',
            'base_amount'     => 'required|numeric|min:0',
            'alert_threshold' => 'nullable|numeric|min:0',
        ];
    }
}
