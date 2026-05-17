<?php

namespace Modules\CostCenter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CostCenter\Entities\CostCenterInventory;
use Modules\CostCenter\Services\CostCenterInventoryService;
use Modules\Product\Entities\Product;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;
use Modules\CostCenter\Entities\CostCenter;
use Modules\UserActivityLog\Traits\LogActivity;

class CostCenterInventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(CostCenterInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return view('costcenter::inventory.index');
    }

    public function showTransferForm(Request $request)
    {
        $data = $this->inventoryService->getTransferFormData();
        // Recibimos el centro por parámetro si viene desde la vista de detalle
        $data['preselectedCenterId'] = $request->get('center_id');
        return view('costcenter::inventory.transfer', $data);
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $query = $this->inventoryService->getDatatablesQuery();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('product_count', fn($row) => (int) $row->product_count)
                ->addColumn('total_quantity', fn($row) => (int) $row->total_quantity)
                ->addColumn('status_toggle', fn($row) => view('costcenter::components.status_switch', ['row' => $row]))
                ->addColumn('actions', function ($row) {
                    return view('costcenter::inventory.partials.actions', ['row' => $row])->render();
                })
                ->rawColumns(['status_toggle', 'actions'])
                ->make(true);
        }
    }

    public function listMainWarehouseSkus(Request $request)
    {
        try {
            $skus = $this->inventoryService->getMainWarehouseSkus($request->get('search'));
            return response()->json(['success' => true, 'data' => $skus]);
        } catch (\Exception $e) {
            LogActivity::errorLog(__('costcenter::inventory.log_list_main_skus_error', ['error' => $e->getMessage()]));
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCenterSkus($centerId)
    {
        $query = $this->inventoryService->getCenterProductsQuery($centerId);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('image', fn($row) => showImage($row->thumbnail_image_source))
            ->addColumn('product_name', function ($row) {
                $value = json_decode($row->product_name, true);
                return is_array($value) ? ($value[app()->getLocale()] ?? reset($value) ?? '') : ($row->product_name ?? '');
            })
            ->addColumn('brand', function ($row) {
                if (!$row->brand) return '';
                $value = json_decode($row->brand, true);
                return is_array($value) ? ($value[app()->getLocale()] ?? reset($value) ?? '') : $row->brand;
            })
            ->addColumn('product_type', fn($row) => $row->is_physical == 1
                ? __('product.physical_product')
                : __('product.digital_product')
            )
            ->addColumn('unit_type', function ($row) {
                if (!$row->unit_type_json) return '';
                $value = json_decode($row->unit_type_json, true);
                return $value[app()->getLocale()] ?? reset($value) ?? '';
            })
            ->addColumn('actions', fn($row) => view('costcenter::inventory.partials.center_product_actions', [
                'product_id' => $row->product_id,
                'center_id'  => $centerId,
                'min_stock'  => (int) ($row->min_stock ?? 0),
                'max_stock'  => (int) ($row->max_stock ?? 0),
            ])->render())
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function getLocationUsers($locationId)
    {
        try {
            if ($locationId === 'main') {
                // Return all active admins/staff for the main warehouse
                $users = User::where('is_active', 1)
                    ->whereIn('role_id', [1, 2, 3]) // # TODO: Pendiente aclaración de que usuarios pueden  participar en la entrega y recibimiento de traslados
                    ->select('id', 'first_name', 'last_name', 'username')
                    ->get();
            } else {
                // Return users directly assigned to the cost center
                $centerId = str_replace('center-', '', $locationId);
                $users = User::where('is_active', 1)
                    ->where('cost_center_id', $centerId)
                    ->select('id', 'first_name', 'last_name', 'username')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'users' => $users->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => trim($u->first_name . ' ' . $u->last_name),
                        'username' => $u->username
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showProductDetail(Request $request)
    {
        try {
            $product = Product::with([
                'brand',
                'unit_type',
                'categories',
                'gallary_images',
                'gstGroup',
                'skus.product_variations.attribute',
                'skus.product_variations.attribute_value.color',
                'skus.digital_file',
            ])->findOrFail($request->product_id);

            $centerInventories = CostCenterInventory::where('cost_center_id', $request->center_id)
                ->where('qty', '>', 0)
                ->whereIn('product_sku_id', $product->skus->pluck('id'))
                ->get()
                ->keyBy('product_sku_id');

            $centerId = (int) $request->center_id;
            return view('costcenter::inventory.partials.product_detail_modal', compact('product', 'centerInventories', 'centerId'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => __('product.product_not_found')], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStockAlert(Request $request)
    {
        $request->validate([
            'center_id'  => 'required|exists:cost_centers,id',
            'product_id' => 'required|exists:products,id',
            'min_stock'  => 'required|integer|min:0',
            'max_stock'  => 'required|integer|min:0',
        ]);

        try {
            $this->inventoryService->updateProductAlert(
                $request->center_id,
                $request->product_id,
                $request->min_stock,
                $request->max_stock
            );

            $center = CostCenter::find($request->center_id);
            LogActivity::successLog(__('costcenter::inventory.log_stock_alert_updated_success', [
                'center' => $center ? $center->name : $request->center_id,
                'product_id' => $request->product_id,
                'min' => $request->min_stock,
                'max' => $request->max_stock,
            ]));
            return response()->json(['success' => true, 'message' => __('common.updated_successfully')]);
        } catch (\Exception $e) {
            LogActivity::errorLog(__('costcenter::inventory.log_stock_alert_updated_error', ['error' => $e->getMessage()]));
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCenterInventory($centerId)
    {
        try {
            $data = $this->inventoryService->getCenterSkus($centerId);
            return response()->json([
                'success'  => true,
                'products' => $data['products'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getLocationLots(Request $request)
    {
        $request->validate([
            'location' => 'required|string',
            'sku_id' => 'required|exists:product_sku,id',
        ]);

        $location = $request->get('location');
        $skuId = (int) $request->get('sku_id');

        if ($location === 'main') {
            $type = 'main';
            $id = null;
        } elseif (preg_match('/^center-(\d+)$/', $location, $matches)) {
            $type = 'cost_center';
            $id = (int) $matches[1];
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid location'], 422);
        }

        $lots = $this->inventoryService->getLocationLots($type, $id, $skuId);

        return response()->json([
            'success' => true,
            'lots' => $lots->map(function ($row) {
                return [
                    'lot_id' => $row->lot_id,
                    'lot_number' => $row->lot?->lot_number ?? 'N/A',
                    'expiration_date' => $row->lot?->expiration_date?->format('Y-m-d'),
                    'available_qty' => (float) $row->qty,
                ];
            }),
        ]);
    }

    /**
     * Mostrar la vista de productos para un centro de costo específico.
     */
    public function showCenterProducts($centerId)
    {
        $data = $this->inventoryService->getCenterSkus($centerId);
        // Pasamos el centro y las inventories al blade; la vista cargará los datos vía AJAX también.
        return view('costcenter::inventory.center_products', [
            'center' => $data['center'],
            'centerId' => $centerId
        ]);
    }

}
