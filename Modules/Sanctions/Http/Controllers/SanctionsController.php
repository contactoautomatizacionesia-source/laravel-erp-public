<?php

namespace Modules\Sanctions\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Modules\Sanctions\Entities\Investigation;
use Modules\Sanctions\Entities\InvestigationEvidence;
use Modules\Sanctions\Entities\AppliedMitigatingFactor;
use Modules\Sanctions\Entities\CatComplaintSource;
use Modules\Sanctions\Entities\CatMitigatingFactor;
use Modules\Sanctions\Entities\CatOffenseType;
use Modules\Sanctions\Entities\CatProcessStatus;
use Modules\Sanctions\Http\Requests\StoreCaseRequest;

class SanctionsController extends Controller
{
    public function index()
    {
        $complaintSources  = CatComplaintSource::where('is_active', true)->orderBy('name')->get();
        $mitigatingFactors = CatMitigatingFactor::where('is_active', true)->get();
        $offenseTypes      = CatOffenseType::where('is_active', true)->orderBy('level')->get();
        $instructors       = User::whereIn('role_id', $this->instructorRoleIds())
                                  ->select('id', 'first_name', 'last_name', 'username')
                                  ->orderBy('first_name')
                                  ->get();

        return view('sanctions::index', compact('complaintSources', 'mitigatingFactors', 'offenseTypes', 'instructors'));
    }

    public function get_data(Request $request)
    {
        if (! $request->ajax()) {
            abort(403);
        }

        $investigations = Investigation::with(['eui', 'offenseType', 'processStatus'])
            ->where('is_active', true)
            ->whereNull('closed_at')
            ->select('investigations.*');

        return DataTables::of($investigations)
            ->addIndexColumn()
            ->addColumn('eui_info', function ($inv) {
                $eui  = $inv->eui;
                $name = $eui ? trim($eui->first_name . ' ' . $eui->last_name) : '—';
                $code = $eui ? $eui->username : '—';
                return '<span class="font-weight-bold">' . e($code) . '</span>'
                     . '<br><small class="text-muted">' . e($name) . '</small>';
            })
            ->addColumn('offense_badge', function ($inv) {
                $type = $inv->offenseType;
                if (! $type) {
                    return '—';
                }
                $class = match ($type->code) {
                    'MINOR'    => 'badge_5',
                    'MODERATE' => 'badge_3',
                    'SEVERE'   => 'badge_2',
                    default    => 'badge_5',
                };
                return '<span class="' . $class . '">' . e($type->name) . '</span>';
            })
            ->addColumn('status_badge', function ($inv) {
                $status = $inv->processStatus;
                if (! $status) {
                    return '—';
                }
                $class = match ($status->code) {
                    'OPEN'             => 'badge_1',
                    'AWAITING_DEFENSE' => 'badge_3',
                    'IN_RESOLUTION'    => 'badge_5',
                    'APPEALED'         => 'badge_2',
                    'CLOSED'           => 'badge_6',
                    default            => 'badge_5',
                };
                return '<span class="' . $class . '">' . e($status->name) . '</span>';
            })
            ->addColumn('opened_at_formatted', function ($inv) {
                return $inv->opened_at ? $inv->opened_at->format('d/m/Y') : '—';
            })
            ->addColumn('action', function ($inv) {
                $id  = $inv->id;
                $sel = __('common.select');
                return '<div class="dropdown CRM_dropdown">'
                    . '<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">'
                    . $sel . '</button>'
                    . '<div class="dropdown-menu dropdown-menu-right">'
                    . '<a class="dropdown-item view_case" href="#" data-id="' . $id . '">'
                    . __('common.details') . '</a>'
                    . '</div></div>';
            })
            ->rawColumns(['eui_info', 'offense_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function get_investigated_customer(Request $request)
    {
        $eui = User::where('username', $request->eui_code)->first();
        if ($eui) {
            $sanctionsCount = Investigation::where('eui_id', $eui->id)
                ->where('is_active', true)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => trim($eui->first_name . ' ' . $eui->last_name),
                    'eui_code' => $eui->username,
                    'plan' => 'Sol', //Simulado
                    'sanctions_count' => $sanctionsCount,
                ],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => __('sanctions.user_not_found', ['code' => request('eui_code')]),
            ], 404);
        }
    }

