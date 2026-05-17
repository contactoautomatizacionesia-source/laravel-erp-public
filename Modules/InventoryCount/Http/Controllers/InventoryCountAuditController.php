<?php

namespace Modules\InventoryCount\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\InventoryCount\Http\Requests\StoreInventoryCountAuditRequest;
use Modules\InventoryCount\Services\InventoryCountService;
use Yajra\DataTables\Facades\DataTables;

class InventoryCountAuditController extends Controller
{
    public function __construct(
        protected InventoryCountService $service,
    ) {}

    public function index()
    {
        return view('inventorycount::audits.index');
    }

    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            abort(403);
        }

        $query = $this->service->auditRepo->getDatatablesQuery();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('created_at', fn ($row) => $row->created_at
                ? '<span title="' . $row->created_at->format('d/m/Y H:i') . '">' . $row->created_at->diffForHumans() . '</span>'
                : '-')
            ->addColumn('count_code', fn ($row) => optional($row->inventoryCount)->count_code ?? '-')
            ->addColumn('cost_center', fn ($row) => optional($row->inventoryCount?->costCenter)->name ?? '-')
            ->addColumn('asesor', function ($row) {
                $u = $row->inventoryCount?->user;
                return $u ? trim($u->first_name . ' ' . $u->last_name) : '-';
            })
            ->addColumn('auditor_name', function ($row) {
                $u = $row->auditor;
                return $u ? trim($u->first_name . ' ' . $u->last_name) : '-';
            })
            ->addColumn('status_label', fn ($row) => view('inventorycount::audits.partials.status_badge', ['row' => $row])->render())
            ->addColumn('actions', fn ($row) => view('inventorycount::audits.partials.actions', ['row' => $row])->render())
            ->rawColumns(['created_at', 'status_label', 'actions'])
            ->make(true);
    }

    public function show(int $id)
    {
        $audit = $this->service->auditRepo->findById($id);
        return view('inventorycount::audits.show', compact('audit'));
    }

    /**
     * Muestra la modal de revisión (datos para cargar vía AJAX)
     */
    public function getReviewData(int $countId)
    {
        try {
            $data = $this->service->getReviewData($countId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Procesa la auditoría (approved / rejected)
     */
    public function store(StoreInventoryCountAuditRequest $request)
    {
        $result = $this->service->processAudit(
            $request->count_id,
            $request->audit_status,
            $request->notes,
            auth()->id()
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
