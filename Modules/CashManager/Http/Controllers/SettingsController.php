<?php

namespace Modules\CashManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Setup\Services\CountryService;
use Modules\CostCenter\Entities\CostCenter;
use Modules\CashManager\Entities\CatDenomination;
use Modules\CashManager\Entities\CashBox;
use Modules\CashManager\Http\Requests\StoreDenominationRequest;
use Modules\CashManager\Http\Requests\StoreCashBoxRequest;
use Modules\CashManager\Http\Requests\SaveOperatorRolesRequest;

class SettingsController extends Controller
{
    public function __construct(private CountryService $countryService) {}

    public function index()
    {
        $countries   = $this->countryService->getActiveAll();
        $costCenters = CostCenter::orderBy('name')->get();

        $denominations = CatDenomination::with('country')
            ->orderBy('value', 'desc')
            ->get()
            ->map(fn ($d) => [
                'id'         => $d->id,
                'country'    => $d->country?->name ?? 'N/A',
                'value'      => $d->value,
                'type'       => $d->type,
                'type_label' => $d->type === 'BILLETE'
                    ? __('cashmanager::cash_manager.type_bill')
                    : __('cashmanager::cash_manager.type_coin'),
                'is_active'  => $d->is_active,
            ]);

        $cashBoxes = CashBox::with('costCenter', 'parentBox')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn ($b) => [
                'id'         => $b->id,
                'code'       => $b->code,
                'name'       => $b->name,
                'type'       => $b->type,
                'type_label' => __('cashmanager::cash_manager.type_' . strtolower($b->type)),
                'base'       => $b->base_amount,
                'threshold'  => $b->alert_threshold,
                'status'     => $b->status,
                'cc_name'    => $b->costCenter?->name ?? '—',
                'parent_name'=> $b->parentBox?->name ?? '—',
            ]);

        // Roles disponibles en el sistema para asignar como operadores
        $allRoles = DB::table('roles')->orderBy('name')->get(['id', 'name']);

        // Roles actualmente configurados como operadores de caja
        $setting           = DB::table('cash_manager_settings')->where('key', 'operator_role_ids')->first();
        $operatorRoleIds   = $setting ? json_decode($setting->value, true) : [];

        $vaultExists = CashBox::where('type', 'VAULT')->exists();

