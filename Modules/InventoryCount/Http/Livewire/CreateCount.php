<?php

namespace Modules\InventoryCount\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\InventoryCount\Services\InventoryCountService;
use Illuminate\Database\Eloquent\Collection;

class CreateCount extends Component
{
    use WithPagination;

    // --- Estado del conteo ---
    public int    $costCenterId;
    public ?int   $countId       = null;  // null hasta el primer submit exitoso o en reintento
    public string $countCode     = '';
    public bool   $countStarted  = false;

    // --- Datos del formulario ---
    public array  $quantities        = [];   // [product_id => qty]
    public array  $observations      = [];   // [product_id => observation_type_id]
    public string $searchTerm        = '';
    public string $filterMode        = 'all'; // 'all' | 'pending'

    // --- Estado UI ---
    public int    $countedCount      = 0;
    public int    $totalCount        = 0;
    public bool   $showConfirmModal  = false;
    public bool   $submitting        = false;

    // --- Datos de referencia (cargados en mount, serializados por Livewire) ---
    public array $products        = [];
    public Collection $observationTypes;

    protected InventoryCountService $service;

    public function boot(InventoryCountService $service)
    {
        $this->service = $service;
    }

    public function mount(int $costCenterId, string $countCode)
    {
        $this->costCenterId = $costCenterId;
        $this->countCode    = $countCode;

        $data                   = $this->service->getCountFormData($costCenterId);
        $this->products         = $data['products']->toArray();
        $this->observationTypes = $data['observationTypes'];
        $this->totalCount       = count($this->products);

        // Inicializar en blanco
        foreach ($this->products as $product) {
            $pid = $product['product_id'];
            $this->quantities[$pid]   = null;
            $this->observations[$pid] = null;
        }

        // Restaurar draft de sesión si existe
        $draft = session($this->draftKey());
        if ($draft) {
            $this->countStarted = true;
            // Solo restaurar valores de productos que siguen existiendo
            foreach ($this->products as $product) {
                $pid = $product['product_id'];
                if (isset($draft['quantities'][$pid])) {
                    $this->quantities[$pid] = $draft['quantities'][$pid];
                }
                if (isset($draft['observations'][$pid])) {
                    $this->observations[$pid] = $draft['observations'][$pid];
                }
            }
            $this->updateCountedCount();
        }
    }

    /**
     * Solo marca el conteo como iniciado en la UI, guarda draft inicial en sesión.
     * NO crea ningún registro en BD.
     */
    public function startCount(array $deviceInfo = [])
    {
        if ($this->countStarted) {
            return;
        }

        $this->countStarted = true;
        $this->saveDraft();
        $this->emit('toastr-success', __('inventorycount::messages.count_started'));
    }

    public function updatedQuantities()
    {
        $this->updateCountedCount();
        $this->saveDraft();
    }

    public function updatedObservations()
    {
        $this->saveDraft();
    }

    public function resetFilters()
    {
        $this->searchTerm = '';
        $this->filterMode = 'all';
    }

    public function openConfirmModal()
    {
        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    /**
     * Crea el conteo en BD, Crea el conteo en BD de forma completa en una sola operación al confirmar.
     */
    public function confirmSubmit(array $deviceInfo = []): mixed
    {
        if ($this->submitting) {
            return null;
        }

        $this->submitting       = true;
        $this->showConfirmModal = false;

        $result = $this->service->createAndSubmit(
            $this->costCenterId,
            auth()->id(),
            $deviceInfo,
            $this->buildLines()
        );

        if (! $result['success']) {
            $this->submitting = false;
            $this->emit('toastr-error', $result['message']);
            return null;
        }

        $this->clearDraft();
        session()->flash('count_result', [
            'status'  => $result['status'],
            'message' => $result['message'],
        ]);
        return redirect()->route('inventory_count.show', $result['count_id']);
    }

    public function render()
    {
        $filtered = collect($this->products);

        if ($this->searchTerm) {
            $term     = mb_strtolower($this->searchTerm);
            $filtered = $filtered->filter(fn ($p) =>
                str_contains(mb_strtolower($this->translateProductName($p['product_name'])), $term)
            );
        }

        if ($this->filterMode === 'pending') {
            $filtered = $filtered->filter(fn ($p) =>
                $this->quantities[$p['product_id']] === null
            );
        }

        return view('inventorycount::livewire.create-count', [
            'filteredProducts' => $filtered->values(),
            'observationTypes' => $this->observationTypes,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function buildLines(): array
    {
        $lines = [];
        foreach ($this->products as $product) {
            $pid     = $product['product_id'];
            $lines[] = [
                'product_id'          => $pid,
                'system_stock'        => (int) $product['current_stock'],
                'physical_quantity'   => $this->quantities[$pid] !== null ? (int) $this->quantities[$pid] : null,
                'observation_type_id' => $this->observations[$pid] ?? null,
            ];
        }
        return $lines;
    }

    private function updateCountedCount(): void
    {
        $this->countedCount = collect($this->quantities)
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->count();
    }

    private function translateProductName(string $json): string
    {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            return $decoded[app()->getLocale()] ?? reset($decoded) ?? $json;
        }
        return $json;
    }

    private function draftKey(): string
    {
        return 'inventory_count_draft_' . $this->countCode;
    }

    private function saveDraft(): void
    {
        session([
            $this->draftKey() => [
                'quantities'   => $this->quantities,
                'observations' => $this->observations,
                'countId'      => $this->countId,
            ],
        ]);
    }

    private function clearDraft(): void
    {
        session()->forget($this->draftKey());
    }
}
