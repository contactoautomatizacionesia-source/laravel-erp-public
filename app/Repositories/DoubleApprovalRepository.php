<?php

namespace App\Repositories;

use App\Models\PendingApproval;
use Illuminate\Support\Collection;

class DoubleApprovalRepository
{
    public function findByAssignedApproverId(int $assignedApproverId): Collection
    {
        return PendingApproval::where('assigned_approver_id', $assignedApproverId)->get();
    }

    public function findByHash(string $hash)
    {
        return PendingApproval::where('hash', $hash)->first();
    }

    public function findById(int $id)
    {
        return PendingApproval::find($id);
    }

    public function hasPending(string $module, string $actionType)
    {
        return PendingApproval::where('module', $module)
            ->where('action_type', $actionType)
            ->where('status', 0) // Solo pendientes
            ->exists();
    }

    public function create(array $data)
    {
        return PendingApproval::create($data);
    }

    public function updateStatus(int $id, int $status, int $assignedApproverId, ?string $rejectionReason = null)
    {
        return PendingApproval::where('id', $id)->update([
            'status' => $status,
            'assigned_approver_id' => $assignedApproverId,
            'rejection_reason' => $rejectionReason,
            'updated_at' => now()
        ]);
    }
}
