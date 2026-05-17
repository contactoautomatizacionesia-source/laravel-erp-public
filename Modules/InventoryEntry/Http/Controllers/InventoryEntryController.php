<?php

namespace Modules\InventoryEntry\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\InventoryEntry\Entities\InventoryEntry;
use Modules\InventoryEntry\Entities\ProductLot;
use Modules\InventoryEntry\Http\Requests\StoreInventoryEntryRequest;
use Modules\InventoryEntry\Http\Requests\UpdateInventoryEntryRequest;
use Modules\InventoryEntry\Http\Requests\DeleteInventoryEntryRequest;
use Modules\InventoryEntry\Services\InventoryEntryService;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductSku;
use Modules\UserActivityLog\Traits\LogActivity;
use Yajra\DataTables\Facades\DataTables;

class InventoryEntryController extends Controller
{
    public function __construct(protected InventoryEntryService $service) {}

    // ─── Vistas ───────────────────────────────────────────────────

    public function index()
    {
        return view('inventoryentry::inventory.index');
    }

    public function create()
    {
        return view('inventoryentry::inventory.create');
    }

    // ─── DataTable ────────────────────────────────────────────────

    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        $tableType = $request->get('table', 'active');

        $query = InventoryEntry::with([
                'lot',
                'productSku.product',
                'productSku.product_variations',
                'createdBy',
                'latestDeletedAudit.responsible',
                'latestModifiedAudit.responsible',
            ])
            ->select('product_inventory_entries.*');

        if ($tableType === 'deleted') {
            $query->onlyTrashed();
        } elseif ($tableType === 'modified') {
            $query->whereHas('audits', fn($q) => $q->where('action', 'modified'));
        }

        $this->applyProductFilter($query, $request);
        $this->applyLotFilter($query, $request);
        $this->applyStatusFilter($query, $request);
        $this->applyDateRangeFilter($query, $request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('product_name', fn($row) => $this->formatProductName($row))
            ->addColumn('sku_variant', fn($row) => $this->formatSkuVariant($row))
            ->addColumn('lot_number', fn($row) => $row->lot?->lot_number ?? 'N/A')
            ->addColumn('manufacture_date', fn($row) => $this->formatDate($row->lot?->manufacture_date))
            ->addColumn('expiration_date', fn($row) => $this->formatDate($row->lot?->expiration_date))
            ->addColumn('status_badge', fn($row) => $this->formatStatusBadge($row))
            ->addColumn('created_by_name', fn($row) => $row->createdBy ? trim($row->createdBy->first_name . ' ' . $row->createdBy->last_name) : 'N/A')
            ->addColumn('entry_date', fn($row) => '<span title="' . $row->created_at->format('Y-m-d H:i') . '">' . $row->created_at->diffForHumans() . '</span>')
            ->addColumn('deleted_by', function ($row) {
                $responsible = $row->latestDeletedAudit?->responsible;
                return $responsible ? trim($responsible->first_name . ' ' . $responsible->last_name) : 'N/A';
            })
            ->addColumn('deleted_notes', fn($row) => $row->latestDeletedAudit?->notes ?? '-')
            ->addColumn('deleted_date_long', function ($row) {
                $audit = $row->latestDeletedAudit;
                return $audit?->created_at?->isoFormat('LLLL') ?? '-';
            })
            ->addColumn('deleted_ip', fn($row) => $row->latestDeletedAudit?->ip_address ?? '-')
            ->addColumn('deleted_agent', fn($row) => $row->latestDeletedAudit?->user_agent ?? '-')
            ->addColumn('actions', fn($row) => view('inventoryentry::inventory.partials.entry_actions', ['row' => $row, 'table' => $tableType])->render())
            ->rawColumns(['sku_variant', 'manufacture_date', 'expiration_date', 'status_badge', 'entry_date', 'actions'])
            ->make(true);
    }

    // ─── Guardar ingreso ──────────────────────────────────────────

