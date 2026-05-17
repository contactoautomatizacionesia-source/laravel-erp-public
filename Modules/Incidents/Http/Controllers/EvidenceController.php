<?php

namespace Modules\Incidents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Incidents\Services\EvidenceService;
use Modules\UserActivityLog\Traits\LogActivity;

class EvidenceController extends Controller
{
    public function __construct(protected EvidenceService $evidenceService) {}

    public function store(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'file_evidence' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'actor_role' => 'required|in:destination,origin,admin',
            'notes'      => 'nullable|string|max:1000',
        ]);

        try {
            $evidence = $this->evidenceService->upload(
                $id,
                $request->file('file_evidence'),
                $request->input('actor_role'),
                $request->input('notes'),
                auth()->id()
            );

            return response()->json([
                'success'  => true,
                'message'  => __('incidents::messages.evidence_uploaded'),
                'evidence' => [
                    'id'        => $evidence->id,
                    'file_name' => $evidence->file_name,
                    'file_url'  => $evidence->file_url,
                    'notes'     => $evidence->notes,
                    'is_image'  => $evidence->isImage(),
                ],
            ]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            LogActivity::errorLog('Error al subir evidencia: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('incidents::messages.error_generic')], 500);
        }
    }
}
