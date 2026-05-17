<?php
namespace Modules\Plans\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Plans\Services\PlanService;
use Modules\Plans\Repositories\PlanRepository;
use Modules\Plans\Entities\Plan;
use Modules\Plans\Entities\PlanScale;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\UserActivityLog\Traits\LogActivity;
use App\Exceptions\InvalidSvgException;
use Exception;

class PlansController extends Controller
{
    protected $planService;
    protected $planRepository;

    public function __construct(PlanService $planService, PlanRepository $planRepository)
    {
        $this->planService    = $planService;
        $this->planRepository = $planRepository;
    }

    public function index()
    {
        return view('plans::index');
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $plans = $this->planRepository->getBaseQuery();

            return DataTables::of($plans)
                
                ->addIndexColumn()
                ->addColumn('order_drag', function () {
                    return '<span class="drag-handle" title="Arrastrar para reordenar"><i class="ti-menu"></i></span>';
                })
                ->addColumn('order_number', function ($plan) {
                    return $plan->order;
                })
                ->addColumn('image_col', fn ($plan) => $this->renderIconCell($plan))
                ->addColumn('color_col', fn ($plan) => $this->renderColorCell($plan))
                ->addColumn('title', function ($plan) {
                    return $plan->title;
                })
                ->addColumn('network_type_badge', function ($plan) {
                    return $plan->is_life_title
                        ? '<span class="badge_1">' . __('common.red_life') . '</span>'
                        : '<span class="badge_4">' . __('common.red_no_life') . '</span>';
                })
                ->addColumn('scale_detail', function ($plan) {
                    if ($plan->scale_type === 'CUMULATIVE') {
                        return __('common.scale_type_cumulative');
                    }
                    return __('common.scale_type_cycle');
                })
                ->addColumn('children_badge', function ($plan) {
                    return '<span class="badge_5">' . $plan->plan_children_count . '</span>';
                })
                ->addColumn('status_badge', function ($plan) {
                    return $plan->is_active
                        ? '<span class="badge_1">'.__('common.active').'</span>'
                        : '<span class="badge_4">'.__('common.inactive').'</span>';
                })
                ->addColumn('action', function ($plan) {
                    return '<div class="dropdown CRM_dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                            ' . __('common.select') . '
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="' . route('plans.children.index', $plan->id) . '"></i>'.
                            __('common.manage_subplans').'</a><a class="dropdown-item edit_plan" href="#" data-value="' . $plan->id . '">' . __('common.edit') .
                            '</a><a class="dropdown-item delete_plan" href="#" data-value="' . $plan->id . '">' . __('common.delete') . '</a>
                        </div>
                    </div>';
                })
                ->rawColumns(['order_drag', 'image_col', 'color_col', 'network_type_badge', 'children_badge', 'status_badge', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'              => 'required|max:100',
            'order'              => 'required|integer|min:1',
            'scale_type'         => 'required|in:CYCLE,CUMULATIVE',
            'is_life_title'      => 'required|boolean',
            'image'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'primary_color'      => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
            'icon'               => ['nullable', 'string', 'max:65535', 'regex:/^\s*<svg[\s>]/i'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $data          = $request->all();
            $data['image'] = $request->file('image');
            $this->planService->storePlan($data);
            LogActivity::successLog('Plan padre creado: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.plan_created_correctly')]);
        } catch (Exception $e) {
            return $this->handlePlanException($e, 'Error al crear plan: ');
        }
    }

    public function edit($id)
    {
        $plan              = $this->planRepository->findById($id);
        $data              = $plan->toArray();
        $data['image_url'] = $plan->image ? asset(asset_path($plan->image)) : null;
        $data['icon']      = optional($plan->styles)['icon'] ?? null;
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'              => 'required|max:100',
            'order'              => 'required|integer|min:1',
            'scale_type'         => 'required|in:CYCLE,CUMULATIVE',
            'is_life_title'      => 'required|boolean',
            'image'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'primary_color'      => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
            'icon'               => ['nullable', 'string', 'max:65535', 'regex:/^\s*<svg[\s>]/i'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $data                 = $request->all();
            $data['image']        = $request->file('image');
            $data['remove_image'] = $request->input('remove_image') === '1';
            $data['remove_icon']  = $request->input('remove_icon') === '1';
            $this->planService->updatePlan($id, $data);
            LogActivity::successLog('Plan padre actualizado: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.plan_updated_succesfully')]);
        } catch (Exception $e) {
            return $this->handlePlanException($e, 'Error al actualizar plan: ');
        }
    }

    public function destroy($id)
    {
        try {
            $this->planService->deletePlan($id);
            LogActivity::successLog('Plan padre eliminado - ID: ' . $id);
            return response()->json(['success' => true, 'message' => __('common.plan_deleted_succesfully')]);
        } catch (Exception $e) {
            LogActivity::errorLog('Error al eliminar plan: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function reorder(Request $request)
    {
        try {
            $ids   = array_filter((array) $request->input('ids', []), 'is_numeric');
            $ids   = array_values(array_map('intval', $ids));
            $valid = Plan::whereIn('id', $ids)->pluck('id')->toArray();

            if (count($valid) !== count($ids)) {
                return response()->json(['success' => false, 'error' => 'IDs inválidos'], 422);
            }

            $this->planService->reorder($ids);
            LogActivity::successLog('Orden de planes actualizado.');
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            LogActivity::errorLog('Error al reordenar planes: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function get_list()
    {
        $plans = Plan::where('is_active', true)->orderBy('order')->select('id', 'title as text')->get();
        return response()->json($plans);
    }

    private function renderIconCell($plan): string
    {
        $svg = optional($plan->styles)['icon'] ?? null;
        return $svg ? '<div class="svg-icon-plan svg-sm">' . $svg . '</div>' : '';
    }

    private function renderColorCell($plan): string
    {
        $color = optional($plan->styles)['primaryColor'] ?? null;
        if ($color) {
            return '<div title="' . e($color) . '" style="width:28px;height:28px;border-radius:50%;background:' . e($color) . ';border:1px solid #ddd;display:inline-block;"></div>';
        }
        return '<span style="color:#ccc;">—</span>';
    }

    private function handlePlanException(Exception $e, string $logPrefix)
    {
        if ($e instanceof InvalidSvgException) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        LogActivity::errorLog($logPrefix . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