    public function store(StoreInventoryEntryRequest $request)
    {
        try {
            $entries = $this->service->createMany($request->validated()['entries']);

            $lots = collect($entries)->map(fn($e) => $e->lot->lot_number ?? '?')->unique()->implode(', ');
            LogActivity::successLog(__('inventoryentry::inventory.created_success') . " — {$lots} (" . count($entries) . " " . __('common.records') . ")");

            return response()->json([
                'success' => true,
                'message' => __('inventoryentry::inventory.created_success'),
            ]);
        } catch (\Exception $e) {
            LogActivity::errorLog('InventoryEntry store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Detalle ──────────────────────────────────────────────────

    public function detail($id)
    {
        $entry = InventoryEntry::withTrashed()->with([
            'lot.entries' => fn($q) => $q->withTrashed(),
            'lot.entries.productSku.product',
            'lot.entries.createdBy',
            'productSku.product',
            'productSku.product_variations.attribute',
            'productSku.product_variations.attribute_value',
            'createdBy',
            'latestModifiedAudit.responsible',
            'latestDeletedAudit.responsible',
        ])->findOrFail($id);

        $statusBadge = $this->service->getLotStatusBadge($entry->lot);

        $latestModifiedAudit = $entry->latestModifiedAudit;
        $latestDeletedAudit = $entry->latestDeletedAudit;

        return view('inventoryentry::inventory.partials.entry_detail_modal', compact('entry', 'statusBadge', 'latestModifiedAudit', 'latestDeletedAudit'))->render();
    }

    public function edit($id)
    {
        $entry = InventoryEntry::with(['lot'])->findOrFail($id);

        try {
            $this->service->ensureCanMutate($entry);
            $canEdit = true;
            $message = null;
        } catch (\Exception $e) {
            $canEdit = false;
            $message = $e->getMessage();
        }

        return response()->json([
            'can_edit' => $canEdit,
            'message' => $message,
            'entry' => [
                'id' => $entry->id,
                'quantity' => $entry->quantity,
                'unit_cost' => $entry->unit_cost,
                'supplier' => $entry->supplier,
                'notes' => $entry->notes,
                'manufacture_date' => $entry->lot?->manufacture_date?->format('Y-m-d'),
                'expiration_date' => $entry->lot?->expiration_date?->format('Y-m-d'),
            ],
        ]);
    }

    public function update(UpdateInventoryEntryRequest $request, $id)
    {
        try {
            $entry = InventoryEntry::findOrFail($id);
            $auditNotes = $request->get('audit_notes');

            $this->service->updateEntry($entry, $request->validated(), $auditNotes, [
                'responsible_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            LogActivity::successLog(__('inventoryentry::inventory.updated_success'));

            return response()->json([
                'success' => true,
                'message' => __('inventoryentry::inventory.updated_success'),
            ]);
        } catch (\Exception $e) {
            LogActivity::errorLog('InventoryEntry update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(DeleteInventoryEntryRequest $request, $id)
    {
        try {
            $entry = InventoryEntry::findOrFail($id);
            $auditNotes = $request->get('audit_notes');

            $this->service->deleteEntry($entry, $auditNotes, [
                'responsible_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            LogActivity::successLog(__('inventoryentry::inventory.deleted_success'));

            return response()->json([
                'success' => true,
                'message' => __('inventoryentry::inventory.deleted_success'),
            ]);
        } catch (\Exception $e) {
            LogActivity::errorLog('InventoryEntry delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Columnas DataTable ───────────────────────────────────────

    private function formatProductName($row): string
    {
        if (!$row->productSku?->product) {
            return 'N/A';
        }
        $name = $row->productSku->product->product_name;
        $decoded = json_decode($name, true);
        return is_array($decoded) ? ($decoded[app()->getLocale()] ?? reset($decoded) ?? $name) : $name;
    }

    private function formatSkuVariant($row): string
    {
        if (!$row->productSku) {
            return 'N/A';
        }
        $track = $row->productSku->track_sku ?? '';
        $fallback = $row->productSku->sku ?: 'N/A';
        return $track ? "<span class=\"badge_5 d-inline-block\">{$track}</span>" : $fallback;
    }

    private function formatDate($date): string
    {
        if (!$date) {
            return '—';
        }
        return '<span title="' . $date->format('Y-m-d') . '">' . $date->diffForHumans() . '</span>';
    }

    private function formatStatusBadge($row): string
    {
        if (!$row->lot) {
            return '';
        }
        $badge = $this->service->getLotStatusBadge($row->lot);
        return '<span class="' . $badge['class'] . '">' . $badge['label'] . '</span>';
    }

    // ─── Filtros DataTable ────────────────────────────────────────

    private function applyProductFilter($query, Request $request): void
    {
        if (!$request->filled('product_filter')) {
            return;
        }
        $search = $request->product_filter;
        $query->where(fn($q) => $q
            ->whereHas('productSku.product', fn($q2) => $q2->where('product_name', 'like', "%{$search}%"))
            ->orWhereHas('productSku', fn($q2) => $q2->where('sku', 'like', "%{$search}%"))
        );
    }

    private function applyLotFilter($query, Request $request): void
    {
        if (!$request->filled('lot_filter')) {
            return;
        }
        $query->whereHas('lot', fn($q) => $q->where('lot_number', 'like', '%' . $request->lot_filter . '%'));
    }

    private function applyStatusFilter($query, Request $request): void
    {
        if (!$request->filled('status_filter')) {
            return;
        }
        $today = Carbon::today();
        $status = $request->status_filter;

        $map = [
            'vigente'    => fn($q) => $q->where(fn($q2) => $q2->whereNull('expiration_date')->orWhere('expiration_date', '>', $today->copy()->addDays(30))),
            'por_vencer' => fn($q) => $q->whereBetween('expiration_date', [$today, $today->copy()->addDays(30)]),
            'vencido'    => fn($q) => $q->where('expiration_date', '<', $today),
        ];

        if (isset($map[$status])) {
            $query->whereHas('lot', $map[$status]);
        }
    }

    private function applyDateRangeFilter($query, Request $request): void
    {
        if ($request->filled('exp_from')) {
            $query->whereHas('lot', fn($q) => $q->where('expiration_date', '>=', $request->exp_from));
        }
        if ($request->filled('exp_to')) {
            $query->whereHas('lot', fn($q) => $q->where('expiration_date', '<=', $request->exp_to));
        }
    }

    // ─── AJAX helpers ─────────────────────────────────────────────

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');

        $products = Product::with('skus')
            ->where('status', 1)
            ->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhereHas('skus', fn($q2) => $q2->where('sku', 'like', "%{$search}%"));
            })
            ->select('id', 'product_name', 'product_type')
            ->limit(15)
            ->get()
            ->map(function ($product) {
                $name = $product->product_name;
                $decoded = json_decode($name, true);
                $displayName = is_array($decoded) ? ($decoded[app()->getLocale()] ?? reset($decoded) ?? $name) : $name;

                return [
                    'id'           => $product->id,
                    'text'         => $displayName,
                    'product_type' => $product->product_type,
                ];
            });

        return response()->json(['results' => $products]);
    }

    public function getProductSkus($productId)
    {
        $skus = ProductSku::where('product_id', $productId)
            ->where('status', 1)
            ->get()
            ->map(function ($sku) {
                $label = $sku->track_sku ?: ($sku->sku ?: "SKU #{$sku->id}");
                return [
                    'id'           => $sku->id,
                    'text'         => $label,
                    'product_stock'=> $sku->product_stock,
                ];
            });

        return response()->json(['skus' => $skus]);
    }

    public function findLot(Request $request)
    {
        $lot = ProductLot::where('lot_number', $request->get('lot_number'))->first();

        if (!$lot) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'            => true,
            'manufacture_date' => $lot->manufacture_date?->format('Y-m-d'),
            'expiration_date'  => $lot->expiration_date?->format('Y-m-d'),
        ]);
    }
}
