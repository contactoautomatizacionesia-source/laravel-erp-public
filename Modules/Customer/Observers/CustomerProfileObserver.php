<?php

namespace Modules\Customer\Observers;

use Modules\Customer\Entities\CustomerProfile;
use Modules\NetworkTree\Services\NetworkTreeManager;

class CustomerProfileObserver
{
    public function __construct(private NetworkTreeManager $treeService)
    {
    }

    /**
     * Se dispara al crear un CustomerProfile nuevo.
     * Inserta el nodo en la closure table con su padre (representative_id).
     */
    public function created(CustomerProfile $profile): void
    {
        $this->treeService->insertNode(
            $profile->user_id,
            $profile->representative_id  // null si es raíz
        );

        $this->clearTreeCache([$profile->user_id, $profile->representative_id]);
    }

    /**
     * Se dispara al actualizar un CustomerProfile existente.
     * Solo actúa si representative_id cambió — mueve el nodo junto con su
     * subárbol completo al nuevo padre de forma atómica.
     */
    public function updated(CustomerProfile $profile): void
    {
        if (! $profile->wasChanged('representative_id')) {
            return;
        }

        $oldParent = $profile->getOriginal('representative_id');
        $newParent = $profile->representative_id;

        $this->treeService->moveSubtree(
            $profile->user_id,
            $newParent  // null si pasa a ser raiz
        );

        $this->clearTreeCache([$profile->user_id, $oldParent, $newParent]);
    }

    private function clearTreeCache(array $userIds): void
    {
        $ids = collect($userIds)->filter()->unique();
        foreach ($ids as $id) {
            for ($d = 1; $d <= 5; $d++) {
                cache()->forget("network_tree_{$id}_d{$d}");
            }
            cache()->forget("network_stats_{$id}");
        }
    }
}
