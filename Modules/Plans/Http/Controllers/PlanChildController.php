<?php

namespace Modules\Plans\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Plans\Services\PlanChildService;
use Modules\Plans\Repositories\PlanChildRepository;
use Modules\Plans\Repositories\PlanRepository;
use Modules\Plans\Entities\PlanChild;
use Modules\Plans\Entities\Rule;
use Modules\Plans\Entities\Benefit;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\UserActivityLog\Traits\LogActivity;
use Exception;

class PlanChildController extends Controller
{
    protected $planChildService;
    protected $planChildRepository;
    protected $planRepository;

    public function __construct(
        PlanChildService    $planChildService,
        PlanChildRepository $planChildRepository,
        PlanRepository      $planRepository
    ) {
        $this->planChildService    = $planChildService;
        $this->planChildRepository = $planChildRepository;
        $this->planRepository      = $planRepository;
    }

    public function index($planId)
    {
        $plan = $this->planRepository->findById($planId);
        return view('plans::children.index', compact('plan'));
    }

    public function get_data(Request $request, $planId)
    {
        if ($request->ajax()) {
            $children = $this->planChildRepository->getByPlan($planId);

            return DataTables::of($children)
                ->addIndexColumn()
                ->addColumn('level_order_drag', function () {
                    return '<span class="drag-handle" title="Arrastrar para reordenar"><i class="ti-menu"></i></span>';
                })
                ->addColumn('order_number', function ($c) {
                    return  $c->level_order;
                })
                ->addColumn('title', function ($c) {
                    return $c->title;
                })
                ->addColumn('description', function ($c) {
                    return $c->description;
                })
                ->addColumn('rules_badge', function ($c) {
                    return '<span class="badge_1">' . $c->rules_count . '</span>';
                })
                ->addColumn('benefits_badge', function ($c) {
                    return '<span class="badge_1">' . $c->benefits_count . '</span>';
                })
                ->addColumn('status_badge', function ($c) {
                    return $c->is_active
                        ? '<span class="badge_1">'.__('common.active').'</span>'
                        : '<span class="badge_4">'.__('common.inactive').'</span>';
                })
                ->addColumn('action', function ($c) {
                    $sel = __('common.select');
                    return '<div class="dropdown CRM_dropdown"><button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">' .
                    $sel . '</button><div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item btn-assign" href="#" data-value="' .
                    $c->id . '">'.__('common.assign_rules_benefits').'</a><a class="dropdown-item btn-edit-child" href="#" data-value="' .
                    $c->id . '">'.__('common.edit').'</a><a class="dropdown-item btn-delete-child" href="#" data-value="' .
                    $c->id . '">'.__('common.delete').'</a></div></div>';
                })
                ->rawColumns(['level_order_drag', 'rules_badge', 'benefits_badge', 'status_badge', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request, $planId)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|max:100',
            'level_order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $data = array_merge($request->all(), ['plan_id' => $planId]);
            $this->planChildService->store($data);
            LogActivity::successLog('Subplan creado: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.subplan_created_succesfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($planId, $id)
    {
        return response()->json($this->planChildRepository->findById($id));
    }

    public function update(Request $request, $planId, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|max:100',
            'level_order' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        try {
            $data = array_merge($request->all(), ['plan_id' => $planId]);
            $this->planChildService->update($id, $data);
            LogActivity::successLog('Subplan actualizado: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.subplan_updated_succesfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($planId, $id)
    {
        try {
            $this->planChildService->delete($id);
            LogActivity::successLog('Subplan eliminado - ID: ' . $id);
            return response()->json(['success' => true, 'message' => __('common.subplan_deleted_succesfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function reorder(Request $request, $planId)
    {
        try {
            $ids   = array_filter((array) $request->input('ids', []), 'is_numeric');
            $ids   = array_values(array_map('intval', $ids));
            $valid = PlanChild::where('plan_id', $planId)->whereIn('id', $ids)->pluck('id')->toArray();

            if (count($valid) !== count($ids)) {
                return response()->json(['success' => false, 'error' => 'IDs inválidos'], 422);
            }

            $this->planChildService->reorder($ids);
            LogActivity::successLog('Orden de subplanes actualizado para plan ' . $planId);
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            LogActivity::errorLog('Error al reordenar subplanes: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function get_assignments($planId, $id)
    {
        $child = $this->planChildRepository->findById($id, ['rules.category', 'benefits.category']);
        return response()->json([
            'child'    => $child,
            'rules'    => $child->rules->map(function ($r) {
                return ['id' => $r->id, 'code' => $r->code, 'title' => $r->title, 'is_required' => (bool) $r->pivot->is_required, 'category' => $r->category ? $r->category->name : ''];
            }),
            'benefits' => $child->benefits->map(function ($b) {
                return ['id' => $b->id, 'code' => $b->code, 'title' => $b->title, 'category' => $b->category ? $b->category->name : ''];
            }),
        ]);
    }

    public function assign_rules(Request $request, $planId, $id)
    {
        try {
            $this->planChildService->assignRules($id, $request->input('rules', []));
            return response()->json(['success' => true, 'message' => __('common.rules_assigned_correctly')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function assign_benefits(Request $request, $planId, $id)
    {
        try {
            $this->planChildService->assignBenefits($id, $request->input('benefit_ids', []));
            return response()->json(['success' => true, 'message' => __('common.benefits_assigned_correctly')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function get_available_rules()
    {
        $rules = Rule::with('category')->where('is_active', true)->orderBy('code')->get();
        return response()->json($rules->map(function ($r) {
            $titleDecoded = is_string($r->title) ? json_decode($r->title, true) : $r->title;
            $titleAll = is_array($titleDecoded) ? implode(' ', array_values($titleDecoded)) : ($r->title ?? '');
            $catName = $r->category ? $r->category->name : '';
            $catDecoded = is_string($catName) ? json_decode($catName, true) : $catName;
            $catAll = is_array($catDecoded) ? implode(' ', array_values($catDecoded)) : ($catName ?? '');
            return [
                'id'       => $r->id,
                'code'     => $r->code,
                'title'    => $titleDecoded ?? $r->title,
                'category' => $catDecoded ?? $catName,
                'search'   => strtolower(($r->code ?? '') . ' ' . $titleAll . ' ' . $catAll),
            ];
        }));
    }

    public function get_available_benefits()
    {
        $benefits = Benefit::with('category')->where('is_active', true)->orderBy('title')->get();
        return response()->json($benefits->map(function ($b) {
            $titleDecoded = is_string($b->title) ? json_decode($b->title, true) : $b->title;
            $titleAll = is_array($titleDecoded) ? implode(' ', array_values($titleDecoded)) : ($b->title ?? '');
            $catName = $b->category ? $b->category->name : '';
            $catDecoded = is_string($catName) ? json_decode($catName, true) : $catName;
            $catAll = is_array($catDecoded) ? implode(' ', array_values($catDecoded)) : ($catName ?? '');
            return [
                'id'       => $b->id,
                'code'     => $b->code,
                'title'    => $titleDecoded ?? $b->title,
                'category' => $catDecoded ?? $catName,
                'search'   => strtolower(($b->code ?? '') . ' ' . $titleAll . ' ' . $catAll),
            ];
        }));
    }
}
