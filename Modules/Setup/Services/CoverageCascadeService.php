<?php

namespace Modules\Setup\Services;

use DomainException;
use Illuminate\Support\Facades\DB;
use Modules\Setup\Entities\City;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;

class CoverageCascadeService
{
    public function previewCascade(string $type, int $id): array
    {
        if ($type === 'country') {
            $stateIds = State::where('country_id', $id)
                ->where('status', 1)
                ->pluck('id');

            $statesCount = $stateIds->count();
            $citiesCount = City::whereIn('state_id', $stateIds)
                ->where('status', 1)
                ->count();

            return [
                'states' => $statesCount,
                'cities' => $citiesCount,
            ];
        }

        if ($type === 'state') {
            $citiesCount = City::where('state_id', $id)
                ->where('status', 1)
                ->count();

            return [
                'states' => 0,
                'cities' => $citiesCount,
            ];
        }

        return [
            'states' => 0,
            'cities' => 0,
        ];
    }

    public function deactivateCountry(int $id): void
    {
        $country = Country::findOrFail($id);
        $activeCount = Country::where('status', 1)->count();

        if ($country->is_default && $activeCount <= 1) {
            throw new DomainException('Operación denegada: La plataforma requiere al menos un país activo para operar');
        }

        if ($activeCount <= 1) {
            throw new DomainException('Operación denegada: La plataforma requiere al menos un país activo para operar');
        }

        DB::transaction(function () use ($id) {
            $stateIds = State::where('country_id', $id)->pluck('id');
            Country::where('id', $id)->update(['status' => 0]);
            State::whereIn('id', $stateIds)->update(['status' => 0]);
            City::whereIn('state_id', $stateIds)->update(['status' => 0]);
        });
    }

    public function deactivateState(int $id): void
    {
        DB::transaction(function () use ($id) {
            State::where('id', $id)->update(['status' => 0]);
            City::where('state_id', $id)->update(['status' => 0]);
        });
    }

    public function deactivateCity(int $id): void
    {
        City::where('id', $id)->update(['status' => 0]);
    }

    public function activateState(int $id): void
    {
        $state = State::with('country')->findOrFail($id);

        if ((int) $state->country->status === 0) {
            throw new DomainException(
                'No se puede activar el registro: El nivel superior ' . $state->country->name . ' está inactivo'
            );
        }

        State::where('id', $id)->update(['status' => 1]);
    }

    public function activateCity(int $id): void
    {
        $city = City::with('state.country')->findOrFail($id);

        if ((int) $city->state->status === 0) {
            throw new DomainException(
                'No se puede activar el registro: El nivel superior ' . $city->state->name . ' está inactivo'
            );
        }

        if ((int) $city->state->country->status === 0) {
            throw new DomainException(
                'No se puede activar el registro: El nivel superior ' . $city->state->country->name . ' está inactivo'
            );
        }

        City::where('id', $id)->update(['status' => 1]);
    }
}
