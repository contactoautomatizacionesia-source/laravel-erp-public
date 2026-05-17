<?php

namespace Modules\CostCenter\Actions;

use App\Models\User;
use Modules\CostCenter\Entities\CostCenter;
use Modules\Shipping\Entities\PickupLocation;

class SyncPickupLocation
{
    private static ?int $superadminId = null;

    public function execute(CostCenter $costCenter): void
    {
        $costCenter->loadMissing('city.state.country');
        $city    = $costCenter->city;
        $state   = $city?->state;
        $country = $state?->country;

        if (auth()->id()) {
            $createdBy = auth()->id();
        } else {
            if (self::$superadminId === null) {
                self::$superadminId = User::whereHas('role', fn ($q) => $q->where('type', 'superadmin'))->value('id');
            }
            $createdBy = self::$superadminId;
        }

        PickupLocation::updateOrCreate(
            ['cost_center_id' => $costCenter->id],
            [
                'pickup_location' => $costCenter->name,
                'name'            => $costCenter->name,
                'phone'           => $costCenter->phone,
                'address'         => $costCenter->address,
                'pin_code'        => $costCenter->pin_code,
                'city_id'         => $costCenter->city_id,
                'state_id'        => $state?->id,
                'country_id'      => $country?->id,
                'status'          => $costCenter->status,
                'email'           => null,
                'address_2'       => null,
                'lat'             => null,
                'long'            => null,
                'created_by'      => $createdBy,
            ]
        );
    }
}
