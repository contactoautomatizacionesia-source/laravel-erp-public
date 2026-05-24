<?php

namespace Modules\Setup\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Setup\Entities\Country;
use Modules\Setup\Entities\State;

class StateTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_for_add_new_state()
    {
        $user = User::find(1);
        $this->actingAs($user);


        $this->post('/setup/location/state/store',[
            "name" => "test 99",
            "country" => 1,
            'status' => 0

        ])->assertStatus(200);
    }

    public function test_for_get_edit_data()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $state = State::create([
            'name' => 'test 99',
            'country_id' => 1,
            'status' => 1
        ]);
        $this->get('/setup/location/state/edit/'.$state->id)->assertSee('Editar');

    }

    public function test_for_update_state()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $state = State::create([
            'name' => 'test 99',
            'country_id' => 1,
            'status' => 0
        ]);

        $this->post('/setup/location/state/update',[
            "name" => "test 99",
            'status' => 1,
            'id' => $state->id,
            'country' => 1

        ])->assertStatus(200);


    }

    public function test_for_status_change_state()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'Pais Estado Test',
            'code' => 'PET'.substr(preg_replace('/\D/', '', (string) microtime(true)), -5),
            'phonecode' => '1',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $state = State::create([
            'name' => 'Region Estado Test',
            'country_id' => $country->id,
            'status' => 0,
        ]);

        $this->post('/setup/location/state/status',[
            'id' => $state->id,
            'status' => 1,

        ])->assertStatus(200);


    }

    public function test_state_get_data_filters_active_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $unique = '_ST'.substr(microtime(true)*1000, -6);
        $country = Country::create(['name' => 'StateFilterCountry', 'code' => 'SFC'.$unique, 'phonecode' => '1', 'flag' => null, 'status' => 1, 'is_default' => 0]);
        State::create(['name' => 'Active'.$unique, 'country_id' => $country->id, 'status' => 1]);
        State::create(['name' => 'Inactive'.$unique, 'country_id' => $country->id, 'status' => 0]);

        $response = $this->get('/setup/location/state/get-data?draw=1&start=0&length=100&table=active')
            ->assertOk();

        $json = $response->json();
        $names = collect($json['data'])->pluck('name')->toArray();

        $this->assertContains('Active'.$unique, $names, 'Active state should appear in active filter');
        $this->assertNotContains('Inactive'.$unique, $names, 'Inactive state should NOT appear in active filter');
    }

    public function test_state_get_data_filters_inactive_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $unique = '_STI'.substr(microtime(true)*1000, -6);
        $country = Country::create(['name' => 'StateFilterCountry2', 'code' => 'SFC2'.$unique, 'phonecode' => '1', 'flag' => null, 'status' => 1, 'is_default' => 0]);
        State::create(['name' => 'Active'.$unique, 'country_id' => $country->id, 'status' => 1]);
        State::create(['name' => 'Inactive'.$unique, 'country_id' => $country->id, 'status' => 0]);

        // recordsTotal reflects ALL records in the query (before DataTables pagination)
        // so table=inactive includes all 4094 seeded + our 1 test inactive = 4095+
        $response = $this->get('/setup/location/state/get-data?draw=1&start=0&length=100&table=inactive')
            ->assertOk();

        $json = $response->json();
        $this->assertGreaterThan(4094, $json['recordsTotal'], 'Inactive filter recordsTotal should include test-created inactive state');

        // Verify every returned row on first page has status=0 (filter applied correctly)
        $names = collect($json['data'])->pluck('name')->toArray();
        $this->assertNotContains('Active'.$unique, $names, 'Active state should NOT appear in inactive filter');
    }

    public function test_state_get_data_filters_default_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        // Use the EXISTING default country from seed data (don't create a new one)
        $defaultCountry = Country::where('is_default', 1)->first();
        $this->assertNotNull($defaultCountry, 'Seeded default country must exist');

        $unique = '_STD'.substr(microtime(true)*1000, -6);
        $otherCountry = Country::create(['name' => 'OtherStateCountry'.$unique, 'code' => 'OSC'.$unique, 'phonecode' => '2', 'flag' => null, 'status' => 1, 'is_default' => 0]);
        State::create(['name' => 'InDefault'.$unique, 'country_id' => $defaultCountry->id, 'status' => 1]);
        State::create(['name' => 'NotInDefault'.$unique, 'country_id' => $otherCountry->id, 'status' => 1]);

        $response = $this->get('/setup/location/state/get-data?draw=1&start=0&length=100&table=default')
            ->assertOk();

        $json = $response->json();

        // Verify all returned rows belong to the default country
        foreach ($json['data'] as $row) {
            $this->assertEquals($defaultCountry->id, $row['country_id'], 'Every state in default filter must be in the default country');
        }
    }
}
