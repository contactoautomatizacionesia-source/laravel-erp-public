<?php

namespace Modules\CostCenter\Http\Controllers;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CostCenter\Exceptions\CannotDeleteWithInventoryException;
use Modules\CostCenter\Exceptions\DefaultCostCenterException;
use Modules\CostCenter\Http\Requests\CostCenterRequest;
use Modules\CostCenter\Repositories\CostCenterRepository;
use Modules\CostCenter\Services\CostCenterService;
use Modules\GeneralSetting\Entities\Catalogs\PaymentForm;
use Modules\Product\Entities\Brand;
use Modules\Setup\Entities\City;
use Modules\UserActivityLog\Traits\LogActivity;
use Yajra\DataTables\Facades\DataTables;

class CostCenterController extends Controller
{
    protected $service;
    protected $repository;

    public function __construct(CostCenterService $service, CostCenterRepository $repository)
    {
        $this->service = $service;
        $this->repository = $repository;
    }

    public function index()
    {
        return view('costcenter::index');
    }

    public function get_data(Request $request) //NOSONAR
    {
        if ($request->ajax()) {
            $table = $request->get('table', 'active');

            if ($table === 'deleted') {
                $query = $this->repository->getDeletedQuery();
            } else {
                $query = $this->repository->getActiveQuery();
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('pin_code', function ($row) {
                    return filled($row->pin_code) ? $row->pin_code : '—';
                })
                ->addColumn('city_name', function ($row) {
                    return $row->city ? $row->city->name : 'N/A';
                })
                ->addColumn('brand_name', function ($row) {
                    return $row->brand ? $row->brand->name : 'N/A';
                })
                ->addColumn('payment_methods_list', function ($row) {
                    return view('costcenter::components.payment_form_badge', compact('row'))->render();
                })
                ->addColumn('default_badge', function ($row) use ($table) {
                    return view('costcenter::components.default_switch', compact('row', 'table'))->render();
                })
                ->addColumn('status_badge', function ($row) use ($table) {
                    if ($table === 'deleted') {
                        return view('costcenter::components.status_badge', compact('row'))->render();
                    }

                    return view('costcenter::components.status_switch', ['row' => $row])->render();
                })
                ->addColumn('created_formatted', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i') : 'N/A';
                })
                ->addColumn('updated_formatted', function ($row) {
                    return $row->updated_at ? $row->updated_at->format('Y-m-d H:i') : 'N/A';
                })
                ->addColumn('action', function ($row) use ($table) {
                    return view('costcenter::components.actions', compact('row', 'table'))->render();
                })
                ->rawColumns(['payment_methods_list', 'default_badge', 'status_badge', 'action'])
                ->make(true);
        }
    }

    public function store(CostCenterRequest $request)
    {
        try {
            $costCenter = $this->service->store($request->all());
            LogActivity::successLog(__('cost_center.log_created_success', ['name' => $costCenter->name, 'code' => $costCenter->code]));

            return response()->json(['success' => true, 'message' => __('cost_center.created_successfully')]);
        } catch (Exception $e) {
            $error = $this->parseError($e);
            LogActivity::errorLog(__('cost_center.log_created_error', ['error' => $error]));

            return response()->json([
                'success' => false,
                'error' => $error,
            ], $this->resolveStatusCode($e));
        }
    }

    private function parseError(Exception $e)
    {
        if ($e instanceof CannotDeleteWithInventoryException || $e instanceof DefaultCostCenterException) {
            return $e->getMessage();
        }

        if ($e instanceof QueryException && ($e->errorInfo[1] ?? null) == 1062) {
            return __('cost_center.code_already_exists');
        }

        return __('cost_center.error_occurred');
    }

    private function resolveStatusCode(Exception $e): int
    {
        return $e instanceof CannotDeleteWithInventoryException || $e instanceof DefaultCostCenterException
            ? 422
            : 500;
    }

    public function edit($id)
    {
        $costCenter = $this->repository->findById($id, ['paymentForm:id', 'city:id,name', 'brand:id,name', 'users:id,cost_center_id']);
        $locale = app()->getLocale();

        if ($costCenter->city) {
            $costCenter->city_text = $costCenter->city->getTranslation('name', $locale);

            if ($costCenter->city->state) {
                $costCenter->city_text .= ' - ' . $costCenter->city->state->name;

                if ($costCenter->city->state->country) {
                    $costCenter->city_text .= ' - ' . $costCenter->city->state->country->name;
                }
            }
        }

        if ($costCenter->brand) {
            $costCenter->brand_text = $costCenter->brand->getTranslation('name', $locale);
        }

        return response()->json($costCenter);
    }

