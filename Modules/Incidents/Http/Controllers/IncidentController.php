<?php

namespace Modules\Incidents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Incidents\Http\Requests\ResolveIncidentRequest;
use Modules\Incidents\Http\Requests\SubmitStatementRequest;
use Modules\Incidents\Repositories\IncidentRepository;
use Modules\Incidents\Services\CashClosingLinkService;
use Modules\Incidents\Services\EvidenceService;
use Modules\Incidents\Services\ResolutionService;
use Modules\Incidents\Services\StatementService;
use Modules\UserActivityLog\Traits\LogActivity;
use Yajra\DataTables\Facades\DataTables;

class IncidentController extends Controller
{
    public function __construct(
        protected IncidentRepository     $repo,
        protected StatementService       $statementService,
        protected ResolutionService      $resolutionService,
        protected CashClosingLinkService $closingService,
        protected EvidenceService        $evidenceService
    ) {}

    public function index()
    {
        return view('incidents::index');
    }

    public function get_data(Request $request)
    {
        if (! $request->ajax()) {
            abort(403);
        }

        $filters = $request->only([
            'status',
            'incident_type',
            'responsible_branch_id',
            'date_from',
            'date_to',
        ]);

        $query = $this->repo->getBaseQuery($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('type_badge', fn ($i) => $this->renderTypeBadge($i->incident_type))
            ->addColumn('status_badge', fn ($i) => $this->renderStatusBadge($i->status))
            ->addColumn('branch', fn ($i) => $i->responsibleBranch?->name ?? '—')
            ->addColumn('advisor', fn ($i) => $i->responsibleUser?->name ?? '—')
            ->addColumn('total_value_fmt', fn ($i) => '$ ' . number_format($i->total_value, 2, ',', '.'))
            ->addColumn('created_at_fmt', fn ($i) => $i->created_at?->format('d/m/Y H:i') ?? '—')
            ->addColumn('action', fn ($i) => $this->renderActions($i))
            ->rawColumns(['type_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function metrics(): JsonResponse
    {
        return response()->json($this->repo->getMetrics());
    }

    public function show(string $id)
    {
        $incident = $this->repo->findById($id, [
            'responsibleBranch',
            'responsibleUser',
            'originBranch',
            'originUser',
            'resolvedBy',
            'evidences.uploadedBy',
            'auditLogs.user',
            'cashClosingLink',
        ]);

        return view('incidents::show', compact('incident'));
    }

    public function submitStatement(SubmitStatementRequest $request, string $id): JsonResponse
    {
        try {
            // Si es acknowledged, guardar la evidencia primero para que StatementService la encuentre
            if ($request->input('statement_type') === 'acknowledged' && $request->hasFile('file')) {
                $this->evidenceService->upload(
                    $id,
                    $request->file('file'),
                    'origin',
                    $request->input('notes'),
                    auth()->id()
                );
            }

            $incident = $this->statementService->submit(
                $id,
                $request->input('statement_type'),
                $request->input('notes'),
                auth()->id()
            );

            LogActivity::successLog('Pronunciamiento registrado en novedad ' . $incident->sequential_code);
            return response()->json(['success' => true, 'message' => __('incidents::messages.statement_submitted')]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            LogActivity::errorLog('Error al registrar pronunciamiento: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('incidents::messages.error_generic')], 500);
        }
    }

    public function resolve(ResolveIncidentRequest $request, string $id): JsonResponse
    {
        try {
            $incident = $this->resolutionService->resolve(
                $id,
                $request->input('resolution_party'),
                $request->input('resolution_notes'),
                auth()->id()
            );

            LogActivity::successLog('Novedad resuelta: ' . $incident->sequential_code);
            return response()->json(['success' => true, 'message' => __('incidents::messages.resolved')]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            LogActivity::errorLog('Error al resolver novedad: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('incidents::messages.error_generic')], 500);
        }
    }

    public function void(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|min:10']);

        try {
            $this->resolutionService->void($id, $request->input('reason'), auth()->id());
            return response()->json(['success' => true, 'message' => __('incidents::messages.voided')]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => __('incidents::messages.error_generic')], 500);
        }
    }

    public function linkClosing(Request $request, string $id): JsonResponse
    {
        $request->validate(['cash_closing_id' => 'required|integer']);

        try {
            $this->closingService->link($id, $request->input('cash_closing_id'), auth()->id());
            return response()->json(['success' => true, 'message' => __('incidents::messages.linked_to_closing')]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => __('incidents::messages.error_generic')], 500);
        }
    }

    // ─── Helpers de renderizado ───────────────────────────────────────────────

    private function renderTypeBadge(string $type): string
    {
        $map = [
            'transfer'        => __('incidents::messages.type_transfer'),
            'inventory_count' => __('incidents::messages.type_inventory_count'),
        ];
        $label = $map[$type] ?? $type;
        $class = $type == 'transfer' ? 'badge_5' : 'badge_6';
        return '<span class=" ' . $class . '">' . $label . '</span>';
    }

    private function renderStatusBadge(string $status): string
    {
        $map = [
            'pending'              => ['label' => __('incidents::messages.status_pending'),       'class' => 'badge_3'],
            'awaiting_statement'   => ['label' => __('incidents::messages.status_awaiting'),      'class' => 'badge_2'],
            'under_investigation'  => ['label' => __('incidents::messages.status_investigating'), 'class' => 'badge_5'],
            'closed'               => ['label' => __('incidents::messages.status_closed'),        'class' => 'badge_1'],
            'voided'               => ['label' => __('incidents::messages.status_voided'),        'class' => 'badge_6'],
        ];
        $info = $map[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
        return '<span class=" ' . $info['class'] . '">' . $info['label'] . '</span>';
    }

    private function renderActions($incident): string
    {
        $url = route('incidents.show', $incident->id);
        return '<a href="' . $url . '" class="btn btn-sm btn-primary" title="' . __('incidents::messages.view_detail') . '">
                    <i class="ti-eye" style="color:#fff;"></i>
                </a>';
    }
}
