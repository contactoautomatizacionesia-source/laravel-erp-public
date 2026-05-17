<?php

namespace Modules\InventoryCount\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\InventoryCount\Entities\InventoryCount;
use Modules\InventoryCount\Services\InventoryCountService;
use Yajra\DataTables\Facades\DataTables;

class InventoryCountController extends Controller
{
    public function __construct(
        protected InventoryCountService $service,
    ) {}

    public function index()
    {
        $user        = auth()->user();
        $isAdmin     = in_array($user->role->type, ['superadmin', 'admin']);
        $canCreate   = permissionCheck('inventory_count.create');

        if ($canCreate && !$isAdmin && $user->cost_center_id) {
            $ccId    = $user->cost_center_id;
            $setting = $this->service->settingRepo->findByCostCenter($ccId);

            // Ocultar si ya tiene un conteo aprobado hoy
            $approvedToday = InventoryCount::where('cost_center_id', $ccId)
                ->where('audit_status', 'approved')
                ->whereDate('updated_at', today())
                ->exists();

            if ($approvedToday) {
                $canCreate = false;
            }

            if ($canCreate) {
                $canCreate = !$this->isDailyLimitExceeded($ccId, $setting?->max_attempts ?? 0);
            }
        }

        return view('inventorycount::counts.index', compact('canCreate'));
    }

    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            abort(403);
        }

        $user    = auth()->user();
        $isAdmin = in_array($user->role->type, ['superadmin', 'admin']);

        // Para no-admin: respetar allow_history_view del setting de su centro de costo
        $allowHistory = true;
        if (!$isAdmin && $user->cost_center_id) {
            $setting = $this->service->settingRepo->findByCostCenter($user->cost_center_id);
            $allowHistory = $setting?->allow_history_view ?? true;
        }

        $query = $this->service->repo->getDatatablesQuery($isAdmin, $user->id, $allowHistory);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('created_at', fn ($row) => $row->created_at
                ? '<span title="' . $row->created_at->format('d/m/Y H:i') . '">' . $row->created_at->diffForHumans() . '</span>'
                : '-')
            ->addColumn('cost_center', fn ($row) => optional($row->costCenter)->name ?? '-')
            ->addColumn('responsible', function ($row) {
                $u = $row->user;
                return $u ? trim($u->first_name . ' ' . $u->last_name) : '-';
            })
            ->addColumn('total_attempts', fn ($row) => $row->total_attempts ?? 1)
            ->addColumn('status_label', fn ($row) => view('inventorycount::counts.partials.status_badge', ['row' => $row])->render())
            ->addColumn('audit_status_label', fn ($row) => view('inventorycount::counts.partials.audit_badge', ['row' => $row])->render())
            ->addColumn('actions', fn ($row) => view('inventorycount::counts.partials.actions', [
                'row'     => $row,
                'isAdmin' => in_array(auth()->user()->role->type, ['superadmin', 'admin']),
            ])->render())
            ->rawColumns(['created_at', 'status_label', 'audit_status_label', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $user         = auth()->user();
        $costCenterId = (int) $user->cost_center_id;

        $blockReason = $this->getCreateBlockReason($costCenterId);
        if ($blockReason) {
            return redirect()->route('inventory_count.index')->with('error', $blockReason);
        }

        $data = $this->service->getCountFormData($costCenterId);
        $data['costCenterId'] = $costCenterId;

        return view('inventorycount::counts.create', $data);
    }

    private function getCreateBlockReason(int $costCenterId): ?string
    {
        if (!$costCenterId) {
            return __('inventorycount::messages.no_cost_center_assigned');
        }

        $approvedToday = InventoryCount::where('cost_center_id', $costCenterId)
            ->where('audit_status', 'approved')
            ->whereDate('updated_at', today())
            ->exists();

        $setting     = $this->service->settingRepo->findByCostCenter($costCenterId);
        $maxAttempts = $setting?->max_attempts ?? 0;

        $limitExceeded = !$approvedToday && $this->isDailyLimitExceeded($costCenterId, $maxAttempts);

        return match (true) {
            $approvedToday => __('inventorycount::messages.count_already_approved_today'),
            $limitExceeded => __('inventorycount::messages.count_limit_exceeded'),
            default        => null,
        };
    }

    private function isDailyLimitExceeded(int $costCenterId, int $maxAttempts): bool
    {
        if ($maxAttempts <= 0) {
            return false;
        }

        $usedToday = $this->service->repo->countTodayAttemptsForCenter($costCenterId);

        return $usedToday >= $maxAttempts
            && !$this->service->repo->hasPendingRecountToday($costCenterId, $maxAttempts);
    }

    public function show(int $id)
    {
        $count   = $this->service->repo->findById($id);
        $isAdmin = in_array(auth()->user()->role->type, ['superadmin', 'admin']);

        if (!$isAdmin && $count->user_id !== auth()->id()) {
            abort(403);
        }

        $siblings        = $this->service->repo->findGroupSiblings($count);
        $approvedSibling = $count->audit_status === 'closed'
            ? $this->service->repo->findApprovedSibling($count)
            : null;

        return view('inventorycount::counts.show', compact('count', 'isAdmin', 'siblings', 'approvedSibling'));
    }
}
