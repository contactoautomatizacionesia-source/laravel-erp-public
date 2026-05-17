<?php

namespace Modules\InventoryCount\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CostCenter\Entities\CostCenter;
use Modules\InventoryCount\Http\Requests\StoreInventoryCountSettingRequest;
use Modules\InventoryCount\Services\InventoryCountService;
use Yajra\DataTables\Facades\DataTables;

class InventoryCountSettingController extends Controller
{
    public function __construct(
        protected InventoryCountService $service,
    ) {}

    public function index()
    {
        $data = $this->service->getSettingsIndexData();
        return view('inventorycount::settings.index', $data);
    }

    public function getData(Request $request)
    {
        if (!$request->ajax()) {
            abort(403);
        }

        $query = $this->service->settingRepo->getAllWithCostCenter();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('cost_center_name', fn ($row) => optional($row->costCenter)->name . ' (' . optional($row->costCenter)->code . ')')
            ->addColumn('role_name', fn ($row) => optional($row->countRole)->name ?? '-')
            ->addColumn('max_attempts_label', function ($row) {
                return $row->max_attempts === 0
                    ? '<span class="badge_5">' . __('inventorycount::messages.unlimited') . '</span>'
                    : __('inventorycount::messages.attempts_label', ['n' => $row->max_attempts]);
            })
            ->addColumn('allow_history_label', function ($row) {
                return $row->allow_history_view
                    ? '<span class="badge_1">' . __('common.yes') . '</span>'
                    : '<span class="badge_2">' . __('common.no') . '</span>';
            })
            ->addColumn('actions', fn ($row) => view('inventorycount::settings.partials.actions', ['row' => $row])->render())
            ->rawColumns(['max_attempts_label', 'allow_history_label', 'actions'])
            ->make(true);
    }

    public function edit(int $costCenterId)
    {
        $setting    = $this->service->settingRepo->findByCostCenter($costCenterId);
        $costCenter = CostCenter::withTrashed()->findOrFail($costCenterId);

        $payload = $setting ? $setting->toArray() : null;
        if ($payload) {
            $payload['cost_center_name'] = optional($costCenter)->name . ' (' . optional($costCenter)->code . ')';
        }

        return response()->json(['success' => true, 'setting' => $payload]);
    }

    public function store(StoreInventoryCountSettingRequest $request)
    {
        $this->service->saveSetting($request->cost_center_id, [
            'count_role_id'      => $request->count_role_id,
            'max_attempts'       => $request->max_attempts,
            'allow_history_view' => $request->boolean('allow_history_view'),
            'notify_user_ids'    => $request->notify_user_ids ?? [],
        ]);

        return response()->json(['success' => true, 'message' => __('common.saved_successfully')]);
    }
}
