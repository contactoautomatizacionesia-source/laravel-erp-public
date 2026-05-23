<?php

namespace Modules\Setup\Services;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Setup\Entities\Country;

class DefaultLocationGuard
{
    public function setDefault(int $countryId): void
    {
        $country = Country::findOrFail($countryId);
        if ((int) $country->status === 0) {
            throw new DomainException(
                'No se puede establecer como país por defecto un país inactivo.'
            );
        }

        DB::transaction(function () use ($countryId) {
            Country::where('is_default', true)
                ->where('id', '!=', $countryId)
                ->lockForUpdate()
                ->update(['is_default' => false]);

            Country::where('id', $countryId)
                ->lockForUpdate()
                ->update(['is_default' => true]);
        });
    }

    public function guardDeactivation(int $countryId): void
    {
        $country = Country::findOrFail($countryId);
        
        // Count active countries EXCLUDING the one being deactivated
        $activeCountAfterDeactivation = Country::where('status', 1)
            ->where('id', '!=', $countryId)
            ->count();

        // If it's the default country and would be the last one
        if ($country->is_default && $activeCountAfterDeactivation === 0) {
            throw new DomainException(
                'Operación denegada: La plataforma requiere al menos un país activo para operar'
            );
        }

        // If after deactivation there would be no active countries
        if ($activeCountAfterDeactivation === 0) {
            throw new DomainException(
                'Operación denegada: La plataforma requiere al menos un país activo para operar'
            );
        }
    }
}