    public function update(CostCenterRequest $request, $id)
    {
        try {
            $costCenter = $this->service->update($id, $request->all());
            LogActivity::successLog(__('cost_center.log_updated_success', ['name' => $costCenter->name, 'code' => $costCenter->code]));

            return response()->json(['success' => true, 'message' => __('cost_center.updated_successfully')]);
        } catch (Exception $e) {
            $error = $this->parseError($e);
            LogActivity::errorLog(__('cost_center.log_updated_error', ['error' => $error]));

            return response()->json([
                'success' => false,
                'error' => $error,
            ], $this->resolveStatusCode($e));
        }
    }

    public function destroy($id)
    {
        try {
            $costCenter = $this->repository->findById($id);
            $this->service->delete($id);
            LogActivity::successLog(__('cost_center.log_deleted_success', ['name' => $costCenter->name, 'id' => $id]));

            return response()->json(['success' => true, 'message' => __('cost_center.deleted_successfully')]);
        } catch (Exception $e) {
            LogActivity::errorLog(__('cost_center.log_deleted_error', ['error' => $e->getMessage()]));

            return response()->json([
                'success' => false,
                'error' => $this->parseError($e),
            ], $this->resolveStatusCode($e));
        }
    }

    public function restore($id)
    {
        try {
            $costCenter = $this->service->restore($id);
            LogActivity::successLog(__('cost_center.log_restored_success', ['name' => $costCenter->name, 'id' => $id]));

            return response()->json(['success' => true, 'message' => __('cost_center.restored_successfully')]);
        } catch (Exception $e) {
            $error = $this->parseError($e);
            LogActivity::errorLog(__('cost_center.log_restored_error', ['error' => $e->getMessage()]));

            return response()->json(['success' => false, 'error' => $error], $this->resolveStatusCode($e));
        }
    }

    public function get_cities(Request $request)
    {
        $search = $request->term ?? '';
        $limit = 50;
        $locale = app()->getLocale();

        $cities = City::with(['state.country'])
            ->where('status', 1)
            ->when($search, function ($query) use ($search, $locale) {
                $query->where('name->' . $locale, 'like', '%' . $search . '%');
            })
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->get();

        return $cities->map(function ($city) use ($locale) {
            $cityName = $city->getTranslation('name', $locale, false);

            if (!$cityName) {
                $translations = $city->getTranslations('name');
                $cityName = reset($translations);
            }

            if (!$cityName) {
                $cityName = $city->name;
            }

            $stateName = $city->state ? $city->state->name : '';
            $countryName = $city->state && $city->state->country ? $city->state->country->name : '';

            return [
                'id' => $city->id,
                'name' => implode(' - ', array_filter([$cityName, $stateName, $countryName])),
            ];
        });
    }

    public function get_brands(Request $request)
    {
        $term = $request->term ?? '';
        $locale = app()->getLocale();

        $brands = Brand::where('status', 1)
            ->when($term, function ($query) use ($term, $locale) {
                $query->where('name->' . $locale, 'like', '%' . $term . '%');
            })
            ->limit(50)
            ->get();

        return response()->json($brands->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
            ];
        }));
    }

    public function get_payment_methods()
    {
        return response()->json(
            PaymentForm::active()->get()->map(function ($item) {
                return ['id' => $item->id, 'name' => $item->name];
            })
        );
    }

    public function get_users(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\User::where('is_active', 1)->whereHas('role', function ($q) {
                $q->where('type', '!=', 'customer');
            });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('full_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->addColumn('role_name', function ($row) {
                    return $row->role ? $row->role->name : 'N/A';
                })
                ->addColumn('select', function ($row) {
                    return '<div class="primary_checkbox d-flex justify-content-center">
                                <input type="checkbox" class="user_checkbox" value="' . $row->id . '">
                                <span class="checkmark"></span>
                            </div>';
                })
                ->rawColumns(['select'])
                ->make(true);
        }
    }

    public function update_status(Request $request)
    {
        try {
            $this->service->update($request->id, ['status' => $request->status]);

            return response()->json(['success' => true, 'message' => __('common.updated_successfully')]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e instanceof CannotDeleteWithInventoryException || $e instanceof DefaultCostCenterException
                    ? $e->getMessage()
                    : __('cost_center.error_occurred'),
            ], $this->resolveStatusCode($e));
        }
    }

    public function update_default(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:cost_centers,id',
            'is_default' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => __('cost_center.error_occurred')], 422);
        }

        try {
            $this->service->setDefault((int) $request->id, (bool) $request->is_default);

            return response()->json([
                'success' => true,
                'message' => __('cost_center.default_updated_successfully'),
            ]);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e instanceof DefaultCostCenterException
                    ? $e->getMessage()
                    : __('cost_center.error_occurred'),
            ], $this->resolveStatusCode($e));
        }
    }
}
