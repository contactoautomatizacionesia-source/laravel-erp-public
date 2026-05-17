<?php

namespace Modules\Plans\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Plans\Services\BenefitService;
use Modules\Plans\Repositories\BenefitRepository;
use Modules\Plans\Entities\BenefitCategory;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Modules\UserActivityLog\Traits\LogActivity;
use Illuminate\Support\Facades\DB;
use Exception;

class BenefitController extends Controller
{
    protected $benefitService;
    protected $benefitRepository;

    public function __construct(BenefitService $benefitService, BenefitRepository $benefitRepository)
    {
        $this->benefitService    = $benefitService;
        $this->benefitRepository = $benefitRepository;
    }

    public function index()
    {
        $categories = BenefitCategory::with('type')->get();
        return view('plans::benefits.index', compact('categories'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $benefits = $this->benefitRepository->getBaseQuery();
            return DataTables::of($benefits)
                ->addIndexColumn()
                ->addColumn('code', function ($benefit) {
                    return $benefit->code ?? '—';
                })
                ->addColumn(
                    'title', function ($benefit) {
                        return $benefit->title;
                    }
                )
                ->addColumn('category_badge', function ($benefit) {
                    $cat  = $benefit->category ? $benefit->category->name : __('common.no_category');
                    $type = ($benefit->category && $benefit->category->type) ? $benefit->category->type->label : '';
                    return '<span class="">' . $type . '</span> / '
                    . ' <span class="">' . $cat . '</span>';
                })
                ->addColumn('status_badge', function ($benefit) {
                    return $benefit->is_active
                        ? '<span class="badge_1">'.__('common.active').'</span>'
                        : '<span class="badge_2">'.__('common.inactive').'</span>';
                })
                ->addColumn('cumulative_badge', function ($benefit) {
                    return $benefit->is_cumulative
                        ? '<span class="badge_1">'.__('common.yes').'</span>'
                        : '<span class="badge_2">'.__('common.no').'</span>';
                })
                ->addColumn('plans_badge', function ($benefit) {
                    return '<span class="badge_5">' . $benefit->plan_children_count . ' '.__('common.subplans').'</span>';
                })
                ->addColumn('updated_at_formatted', function ($benefit) {
                    return $benefit->updated_at ? $benefit->updated_at->format('d/m/Y') : '—';
                })
                ->addColumn('action', function ($benefit) {
                    $sel = __('common.select');
                    $id  = $benefit->id;
                    return '<div class="dropdown CRM_dropdown">'
                        . '<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">' . $sel . '</button>'
                        . '<div class="dropdown-menu dropdown-menu-right">'
                        . '<a class="dropdown-item show_benefit" href="#" data-value="' . $id . '">' . __('common.details') . '</a>'
                        . '<a class="dropdown-item edit_benefit" href="#" data-value="' . $id . '">' . __('common.edit') . '</a>'
                        . '<a class="dropdown-item delete_benefit" href="#" data-value="' . $id . '">' . __('common.delete') . '</a>'
                        . '</div></div>';
                })
                ->rawColumns(['category_badge', 'status_badge', 'cumulative_badge', 'plans_badge', 'action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'                => ['required', 'max:20', 'regex:/^B[0-9]+$/', 'unique:benefit,code'],
            'title'               => 'required|max:100',
            'benefit_category_id' => 'required|exists:benefit_category,id',
        ], [
            'code.regex'          => 'El código debe tener el formato B[número] (ej. B1, B2).'
        ], [
            'code'                => __('common.code'),
            'title'               => __('common.title'),
            'benefit_category_id' => __('common.benefit_category'),
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }
        try {
            $this->benefitService->storeBenefit($request->all());
            LogActivity::successLog('Beneficio creado: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.benefit_created_correctly')]);
        } catch (Exception $e) {
            LogActivity::errorLog('Error al crear beneficio: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function next_code()
    {
        $prefix = 'B';
        $codes = \Modules\Plans\Entities\Benefit::where('code', 'LIKE', $prefix . '%')
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

    public function edit($id)
    {
        $benefit = $this->benefitRepository->findById($id, ['formAnswers', 'category']);
        return response()->json($benefit);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code'                => ['required', 'max:20', 'regex:/^B[0-9]+$/', 'unique:benefit,code,' . $id],
            'title'               => 'required|max:100',
            'benefit_category_id' => 'required|exists:benefit_category,id',
        ], [
            'code.regex'          => 'El código debe tener el formato B[número] (ej. B1, B2).'
        ], [
            'code'                => __('common.code'),
            'title'               => __('common.title'),
            'benefit_category_id' => __('common.benefit_category'),
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()], 422);
        }
        try {
            $this->benefitService->updateBenefit($id, $request->all());
            LogActivity::successLog('Beneficio actualizado: ' . $request->title);
            return response()->json(['success' => true, 'message' => __('common.benefit_updated_succesfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->benefitService->deleteBenefit($id);
            LogActivity::successLog('Beneficio eliminado - ID: ' . $id);
            return response()->json(['success' => true, 'message' => __('common.benefit_deleted_succesfully')]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 403);
        }
    }

    public function show($id)
    {
        $benefit = $this->benefitRepository->findById($id, ['category.type', 'formAnswers.field.section']);
        $benefit->loadCount('planChildren');

        $benefit->formAnswers->each(function ($answer) {
            if ($answer->field) {
                $answer->field->validation_rules = $this->resolveFieldOptions(
                    $answer->field->validation_rules,
                    'form_options'
                );
            }
        });

        return response()->json($benefit);
    }

    /**
     * Returns form structure (sections + fields) for a given benefit_category_id.
     * Used by the frontend to dynamically build the benefit form.
     */
    public function get_form_structure($categoryId)
    {
        $category = $this->benefitRepository->getCategoryWithSections($categoryId);

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
            'has_form' => $sections->count() > 0,
            'sections' => $sections,
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

    /** Returns list of benefits for select boxes (plan assignments) */
    public function get_list()
    {
        $benefits = \Modules\Plans\Entities\Benefit::with('category')->where('is_active', true)->orderBy('title')->get();
        return response()->json($benefits->map(function ($b) {
            return [
                'id'       => $b->id,
                'text'     => $b->title,
                'category' => $b->category ? $b->category->name : '',
            ];
        }));
    }
}
