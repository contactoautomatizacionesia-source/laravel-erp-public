<?php

namespace Modules\CostCenter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CostCenter\Entities\CostCenter;
use Modules\CostCenter\Entities\CostCenterTransfer;
use Modules\CostCenter\Http\Requests\TransferInventoryRequest;
use Modules\CostCenter\Services\CostCenterInventoryService;
use Modules\UserActivityLog\Traits\LogActivity;
use Yajra\DataTables\Facades\DataTables;
use Modules\CostCenter\Http\Requests\ReceiveTransferRequest;
use Illuminate\Support\Facades\Storage;
use Modules\GeneralSetting\Entities\Catalogs\Novelty;

class CostCenterInventoryTransactionsController extends Controller
{
    protected $inventoryService;

    public function __construct(CostCenterInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function transferToCenter(TransferInventoryRequest $request)
    {
        $validated = $request->validated();
        $origin = $validated['origin_id'];
        $dest = $validated['destination_id'];

        // 1. Guard Clause: Evaluamos primero el caso invalido y salimos temprano
        if ($origin === 'main' && $dest === 'main') {
            LogActivity::warningLog(__('costcenter::inventory.log_transfer_invalid'));
            return response()->json(['success' => false, 'message' => __('costcenter::messages.invalid_transfer')], 422);
        }

        $userId = auth()->id();
        $dispatched = $validated['dispatched_by'] ?? null;
        $received   = $validated['received_by'] ?? null;
        $guide      = $validated['shipping_guide'] ?? null;
        $carrierId  = $validated['carrier_id'] ?? null;
        $guideDate  = $validated['guide_date'] ?? null;

        $transferMeta = [
            'movement_type_id' => $validated['movement_type_id'],
            'reason'           => $validated['reason'],
            'created_by'       => $userId,
            'dispatched_by'    => $dispatched,
            'received_by'      => $received,
            'shipping_guide'   => $guide,
            'carrier_id'       => $carrierId,
            'guide_date'       => $guideDate,
        ];

        if ($origin === 'main') {
            $result = $this->inventoryService->transferFromMainToCenter($dest, $validated['items'], $transferMeta);
        } elseif ($dest === 'main') {
            $result = $this->inventoryService->returnFromCenterToMain($origin, $validated['items'], $transferMeta);
        } else {
            $result = $this->inventoryService->transferBetweenCenters($origin, $dest, $validated['items'], $transferMeta);
        }

        // 3. Resolvemos los nombres usando el helper
        $fromName = $this->getCenterName($origin);
        $toName = $this->getCenterName($dest);

        // 4. Registro de Logs
        if (!empty($result['success'])) {
            LogActivity::successLog(__('costcenter::inventory.log_transfer_success', [
                'from' => $fromName,
                'to' => $toName,
                'products' => count($validated['items']),
                'qty' => collect($validated['items'])->sum('qty'),
                'transfer_id' => $result['transfer_id'] ?? null,
            ]));
        } else {
            LogActivity::errorLog(__('costcenter::inventory.log_transfer_error', [
                'from' => $fromName,
                'to' => $toName,
                'error' => $result['message'] ?? __('costcenter::inventory.unknown_error'),
            ]));
        }

        return response()->json($result, empty($result['success']) ? 422 : 200);
    }

    /**
     * Helper para obtener el nombre del Centro de Costo o Bodega Principal.
     * Al mover esto aqui, evitamos multiples bloques 'if' en el metodo principal.
     */
    private function getCenterName($id)
    {
        if ($id === 'main') {
            return __('costcenter::main_warehouse.name');
        }

        return CostCenter::find($id)->name ?? $id;
    }

    public function transactions($centerId)
    {
        $center = CostCenter::findOrFail($centerId);
        return view('costcenter::inventory.transactions', compact('center'));
    }

    public function allTransactions()
    {
        $center = (object)[
            'id' => null,
            'name' => __('costcenter::inventory.all_transfers')
        ];
        return view('costcenter::inventory.transactions', compact('center'));
    }

    public function allTransactionsData(Request $request)
    {
        return $this->getTransactionsDatatable(null, $request);
    }

    public function transactionsData($centerId, Request $request)
    {
        return $this->getTransactionsDatatable($centerId, $request);
    }

    private function getTransactionsDatatable($centerId, Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Bad Request'], 400);
        }

        $query = CostCenterTransfer::with(['movementType', 'dispatchedBy', 'receivedBy', 'sourceCostCenter', 'destinationCostCenter']);

        if ($centerId) {
            $query->where(function ($q) use ($centerId) {
                $q->where(function($q2) use ($centerId) {
                    $q2->where('source_type', 'cost_center')
                       ->where('source_id', $centerId);
                })->orWhere(function($q2) use ($centerId) {
                    $q2->where('destination_type', 'cost_center')
                       ->where('destination_id', $centerId);
                });
            });
        }

        $query->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', fn($row) => $row->created_at->format('Y-m-d H:i'))
            ->addColumn('type', fn($row) => $row->movementType->name ?? 'N/A')
            ->addColumn('reference_code', fn($row) => $row->reference_code ?? 'N/A')
            ->addColumn('status', function ($row) {
                return view('costcenter::inventory.partials.status', [
                    'status' => $row->status
                ])->render();
            })
            ->addColumn('transfer_type', function ($row) {
                return view('costcenter::inventory.partials.transfer_type', [
                    'source_type'      => $row->source_type,
                    'destination_type' => $row->destination_type
                ])->render();
            })
            ->addColumn('origin', fn($row) => $this->resolveLocationName($row->source_type, $row->source_id, $row->sourceCostCenter, $centerId))
            ->addColumn('destination', fn($row) => $this->resolveLocationName($row->destination_type, $row->destination_id, $row->destinationCostCenter, $centerId))
            ->addColumn('dispatched_by', fn($row) => $this->formatUserName($row->dispatchedBy))
            ->addColumn('received_by', fn($row) => $this->formatUserName($row->receivedBy))
            ->addColumn('actions', function ($row) {
                return view('costcenter::inventory.partials.see_transfer_action', [
                    'id' => $row->id
                ])->render();
            })
            ->rawColumns(['actions', 'transfer_type', 'origin', 'destination', 'status'])
            ->make(true);
    }

