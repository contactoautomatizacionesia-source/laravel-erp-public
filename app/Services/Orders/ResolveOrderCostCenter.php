<?php

namespace App\Services\Orders;

use Modules\CostCenter\Entities\CostCenter;
use Modules\Shipping\Entities\PickupLocation;

class ResolveOrderCostCenter
{
    public function execute(string $deliveryType, $pickupLocation = null, $shippingAddress = null): ?int
    {
        if ($deliveryType === 'pickup_location') {
            return $this->resolveFromPickupLocation($pickupLocation) ?? $this->defaultCostCenterId();
        }

        $cityId = $shippingAddress->city_id ?? $shippingAddress->city ?? $shippingAddress->shipping_city_id ?? null;
        $stateId = $shippingAddress->state_id ?? $shippingAddress->state ?? $shippingAddress->shipping_state_id ?? null;

        return $this->resolveFromLocation($cityId, $stateId) ?? $this->defaultCostCenterId();
    }

    private function resolveFromPickupLocation($pickupLocation): ?int
    {
        if (!$pickupLocation) {
            return null;
        }

        $pickupLocationId = $pickupLocation instanceof PickupLocation
            ? $pickupLocation->id
            : (int) $pickupLocation;

        if ($pickupLocationId <= 0) {
            return null;
        }

        return $this->activePickupLocationQuery()
            ->whereKey($pickupLocationId)
            ->value('cost_center_id');
    }

    private function resolveFromLocation($cityId, $stateId): ?int
    {
        $defaultCostCenterId = $this->defaultCostCenterId() ?? 0;

        if ($cityId) {
            $cityMatch = $this->activeCostCenterQuery($defaultCostCenterId)
                ->where('city_id', $cityId)
                ->inRandomOrder()
                ->value('id');

            if ($cityMatch) {
                return (int) $cityMatch;
            }
        }

        if ($stateId) {
            $stateMatch = $this->activeCostCenterQuery($defaultCostCenterId)
                ->whereHas('city', function ($query) use ($stateId) {
                    $query->where('state_id', $stateId);
                })
                ->inRandomOrder()
                ->value('id');

            if ($stateMatch) {
                return (int) $stateMatch;
            }
        }

        return null;
    }

    private function defaultCostCenterId(): ?int
    {
        return $this->databaseDefaultCostCenterId();
    }

    private function databaseDefaultCostCenterId(): ?int
    {
        $costCenterId = CostCenter::query()
            ->where('status', 1)
            ->where('is_default', 1)
            ->value('id');

        return $costCenterId ? (int) $costCenterId : null;
    }

    private function activePickupLocationQuery()
    {
        return PickupLocation::query()
            ->where('status', 1)
            ->whereNotNull('cost_center_id')
            ->whereHas('costCenter', function ($query) {
                $query->where('status', 1);
            });
    }

    private function activeCostCenterQuery(?int $excludedCostCenterId = null)
    {
        return CostCenter::query()
            ->where('status', 1)
            ->when($excludedCostCenterId > 0, function ($query) use ($excludedCostCenterId) {
                $query->whereKeyNot($excludedCostCenterId);
            });
    }
}