        return view('cashmanager::settings.index', compact(
            'countries',
            'costCenters',
            'denominations',
            'cashBoxes',
            'allRoles',
            'operatorRoleIds',
            'vaultExists'
        ));
    }

    // ─── Denominaciones ─────────────────────────────────────────────────────────

    public function storeDenomination(StoreDenominationRequest $request)
    {
        CatDenomination::create($request->validated());

        return response()->json([
            'message' => __('cashmanager::cash_manager.denomination_created'),
        ]);
    }

    public function updateDenominationStatus(Request $request)
    {
        $request->validate([
            'id'        => 'required|uuid|exists:cat_denominations,id',
            'is_active' => 'required|boolean',
        ]);

        CatDenomination::findOrFail($request->id)
            ->update(['is_active' => $request->is_active]);

        return response()->json([
            'message' => __('common.updated_successfully'),
        ]);
    }

    public function destroyDenomination(string $id)
    {
        $denomination = CatDenomination::findOrFail($id);
        $denomination->delete(); // SoftDelete

        return response()->json([
            'message' => __('common.deleted_successfully'),
        ]);
    }

    // ─── Cajas ───────────────────────────────────────────────────────────────────

    /**
     * Devuelve qué tipo de caja corresponde crear según el estado actual de la jerarquía.
     * Usado por AJAX al seleccionar un centro de costo en el modal de creación.
     *
     * Reglas:
     *   1. No existe ningún VAULT → tipo = VAULT
     *   2. Existe VAULT, el CC no tiene PRINCIPAL → tipo = PRINCIPAL
     *   3. Existe VAULT, el CC ya tiene PRINCIPAL → tipo = AUXILIARY
     */
    public function nextBoxType(Request $request)
    {
        // Sin VAULT: el tipo es siempre VAULT, no se necesita CC
        if (!CashBox::where('type', 'VAULT')->exists()) {
            return response()->json([
                'type'        => 'VAULT',
                'type_label'  => __('cashmanager::cash_manager.type_vault'),
                'parent_id'   => null,
                'parent_name' => null,
                'needs_cc'    => false,
            ]);
        }

        // Con VAULT: necesita CC para determinar PRINCIPAL o AUXILIARY
        $request->validate(['cost_center_id' => 'required|integer|exists:cost_centers,id']);

        $ccId      = $request->cost_center_id;
        $principal = CashBox::where('cost_center_id', $ccId)->where('type', 'PRINCIPAL')->first();

        if (!$principal) {
            $vault = CashBox::where('type', 'VAULT')->first();
            return response()->json([
                'type'        => 'PRINCIPAL',
                'type_label'  => __('cashmanager::cash_manager.type_principal'),
                'parent_id'   => $vault->id,
                'parent_name' => $vault->name,
                'needs_cc'    => true,
            ]);
        }

        return response()->json([
            'type'        => 'AUXILIARY',
            'type_label'  => __('cashmanager::cash_manager.type_auxiliary'),
            'parent_id'   => $principal->id,
            'parent_name' => $principal->name,
            'needs_cc'    => true,
        ]);
    }

    public function storeBox(StoreCashBoxRequest $request)
    {
        $data = $request->validated();

        [$type, $parentId] = $this->resolveBoxTypeAndParent($data['cost_center_id'] ?? null);

        if ($type === null) {
            return response()->json(['message' => __('cashmanager::cash_manager.error_hierarchy_violated')], 422);
        }

        // El código usa el nombre del CC para PRINCIPAL/AUXILIARY, y 'VLT' directo para VAULT
        $costCenter = isset($data['cost_center_id'])
            ? CostCenter::find($data['cost_center_id'])
            : null;

        $ccPrefix   = $costCenter
            ? strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $costCenter->name), 0, 3))
            : 'SYS';

        $typePrefix = match ($type) {
            'VAULT'     => 'VLT',
            'PRINCIPAL' => 'MAIN',
            default     => 'AUX',
        };

        $count = CashBox::where('type', $type)->count() + 1;
        $code  = sprintf('%s-%s-%02d', $ccPrefix, $typePrefix, $count);
        while (CashBox::where('code', $code)->exists()) {
            $count++;
            $code = sprintf('%s-%s-%02d', $ccPrefix, $typePrefix, $count);
        }

        $box = CashBox::create([
            'cost_center_id'  => $data['cost_center_id'] ?? null,
            'parent_id'       => $parentId,
            'type'            => $type,
            'code'            => $code,
            'name'            => $data['name'],
            'base_amount'     => $data['base_amount'],
            'alert_threshold' => $data['alert_threshold'] ?? null,
            'status'          => 'AVAILABLE',
        ]);

        return response()->json(['message' => __('cashmanager::cash_manager.box_created', ['code' => $box->code])]);
    }

    /**
     * Determina el tipo y parent_id que debe tener la próxima caja del CC dado.
     * Retorna [type, parentId] o [null, null] si la jerarquía no lo permite.
     */
    private function resolveBoxTypeAndParent(?int $costCenterId): array
    {
        if (!CashBox::where('type', 'VAULT')->exists()) {
            return ['VAULT', null];
        }

        if (!$costCenterId) {
            return [null, null];
        }

        $principal = CashBox::where('cost_center_id', $costCenterId)->where('type', 'PRINCIPAL')->first();
        $vault     = !$principal ? CashBox::where('type', 'VAULT')->first() : null;

        return $principal
            ? ['AUXILIARY', $principal->id]
            : ['PRINCIPAL', $vault->id];
    }

    // ─── Roles de operador ───────────────────────────────────────────────────────

    public function saveOperatorRoles(SaveOperatorRolesRequest $request)
    {
        DB::table('cash_manager_settings')->updateOrInsert(
            ['key' => 'operator_role_ids'],
            [
                'value'      => json_encode($request->validated()['operator_role_ids']),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'message' => __('common.updated_successfully'),
        ]);
    }
}
