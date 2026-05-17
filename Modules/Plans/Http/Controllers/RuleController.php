<?php

namespace Modules\Plans\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Plans\Services\RuleService;
use Modules\Plans\Repositories\RuleRepository;
use Modules\Plans\Entities\RuleCategory;
use Modules\Plans\Entities\Rule;
use Modules\Plans\Entities\PlanChild;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\UserActivityLog\Traits\LogActivity;
use Illuminate\Support\Facades\DB;
use Exception;

class RuleController extends Controller
{
    protected $ruleService;
    protected $ruleRepository;

    public function __construct(RuleService $ruleService, RuleRepository $ruleRepository)
    {
        $this->ruleService    = $ruleService;
        $this->ruleRepository = $ruleRepository;
    }

    public function index()
    {
        $categories = RuleCategory::with('type')->get();
        return view('plans::rules.index', compact('categories'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $rules = $this->ruleRepository->getBaseQuery();
            return DataTables::of($rules)
                ->addIndexColumn()
                ->addColumn('title', function($rule) {
                    return $rule->title;
                })
                ->addColumn('category_badge', function ($rule) {
                    $cat  = $rule->category ? $rule->category->name : __('common.no_category');
                    $type = ($rule->category && $rule->category->type) ? $rule->category->type->label : '';
                    return '<span class="">' . $type . '</span> / '
                        . ' <span class="">' . $cat . '</span>';
                })
                ->addColumn('status_badge', function ($rule) {
                    return $rule->is_active
                        ? '<span class="badge_1">'.__('common.active').'</span>'
                        : '<span class="badge_4">'.__('common.inactive').'</span>';
                })
                ->addColumn('plans_badge', function ($rule) {
                    return '<span class="badge_5">' . $rule->plan_children_count . ' ' . __('common.subplans') . '</span>';
                })
                ->addColumn('updated_at_formatted', function ($rule) {
                    return $rule->updated_at ? $rule->updated_at->format('d/m/Y') : '—';
                })
                ->addColumn('action', function ($rule) {
                    $sel = __('common.select');
                    $id  = $rule->id;
                    return '<div class="dropdown CRM_dropdown">'
                        . '<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">' . $sel . '</button>'
                        . '<div class="dropdown-menu dropdown-menu-right">'
                        . '<a class="dropdown-item show_rule" href="#" data-value="' . $id . '">' . __('common.details') . '</a>'
                        . '<a class="dropdown-item edit_rule" href="#" data-value="' . $id . '">' . __('common.edit') . '</a>'
                        . '<a class="dropdown-item delete_rule" href="#" data-value="' . $id . '">' . __('common.delete') . '</a>'
                        . '</div></div>';
                })
                ->rawColumns(['category_badge', 'status_badge', 'plans_badge', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $error = $this->validateRuleRequest($request, null);

        if (!$error) {
            $category = RuleCategory::find($request->rule_category_id);
            if ($category && $category->key === 'RULE_GROUPING') {
                $deps = collect($request->input('dependencies', []))->filter(fn($d) => !empty($d['child_rule_id']));
                if ($deps->count() < 2) {
                    $error = response()->json(['success' => false, 'error' => __('common.maintenance_min_two_rules')], 422);
                }
            }
        }

        if ($error) {
            return $error;
        }

        try {
            $this->ruleService->storeRule($request->all());
            LogActivity::successLog('Regla creada: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.rule_created_correctly')]);
        } catch (Exception $e) {
            LogActivity::errorLog('Error al crear regla: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function next_code()
    {
        $prefix = 'R';
        $codes = Rule::where('code', 'LIKE', $prefix . '%')
                    ->pluck('code')
                    ->toArray();

        $numbers = [];
        foreach ($codes as $code) {
            $numStr = substr($code, strlen($prefix));
            if (is_numeric($numStr)) {
                $numbers[] = (int) $numStr;
            }
        }

        $numbers = array_unique($numbers);
        sort($numbers);

        $nextNumber = 1;
        foreach ($numbers as $num) {
            if ($num == $nextNumber) {
                $nextNumber++;
            } elseif ($num > $nextNumber) {
                break;
            }
        }

        return response()->json(['code' => $prefix . $nextNumber]);
    }

    private function validateRuleRequest(Request $request, ?int $ignoreId): ?\Illuminate\Http\JsonResponse
    {
        $errorResponse = null;

        $validator = Validator::make($request->all(), [
            'code'             => ['required', 'max:20', 'regex:/^R[0-9]+$/', 'unique:rule,code,' . ($ignoreId ?? 'NULL')],
            'title'            => 'required|max:100',
            'rule_category_id' => 'required|exists:rule_category,id',
        ], [
            'code.regex'       => 'El código debe tener el formato R[número] (ej. R1, R2).'
        ], [
            'code'             => __('common.code'),
            'title'            => __('common.title'),
            'rule_category_id' => __('common.rule_category'),
        ]);

        if ($validator->fails()) {
            $errorResponse = response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }

        if (!$errorResponse) {
            $category = RuleCategory::with(['formSections.fields'])->find($request->rule_category_id);

            if ($category && $category->key === 'RULE_GROUPING') {
                $deps = collect($request->input('dependencies', []))->filter(fn($d) => !empty($d['child_rule_id']));
                if ($deps->count() < 2) {
                    $errorResponse = response()->json(['success' => false, 'error' => __('common.maintenance_min_two_rules')], 422);
                }
            }

            if (!$errorResponse && $category && in_array($category->key, ['POINTS_THRESHOLD', 'POINTS_RANGE'], true)) {
                $sourceFieldIds = $category->formSections
                    ->flatMap(fn($section) => $section->fields)
                    ->whereIn('field_key', ['INCLUDE_PERSONAL', 'INCLUDE_CHILDREN'])
                    ->pluck('id', 'field_key');

                $answers = (array) $request->input('answers', []);
                $includePersonal = filter_var($answers[$sourceFieldIds['INCLUDE_PERSONAL'] ?? 0] ?? false, FILTER_VALIDATE_BOOLEAN);
                $includeChildren = filter_var($answers[$sourceFieldIds['INCLUDE_CHILDREN'] ?? 0] ?? false, FILTER_VALIDATE_BOOLEAN);

                if (!$includePersonal && !$includeChildren) {
                    $errorResponse = response()->json([
                        'success' => false,
                        'error' => 'Debes seleccionar al menos una fuente de puntos.',
                    ], 422);
                }
            }
        }

        return $errorResponse;
    }

    public function edit($id)
    {
        $rule = $this->ruleRepository->findById($id, ['formAnswers', 'dependencies', 'category']);
        return response()->json($rule);
    }

    public function update(Request $request, $id)
    {
        $error = $this->validateRuleRequest($request, (int) $id);

        if (!$error) {
            $category = RuleCategory::find($request->rule_category_id);
            if ($category && $category->key === 'RULE_GROUPING') {
                $deps = collect($request->input('dependencies', []))->filter(fn($d) => !empty($d['child_rule_id']));
                if ($deps->count() < 2) {
                    $error = response()->json(['success' => false, 'error' => __('common.maintenance_min_two_rules')], 422);
                }
            }
        }

        if ($error) {
            return $error;
        }

        try {
            $this->ruleService->updateRule($id, $request->all());
            LogActivity::successLog('Regla actualizada: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.rule_updated_succesfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->ruleService->deleteRule($id);
            LogActivity::successLog('Regla eliminada - ID: ' . $id);
            return response()->json(['success' => true, 'message' => __('common.rule_deleted_succesfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 403);
        }
    }

    public function show($id)
    {
        $rule = $this->ruleRepository->findById($id, ['category.type', 'formAnswers.field.section', 'dependencies.childRule.category']);
        $rule->loadCount('planChildren');

        $rule->formAnswers->each(function ($answer) {
            if ($answer->field) {
                $answer->field->validation_rules = $this->resolveFieldOptions(
                    $answer->field->validation_rules,
                    'form_options'
                );
            }
        });

        return response()->json($rule);
    }

    /**
     * Returns form structure (sections + fields) for a given rule_category_id.
     * Used by the frontend to dynamically build the rule form.
     */
    public function get_form_structure($categoryId)
    {
        $category      = $this->ruleRepository->getCategoryWithSections($categoryId);
        $isMaintenance = $category->key === 'RULE_GROUPING';

        $sections = $category->formSections->map(function ($section) {
            return [
                'id'            => $section->id,
                'section_label' => $section->section_label,
                'section_key'   => $section->section_key,
                'section_order' => $section->section_order,
                'is_repeatable' => (bool) $section->is_repeatable,
                'fields'        => $section->fields->map(function ($field) {
                    return [
                        'id'               => $field->id,
                        'field_label'      => $field->field_label,
                        'field_key'        => $field->field_key,
                        'field_type'       => $field->field_type,
                        'is_required'      => (bool) $field->is_required,
                        'help_text'        => $field->help_text,
                        'validation_rules' => $this->resolveFieldOptions($field->validation_rules, 'form_options'),
                    ];
                }),
            ];
        });

        return response()->json([
            'is_maintenance' => $isMaintenance,
            'sections'       => $sections,
        ]);
    }

    /**
     * If validation_rules.options is an array of integer IDs, resolve them to
     * full option objects from the given options table.
     * Leaves METHOD strings and inline legacy arrays untouched.
     */
    private function resolveFieldOptions(?array $validationRules, string $optionsTable): ?array
    {
        if (empty($validationRules) || !isset($validationRules['options'])) {
            return $validationRules;
        }

        $options = $validationRules['options'];

        if (!is_array($options) || empty($options) || !is_int($options[0])) {
            return $validationRules;
        }

        $rows = DB::table($optionsTable)->whereIn('id', $options)->get()->keyBy('id');

        $validationRules['options'] = collect($options)
            ->map(function ($id) use ($rows) {
                if (!$rows->has($id)) {
                    return null;
                }
                $row = $rows[$id];
                return [
                    'id'           => $row->id,
                    'option_label' => json_decode($row->option_label, true),
                    'option_key'   => $row->option_key,
                    'help_text'    => $row->help_text ? json_decode($row->help_text, true) : null,
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        return $validationRules;
    }

    /** Returns list of rules for select boxes (rule_dependencies, plan assignments) */
    public function get_list()
    {
        $rules = Rule::with('category')->where('is_active', true)->orderBy('code')->get();
        return response()->json($rules->map(function ($r) {
            return [
                'id'       => $r->id,
                'text'     => '[' . $r->code . '] ' . $r->title,
                'code'     => $r->code,
                'category' => $r->category ? $r->category->name : '',
            ];
        }));
    }

    /** Returns list of plan_children for fields that use METHOD[fetchPlanChildren] */
    public function get_plan_children_list()
    {
        $children = PlanChild::with('plan')
            ->join('plan', 'plan.id', '=', 'plan_child.plan_id')
            ->where('plan_child.is_active', true)
            ->orderBy('plan.order')
            ->orderBy('plan_child.level_order')
            ->select('plan_child.*')
            ->get();
        return response()->json($children->map(function ($c) {
            $planTitle = $c->plan ? $c->plan->title . ' > ' : '';
            return ['id' => $c->id, 'text' => $planTitle . $c->title];
        }));
    }
}
