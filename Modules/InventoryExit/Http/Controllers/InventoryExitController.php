<?php

namespace Modules\InventoryExit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Modules\UserActivityLog\Traits\LogActivity;
use Modules\InventoryExit\Services\InventoryExitService;
use Modules\InventoryExit\Entities\InventoryExitRequest;
use Modules\InventoryExit\Http\Requests\StoreInventoryExitRequest;
use Modules\InventoryExit\Http\Requests\ApproveInventoryExitRequest;

class InventoryExitController extends Controller
{
    public function __construct(private InventoryExitService $service) {}

    // ---------------------------------------------------------------
    // Index + DataTable
    // ---------------------------------------------------------------

    public function index()
    {
        $user        = auth()->user();
        $isGlobal    = in_array($user->role_id, [1, 2]) || empty($user->cost_center_id);
        $costCenters = $isGlobal
            ? $this->service->getCostCenters()
            : $this->service->getCostCenters()->where('id', $user->cost_center_id);

        return view('inventoryexit::index', compact('costCenters', 'isGlobal'));
    }

    public function getData(Request $request)
    {
        $query = $this->service->repo->getDataTableQuery();

        if ($status = $request->input('status_filter')) {
            $query->where('status', $status);
        }

        if ($locationFilter = $request->input('cost_center_filter')) {
            [$locationType, $locationId] = $this->service->resolveLocation($locationFilter);
            $query->where('location_type', $locationType)
                  ->where('location_id', $locationId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('exit_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('exit_date', '<=', $dateTo);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('exit_date_col', fn($row) => $this->formatDate($row->exit_date))
            ->addColumn('exit_reason_col', fn($row) => $row->exitReason?->name ?? '—')
            ->addColumn('products_col', fn($row) => $this->formatProductsSummary($row))
            ->addColumn('cost_center_col', fn($row) => $row->locationLabel())
            ->addColumn('requested_by_col', fn($row) => $this->formatUser($row->requestedBy))
            ->addColumn('approved_by_col', fn($row) => $this->formatUser($row->approvedBy))
            ->addColumn('status_col', fn($row) => $this->formatStatus($row->status))
            ->addColumn('actions', fn($row) => $this->formatActions($row))
            ->rawColumns(['exit_date_col', 'products_col', 'status_col', 'requested_by_col', 'approved_by_col', 'actions'])
            ->make(true);
    }

    // ---------------------------------------------------------------
    // Crear solicitud
    // ---------------------------------------------------------------

    public function store(StoreInventoryExitRequest $request)
    {
        try {
            $exitRequest = $this->service->createRequest($request->validated(), $request);

            LogActivity::successLog('Solicitud de salida de inventario creada. ID: ' . $exitRequest->id);

            return response()->json([
                'success' => true,
                'message' => __('inventoryexit::messages.request_created'),
            ]);
        } catch (\Throwable $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ---------------------------------------------------------------
    // Aprobar / Rechazar
    // ---------------------------------------------------------------

    public function approve(ApproveInventoryExitRequest $request, int $id)
    {
        $exitRequest = $this->service->repo->findOrFail($id);

        if (!$exitRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => __('inventoryexit::messages.already_processed'),
            ], 422);
        }

        try {
            $this->service->processDecision($exitRequest, $request->validated(), $request);

            LogActivity::successLog('Solicitud de salida ID: ' . $id . ' — estado: ' . $request->input('status'));

            return response()->json([
                'success' => true,
                'message' => $request->input('status') === 'approved'
                    ? __('inventoryexit::messages.request_approved')
                    : __('inventoryexit::messages.request_rejected'),
            ]);
        } catch (\Throwable $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ---------------------------------------------------------------
    // Detalle (parcial para modal)
    // ---------------------------------------------------------------

    public function detail(int $id)
    {
        $exitRequest = $this->service->repo->findOrFail($id);

        return view('inventoryexit::partials._detail_modal', compact('exitRequest'));
    }

    // ---------------------------------------------------------------
    // AJAX helpers
    // ---------------------------------------------------------------

    /**
     * Lotes disponibles para un SKU en una ubicación dada.
     */
    public function getLocationLots(Request $request)
    {
        $request->validate([
            'location_type' => ['required', 'in:main,cost_center'],
            'location_id'   => ['nullable', 'integer'],
            'sku_id'        => ['required', 'integer', 'exists:product_sku,id'],
        ]);

        $lots = $this->service->getLocationLots(
            $request->input('location_type'),
            $request->input('location_id'),
            $request->input('sku_id'),
        );

        return response()->json($lots);
    }

    /**
     * Búsqueda de productos/SKUs con stock en la ubicación seleccionada.
     */
    public function searchProducts(Request $request)
    {
        $request->validate([
            'term'          => ['required', 'string', 'min:2'],
            'location_type' => ['required', 'in:main,cost_center'],
            'location_id'   => ['nullable', 'integer'],
        ]);

        $locationType = $request->input('location_type');
        $locationId   = $request->input('location_id');
        $term         = $request->input('term');

        $results = $this->service->searchSkusWithStock($locationType, $locationId, $term, 15)
            ->map(fn($lot) => [
                'sku_id'       => $lot->product_sku_id,
                'sku'          => $lot->productSku?->sku,
                'product_name' => $lot->productSku?->product?->product_name,
            ]);

        return response()->json($results);
    }

    /**
     * SKUs con stock en una ubicación — para nice-select-ajax.
     * Recibe: location ("main"|"center-{id}"), search (opcional).
     * Devuelve: [{id, name}]
     */
    public function getSkusByLocation(Request $request)
    {
        $request->validate([
            'location' => ['required', 'string'],
            'search'   => ['nullable', 'string'],
        ]);

        $location = $request->input('location');
        $search   = $request->input('search', '');

        [$locationType, $locationId] = $this->service->resolveLocation($location);

        $results = $this->service->searchSkusWithStock($locationType, $locationId, $search, 50)
            ->map(fn($lot) => [
                'id'   => $lot->product_sku_id,
                'name' => trim(($lot->productSku?->product?->product_name ?? '') . ' (' . ($lot->productSku?->sku ?? '') . ')'),
            ]);

        return response()->json($results);
    }

    /**
     * Retorna los motivos de salida activos para el select.
     */
    public function getReasons()
    {
        return response()->json($this->service->getActiveReasons());
    }

    // ---------------------------------------------------------------
    // Helpers privados — formateo de columnas DataTable
    // ---------------------------------------------------------------

    private function formatDate($date): string
    {
        if (!$date) { return '—'; }
        $carbon = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $carbon->format('d/m/Y');
    }

    private function formatProductsSummary(InventoryExitRequest $row): string
    {
        $count = $row->items->count();
        if ($count === 0) { return '—'; }

        $first = $row->items->first();
        $name  = $first->productSku?->product?->product_name ?? '—';
        $sku   = $first->productSku?->sku ?? '';
        $label = $sku ? "{$name} ({$sku})" : $name;

        return $count > 1
            ? $label . ' <span class="badge_5">+' . ($count - 1) . '</span>'
            : $label;
    }

    private function formatUser($user): string
    {
        if (!$user) { return '—'; }
        return e($user->name);
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'pending'  => '<span class="badge_3">' . __('inventoryexit::messages.status_pending') . '</span>',
            'approved' => '<span class="badge_1">' . __('inventoryexit::messages.status_approved') . '</span>',
            'rejected' => '<span class="badge_2">' . __('inventoryexit::messages.status_rejected') . '</span>',
            default    => '<span class="badge_5">' . e($status) . '</span>',
        };
    }

    private function formatActions(InventoryExitRequest $row): string
    {
        $detailBtn = '<button class="btn-toolkit btn-secondary-outline btn-sm view_exit_detail" data-id="' . $row->id . '">'
            . '<i class="ti-eye mr-1"></i> ' . __('inventoryexit::messages.detail')
            . '</button>';

        $approveBtn = '';
        if ($row->isPending()) {
            $approveBtn = ' <button class="btn-toolkit btn-primary btn-sm change_exit_status" data-id="' . $row->id . '">'
                . '<i class="ti-check mr-1"></i> ' . __('inventoryexit::messages.change_status')
                . '</button>';
        }

        return $detailBtn . $approveBtn;
    }
}