    /**
     * Helper para resolver el nombre de la ubicacion (Origen / Destino)
     */
    private function resolveLocationName($type, $id, $centerModel, $centerId)
    {
        if ($type === 'main') {
            return __('costcenter::main_warehouse.name');
        }

        $centerName = $centerModel?->name;
        if (!$centerName && $id) {
            $centerName = CostCenter::find($id)?->name;
        }

        if ($id == $centerId) {
            $safeName = e($centerName ?? __('costcenter::inventory.this_center'));
            return '<span class="cc-center-highlight">' . $safeName . '</span>';
        }

        return $centerName ?? 'N/A';
    }

    /**
     * Helper para concatenar y formatear el nombre del usuario
     */
    private function formatUserName($user)
    {
        if (!$user) {
            return 'N/A';
        }

        return $user->first_name . ' ' . $user->last_name;
    }

    public function getCarriers()
    {
        $carriers = $this->inventoryService->getCarriers();
        return response()->json($carriers->map(fn($c) => [
            'id'   => $c->id,
            'name' => is_array($c->name) ? ($c->name[app()->getLocale()] ?? reset($c->name)) : $c->name,
        ]));
    }

    /**
     * Muestra la vista completa del detalle de la transferencia (Sustituye a la antigua modal)
     */
    public function show($id)
    {
        $transfer = CostCenterTransfer::with([
            'movementType',
            'carrier',
            'dispatchedBy',
            'receivedBy',
            'createdBy',
            'sourceCostCenter',
            'destinationCostCenter',
            'items.productSku.product',
            'items.lot',
            'items.discrepancies.novelty'
        ])->findOrFail($id);

        // Obtenemos los tipos de novedades activas
        $novelties = Novelty::get();

        return view('costcenter::inventory.show_transfer', compact('transfer', 'novelties'));
    }

    /**
     * Procesa la confirmación de recepción y las evidencias
     */
    public function receive(ReceiveTransferRequest $request, $id)
    {
        $validatedData = $request->validated();

        // 1. Preparamos el payload y subimos los PDFs si existen
        $payload = [
            'reception_notes' => $validatedData['reception_notes'] ?? null,
            'items' => []
        ];

        foreach ($validatedData['items'] as $index => $itemData) {
            $evidencePath = null;

            // Si el request trae un archivo PDF en esta posición del array
            if ($request->hasFile("items.{$index}.evidence_file")) {
                $file = $request->file("items.{$index}.evidence_file");

                // Creamos un nombre único para evitar que se sobreescriban
                $fileName = 'transfer_evidence_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Movemos el archivo físicamente a la carpeta public/uploads/ (creando la carpeta si no existe)
                $file->move(public_path('uploads/transfers'), $fileName);

                // Guardamos en la BD la ruta relativa
                $evidencePath = 'uploads/transfers/' . $fileName;
            }

            $payload['items'][] = [
                'transfer_item_id' => $itemData['transfer_item_id'],
                'received_qty'     => $itemData['received_qty'],
                'novelty_id'       => $itemData['novelty_id'] ?? null,
                'description'      => $itemData['description'] ?? null,
                'evidence_path'    => $evidencePath,
            ];
        }

        try {
            // 2. Enviamos al servicio que construimos en la Fase 2
            $result = $this->inventoryService->receiveTransfer($id, $payload, auth()->id());

            // 3. Log de Actividad
            $logMsg = $result['has_discrepancies']
                ? 'Recepción de inventario completada CON NOVEDADES.'
                : 'Recepción de inventario completada exitosamente.';

            LogActivity::successLog($logMsg, ['transfer_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => route('cost_centers.inventory.transactions.show', $id) // Le decimos al front a dónde ir
            ], 200);
        } catch (\Exception $e) {
            LogActivity::errorLog('Error al recibir transferencia', ['transfer_id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Muestra la modal express de confirmación cuando la transferencia es exitosa
     */
    public function transactionDetail($id, Request $request)
    {
        $transfer = CostCenterTransfer::with([
            'movementType',
            'carrier',
            'dispatchedBy',
            'receivedBy',
            'createdBy',
            'sourceCostCenter',
            'destinationCostCenter',
            // Mantenemos movements por compatibilidad con la vista de la modal antigua
            'movements.productSku.product',
            'movements.lot',
            // Cargamos items por la nueva estructura
            'items.productSku.product',
            'items.lot'
        ])->findOrFail($id);

        $showCountdown = $request->boolean('countdown', false);

        // AHORA: Redirigimos a la nueva vista de detalle (Full View) al terminar el contador
        $redirectUrl = route('cost_centers.inventory.all-transactions');

        return view('costcenter::inventory.partials.transfer_detail_modal', compact('transfer', 'showCountdown', 'redirectUrl'))->render();
    }
}