    public function store(StoreCaseRequest $request)
    {
        // 1. Resolver el EUI (El FormRequest ya garantizó que existe en la DB)
        $eui = User::where('username', $request->investigated_eui_code)->first();

        // 2. Obtener estado inicial OPEN
        $openStatus = CatProcessStatus::where('code', 'OPEN')->first();
        if (! $openStatus) {
            return response()->json([
                'success' => false,
                'error'   => 'Estado OPEN no encontrado. Ejecuta los seeders.',
            ], 500);
        }

        // 3. Contar reincidencias previas del EUI
        $priorCount = Investigation::where('eui_id', $eui->id)
            ->where('is_active', true)
            ->count();

        DB::beginTransaction();
        try {
            // 4. Crear la investigación principal
            $investigation = Investigation::create([
                'id'                  => Str::uuid()->toString(),
                'eui_id'              => $eui->id,
                'instructor_id'       => $request->case_instructor,
                'offense_type_id'     => $request->offence_scale_id,
                'complaint_source_id' => $request->case_complaint_source,
                'process_status_id'   => $openStatus->id,
                'facts_description'   => $request->facts_description,
                'origin_detail'       => $request->sanction_additional_reference,
                'opened_at'           => $request->investigation_start_date ?? today()->toDateString(),
                'offense_count'       => $priorCount + 1,
                'is_active'           => true,
            ]);

            // 5 y 6. Delegar el guardado de relaciones a métodos privados (los que creamos antes)
            $this->saveMitigatingFactors($request, $investigation->id);
            $this->saveEvidences($request, $investigation->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('sanctions.case_created_successfully'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extrae la lógica de guardado de los factores atenuantes.
     */
    private function saveMitigatingFactors(StoreCaseRequest $request, string $investigationId): void
    {
        if (!$request->filled('mitigating_circumstances')) {
            return;
        }

        foreach ($request->mitigating_circumstances as $factorId) {
            AppliedMitigatingFactor::create([
                'id'                   => Str::uuid()->toString(),
                'investigation_id'     => $investigationId,
                'mitigating_factor_id' => $factorId,
                'justification'        => null,
            ]);
        }
    }

    /**
     * Extrae la lógica de iteración y guardado de evidencias.
     */
    private function saveEvidences(StoreCaseRequest $request, string $investigationId): void
    {
        if (!$request->hasFile('evidences')) {
            return;
        }

        foreach ($request->file('evidences') as $file) {
            $path = $file->store('sanctions/evidences', 'public');
            $type = $this->determineFileType($file->getClientOriginalExtension());

            InvestigationEvidence::create([
                'id'               => Str::uuid()->toString(),
                'investigation_id' => $investigationId,
                'uploaded_by_id'   => Auth::id(),
                'file_type'        => $type,
                'file_url'         => $path,
                'description'      => $request->observations,
                'uploaded_at'      => today()->toDateString(),
            ]);
        }
    }

    /**
     * Reemplaza el bloque de ternarios anidados con una función limpia de mapeo.
     */
    private function determineFileType(string $extension): string
    {
        $ext = strtoupper($extension);

        $typeMap = [
            'JPG'  => 'IMAGE',
            'JPEG' => 'IMAGE',
            'PNG'  => 'IMAGE',
            'MP4'  => 'VIDEO',
            'AVI'  => 'VIDEO',
            'MOV'  => 'VIDEO',
            'MP3'  => 'AUDIO',
            'WAV'  => 'AUDIO',
            'PDF'  => 'PDF',
        ];

        return $typeMap[$ext] ?? 'OTHER';
    }

    /**
     * IDs de roles considerados "instructores" (admins / compliance).
     * Ajusta según los role_id reales del sistema.
     */
    private function instructorRoleIds(): array
    {
        return [1, 2];
    }
}
