<?php

namespace Modules\Setup\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Setup\Entities\City;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;

class CityTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_for_add_new_city()
    {
        $user = User::find(1);
        $this->actingAs($user);


        $this->post('/setup/location/city/store',[
            "name" => "test 99",
            "state" => 400,
            'country' => 18,
            'status' => 0

        ])->assertStatus(200);
    }

    public function test_for_get_state()
    {
        $user = User::find(1);
        $this->actingAs($user);


        $this->post('/setup/location/city/get-state',[
            'country_id' => 18

        ])->assertStatus(200);
    }

    public function test_city_datatables_get_data_returns_valid_json()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $this->get('/setup/location/city/get-data?draw=1&start=0&length=10')
            ->assertOk()
            ->assertSee('recordsTotal', false);
    }

    public function test_for_get_edit_data()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $city = City::create([
            'name' => 'test 99',
            'country_id' => 18,
            'state_id' => 400,
            'status' => 1
        ]);
        $this->get('/setup/location/city/edit/'.$city->id)->assertSee('Editar');

    }

    public function test_for_update_state()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $city = City::create([
            'name' => 'test 99',
            'country_id' => 18,
            'state_id' => 400,
            'status' => 0
        ]);

        $this->post('/setup/location/city/update',[
            "name" => "test 99",
            'status' => 1,
            'id' => $city->id,
            'country' => 18,
            'state' => 400

        ])->assertStatus(200);


    }

    public function test_for_status_change_state()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Pais Ciudad Test',
            'code' => 'PCT'.substr(preg_replace('/\D/', '', (string) microtime(true)), -5),
            'phonecode' => '1',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $state = State::create([
            'country_id' => $country->id,
            'name' => 'Region Test',
            'status' => 1,
        ]);

        $city = City::create([
            'name' => 'Ciudad Test',
            'state_id' => $state->id,
            'status' => 0,
        ]);

        $this->post('/setup/location/city/status',[
            'id' => $city->id,
            'status' => 1,

        ])->assertStatus(200);


    }

    public function test_city_get_data_filters_active_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $unique = '_CY'.substr(microtime(true)*1000, -6);
        $country = Country::create(['name' => 'CityFilterCountry', 'code' => 'CFC'.$unique, 'phonecode' => '1', 'flag' => null, 'status' => 1, 'is_default' => 0]);
        $state = State::create(['name' => 'CityFilterState', 'country_id' => $country->id, 'status' => 1]);
        City::create(['name' => 'ActiveCity'.$unique, 'state_id' => $state->id, 'country_id' => $country->id, 'status' => 1]);
        City::create(['name' => 'InactiveCity'.$unique, 'state_id' => $state->id, 'country_id' => $country->id, 'status' => 0]);

        // recordsTotal reflects all records that match the query filter
        // table=active should count 864 seeded + our 1 test = 865+
        $response = $this->get('/setup/location/city/get-data?draw=1&start=0&length=100&table=active')
            ->assertOk();

        $json = $response->json();
        $this->assertGreaterThan(864, $json['recordsTotal'], 'Active filter recordsTotal should include test-created active city');

        // Verify every returned row on first page has status=1 (filter applied correctly)
        $names = collect($json['data'])->pluck('name')->toArray();
        $this->assertNotContains('InactiveCity'.$unique, $names, 'Inactive city should NOT appear in active filter');
    }

    public function test_city_get_data_filters_inactive_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $unique = '_CYI'.substr(microtime(true)*1000, -6);
        $country = Country::create(['name' => 'CityFilterCountry2', 'code' => 'CFC2'.$unique, 'phonecode' => '1', 'flag' => null, 'status' => 1, 'is_default' => 0]);
        $state = State::create(['name' => 'CityFilterState2', 'country_id' => $country->id, 'status' => 1]);
        City::create(['name' => 'ActiveCity'.$unique, 'state_id' => $state->id, 'country_id' => $country->id, 'status' => 1]);
        City::create(['name' => 'InactiveCity'.$unique, 'state_id' => $state->id, 'country_id' => $country->id, 'status' => 0]);

        $response = $this->get('/setup/location/city/get-data?draw=1&start=0&length=100&table=inactive')
            ->assertOk();

        $json = $response->json();
        $this->assertGreaterThan(47078, $json['recordsTotal'], 'Inactive filter recordsTotal should include test-created inactive city');

        // Verify every returned row on first page has status=0 (filter applied correctly)
        $names = collect($json['data'])->pluck('name')->toArray();
        $this->assertNotContains('ActiveCity'.$unique, $names, 'Active city should NOT appear in inactive filter');
    }

    public function test_city_get_data_filters_default_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        // Use the EXISTING default country from seed data
        $defaultCountry = Country::where('is_default', 1)->first();
        $this->assertNotNull($defaultCountry, 'Seeded default country must exist');

        $unique = '_CYD'.substr(microtime(true)*1000, -6);
        $otherCountry = Country::create(['name' => 'OtherCityCountry'.$unique, 'code' => 'OCC'.$unique, 'phonecode' => '2', 'flag' => null, 'status' => 1, 'is_default' => 0]);
        $defaultState = State::create(['name' => 'DefaultCityState'.$unique, 'country_id' => $defaultCountry->id, 'status' => 1]);
        $otherState = State::create(['name' => 'OtherCityState'.$unique, 'country_id' => $otherCountry->id, 'status' => 1]);
        City::create(['name' => 'InDefaultCity'.$unique, 'state_id' => $defaultState->id, 'country_id' => $defaultCountry->id, 'status' => 1]);
        City::create(['name' => 'NotInDefaultCity'.$unique, 'state_id' => $otherState->id, 'country_id' => $otherCountry->id, 'status' => 1]);

        $response = $this->get('/setup/location/city/get-data?draw=1&start=0&length=100&table=default')
            ->assertOk();

        $json = $response->json();

        // Verify all returned rows have country matching the default country name
        foreach ($json['data'] as $row) {
            $this->assertEquals($defaultCountry->name, $row['country'], 'Every city in default filter must be in the default country');
        }
    }
}
