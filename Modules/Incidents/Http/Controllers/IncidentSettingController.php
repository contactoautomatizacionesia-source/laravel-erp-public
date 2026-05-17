<?php

namespace Modules\Incidents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Incidents\Http\Requests\UpdateIncidentSettingRequest;
use Modules\Incidents\Repositories\IncidentSettingRepository;
use Modules\UserActivityLog\Traits\LogActivity;

class IncidentSettingController extends Controller
{
    public function __construct(protected IncidentSettingRepository $repo) {}

    public function index()
    {
        $setting = $this->repo->getInstance();
        return view('incidents::settings', compact('setting'));
    }

    public function update(UpdateIncidentSettingRequest $request): JsonResponse
    {
        try {
            $this->repo->update($request->validated(), auth()->id());
            LogActivity::successLog('Configuración de novedades actualizada.');
            return response()->json([
                'success' => true,
                'message' => __('incidents::messages.settings_updated'),
            ]);
        } catch (\Throwable $e) {
            LogActivity::errorLog('Error al actualizar configuración de novedades: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('incidents::messages.error_generic'),
            ], 500);
        }
    }
}
