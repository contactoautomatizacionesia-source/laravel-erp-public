<?php

namespace Modules\Setup\Tests\Feature;

use App\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Setup\Entities\City;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;
use Modules\Setup\Services\CoverageCascadeService;
use Tests\TestCase;

class CoverageCascadeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_deactivate_country_cascades_to_states_and_cities()
    {
        $user = User::find(1);
        $this->actingAs($user);

        Country::create([
            'name' => 'Extra',
            'code' => 'EX',
            'phonecode' => '111',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $country = Country::create([
            'name' => 'Colombia',
            'code' => 'CO',
            'phonecode' => '57',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $stateOne = State::create([
            'country_id' => $country->id,
            'name' => 'State One',
            'status' => 1,
        ]);
        $stateTwo = State::create([
            'country_id' => $country->id,
            'name' => 'State Two',
            'status' => 1,
        ]);

        City::create(['state_id' => $stateOne->id, 'name' => 'City 1', 'status' => 1]);
        City::create(['state_id' => $stateOne->id, 'name' => 'City 2', 'status' => 1]);
        City::create(['state_id' => $stateTwo->id, 'name' => 'City 3', 'status' => 1]);
        City::create(['state_id' => $stateTwo->id, 'name' => 'City 4', 'status' => 1]);

        $service = app(CoverageCascadeService::class);
        $service->deactivateCountry($country->id);

        $this->assertDatabaseHas('countries', ['id' => $country->id, 'status' => 0]);
        $this->assertDatabaseHas('states', ['id' => $stateOne->id, 'status' => 0]);
        $this->assertDatabaseHas('states', ['id' => $stateTwo->id, 'status' => 0]);
        $this->assertDatabaseHas('cities', ['state_id' => $stateOne->id, 'status' => 0]);
        $this->assertDatabaseHas('cities', ['state_id' => $stateTwo->id, 'status' => 0]);
    }

    public function test_deactivate_state_cascades_to_cities()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Peru',
            'code' => 'PE',
            'phonecode' => '51',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $state = State::create([
            'country_id' => $country->id,
            'name' => 'Lima',
            'status' => 1,
        ]);

        City::create(['state_id' => $state->id, 'name' => 'City A', 'status' => 1]);
        City::create(['state_id' => $state->id, 'name' => 'City B', 'status' => 1]);
        City::create(['state_id' => $state->id, 'name' => 'City C', 'status' => 1]);

        $service = app(CoverageCascadeService::class);
        $service->deactivateState($state->id);

        $this->assertDatabaseHas('states', ['id' => $state->id, 'status' => 0]);
        $this->assertDatabaseHas('cities', ['state_id' => $state->id, 'status' => 0]);
    }

    public function test_preview_cascade_country_returns_correct_counts()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Chile',
            'code' => 'CL',
            'phonecode' => '56',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $activeStateOne = State::create([
            'country_id' => $country->id,
            'name' => 'Activa 1',
            'status' => 1,
        ]);
        $activeStateTwo = State::create([
            'country_id' => $country->id,
            'name' => 'Activa 2',
            'status' => 1,
        ]);
        State::create([
            'country_id' => $country->id,
            'name' => 'Inactiva',
            'status' => 0,
        ]);

        City::create(['state_id' => $activeStateOne->id, 'name' => 'A1', 'status' => 1]);
        City::create(['state_id' => $activeStateOne->id, 'name' => 'A2', 'status' => 1]);
        City::create(['state_id' => $activeStateOne->id, 'name' => 'A3', 'status' => 1]);
        City::create(['state_id' => $activeStateOne->id, 'name' => 'A4', 'status' => 0]);

        City::create(['state_id' => $activeStateTwo->id, 'name' => 'B1', 'status' => 1]);
        City::create(['state_id' => $activeStateTwo->id, 'name' => 'B2', 'status' => 1]);
        City::create(['state_id' => $activeStateTwo->id, 'name' => 'B3', 'status' => 1]);
        City::create(['state_id' => $activeStateTwo->id, 'name' => 'B4', 'status' => 0]);

        $service = app(CoverageCascadeService::class);
        $result = $service->previewCascade('country', $country->id);

        $this->assertSame(['states' => 2, 'cities' => 6], $result);
    }

    public function test_preview_cascade_state_returns_correct_counts()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Argentina',
            'code' => 'AR',
            'phonecode' => '54',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $state = State::create([
            'country_id' => $country->id,
            'name' => 'Cordoba',
            'status' => 1,
        ]);

        City::create(['state_id' => $state->id, 'name' => 'C1', 'status' => 1]);
        City::create(['state_id' => $state->id, 'name' => 'C2', 'status' => 1]);
        City::create(['state_id' => $state->id, 'name' => 'C3', 'status' => 1]);
        City::create(['state_id' => $state->id, 'name' => 'C4', 'status' => 1]);
        City::create(['state_id' => $state->id, 'name' => 'C5', 'status' => 0]);
        City::create(['state_id' => $state->id, 'name' => 'C6', 'status' => 0]);

        $service = app(CoverageCascadeService::class);
        $result = $service->previewCascade('state', $state->id);

        $this->assertSame(['states' => 0, 'cities' => 4], $result);
    }

    public function test_activate_city_blocked_when_state_is_inactive()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Mexico',
            'code' => 'MX',
            'phonecode' => '52',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);
        $state = State::create([
            'country_id' => $country->id,
            'name' => 'Jalisco',
            'status' => 0,
        ]);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Guadalajara',
            'status' => 0,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No se puede activar el registro: El nivel superior');

        $service = app(CoverageCascadeService::class);
        $service->activateCity($city->id);
    }

    public function test_activate_city_blocked_when_country_is_inactive()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Ecuador',
            'code' => 'EC',
            'phonecode' => '593',
            'flag' => null,
            'status' => 0,
            'is_default' => false,
        ]);
        $state = State::create([
            'country_id' => $country->id,
            'name' => 'Pichincha',
            'status' => 1,
        ]);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Quito',
            'status' => 0,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No se puede activar el registro: El nivel superior');

        $service = app(CoverageCascadeService::class);
        $service->activateCity($city->id);
    }

    public function test_activate_state_blocked_when_country_is_inactive()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Bolivia',
            'code' => 'BO',
            'phonecode' => '591',
            'flag' => null,
            'status' => 0,
            'is_default' => false,
        ]);
        $state = State::create([
            'country_id' => $country->id,
            'name' => 'La Paz',
            'status' => 0,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No se puede activar el registro: El nivel superior');

        $service = app(CoverageCascadeService::class);
        $service->activateState($state->id);
    }

    public function test_activate_state_success_when_country_is_active()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Uruguay',
            'code' => 'UY',
            'phonecode' => '598',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);
        $state = State::create([
            'country_id' => $country->id,
            'name' => 'Montevideo',
            'status' => 0,
        ]);

        $service = app(CoverageCascadeService::class);
        $service->activateState($state->id);

        $this->assertDatabaseHas('states', ['id' => $state->id, 'status' => 1]);
    }

    public function test_activate_city_success_when_parents_are_active()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Costa Rica',
            'code' => 'CR',
            'phonecode' => '506',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);
        $state = State::create([
            'country_id' => $country->id,
            'name' => 'San Jose',
            'status' => 1,
        ]);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Escazu',
            'status' => 0,
        ]);

        $service = app(CoverageCascadeService::class);
        $service->activateCity($city->id);

        $this->assertDatabaseHas('cities', ['id' => $city->id, 'status' => 1]);
    }
}
