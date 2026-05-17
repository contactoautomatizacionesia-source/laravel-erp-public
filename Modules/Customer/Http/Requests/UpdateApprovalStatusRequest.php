<?php

namespace Modules\Customer\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateApprovalStatusRequest extends FormRequest
{
    protected ?User $targetCustomer = null;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|integer|exists:users,id',
            'status' => ['required', Rule::in([
                User::APPROVAL_STATUS_APPROVED,
                User::APPROVAL_STATUS_REJECTED,
            ])],
            'reason' => 'nullable|string|max:1000',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (!manualActivation()) {
                $validator->errors()->add('status', __('common.error_message'));
                return;
            }

            $targetCustomer = User::select('id', 'approval_status')
                ->whereHas('role', function ($query) {
                    $query->where('type', 'customer');
                })
                ->find((int) $this->input('id'));

            if (!$targetCustomer) {
                $validator->errors()->add('id', __('common.error_message'));
                return;
            }

            $this->targetCustomer = $targetCustomer;

            $status = (string) $this->input('status');
            $reason = trim((string) $this->input('reason'));
            $currentStatus = $targetCustomer->approval_status ?? User::APPROVAL_STATUS_APPROVED;
            $reasonRequired = $status === User::APPROVAL_STATUS_REJECTED
                || ($status === User::APPROVAL_STATUS_APPROVED && $currentStatus === User::APPROVAL_STATUS_REJECTED);

            if ($reasonRequired && $reason === '') {
                $validator->errors()->add(
                    'reason',
                    $status === User::APPROVAL_STATUS_REJECTED
                        ? __('common.provide_cancellation_reason')
                        : __('common.provide_approval_reason')
                );
            }
        });
    }

    public function targetCustomer(): ?User
    {
        return $this->targetCustomer;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 0,
            'message' => $validator->errors()->first() ?: __('common.error_message'),
        ], 422));
    }
}
