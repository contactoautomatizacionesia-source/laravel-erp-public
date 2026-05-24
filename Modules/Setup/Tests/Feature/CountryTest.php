<?php

namespace Modules\Setup\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Modules\Setup\Entities\Country;

class CountryTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_for_add_new_country()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $this->get('/setup/location/country')->assertSee(__('setup.default'));

        $code = 'T'.substr(preg_replace('/\D/', '', (string) microtime(true)), -6);

        $this->post('/setup/location/country/store',[
            'name' => 'Prueba Pais',
            'code' => $code,
            'phonecode' => '57',
            'status' => 0,

        ])->assertStatus(200);


    }

    public function test_for_get_edit_data()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'test 99',
            'code' => '7iufdj',
            'phonecode' => '9389',
            'status' => 0,
            'flag' => null
        ]);
        $this->get('/setup/location/country/edit/'.$country->id)->assertSee('Editar');

    }

    public function test_for_update_country()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'test 99',
            'code' => '7iufdj',
            'phonecode' => '9389',
            'status' => 0,
            'flag' => null
        ]);

        $newCode = 'U'.substr(preg_replace('/\D/', '', (string) microtime(true)), -6);

        $this->post('/setup/location/country/update',[
            'name' => 'Nombre Actualizado',
            'code' => $newCode,
            'phonecode' => '58',
            'status' => 1,
            'id' => $country->id,

        ])->assertStatus(200);


    }

    public function test_for_status_change_country()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $country = Country::create([
            'name' => 'test 99',
            'code' => '7iufdj',
            'phonecode' => '9389',
            'status' => 0,
            'flag' => null
        ]);

        $this->post('/setup/location/country/status',[
            'id' => $country->id,
            'status' => 1

        ])->assertStatus(200);


    }

    public function test_country_get_data_filters_active_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $unique = '_ACT'.substr(microtime(true)*1000, -6);
        Country::create(['name' => 'Active'.$unique, 'code' => 'AC'.substr(microtime(true)*1000, -4), 'phonecode' => '1', 'status' => 1, 'flag' => null]);
        Country::create(['name' => 'Inactive'.$unique, 'code' => 'IC'.substr(microtime(true)*1000, -4), 'phonecode' => '2', 'status' => 0, 'flag' => null]);

        $response = $this->get('/setup/location/country/get-data?draw=1&start=0&length=100&table=active&search[value]=Active'.$unique)
            ->assertOk();

        $json = $response->json();
        $names = collect($json['data'])->pluck('name')->toArray();

        $this->assertContains('Active'.$unique, $names, 'Active country should appear in active filter');
        $this->assertNotContains('Inactive'.$unique, $names, 'Inactive country should NOT appear in active filter');
    }

    public function test_country_get_data_filters_inactive_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $unique = '_INA'.substr(microtime(true)*1000, -6);
        Country::create(['name' => 'Active'.$unique, 'code' => 'AI'.substr(microtime(true)*1000, -4), 'phonecode' => '1', 'status' => 1, 'flag' => null]);
        Country::create(['name' => 'Inactive'.$unique, 'code' => 'II'.substr(microtime(true)*1000, -4), 'phonecode' => '2', 'status' => 0, 'flag' => null]);

        $response = $this->get('/setup/location/country/get-data?draw=1&start=0&length=500&table=inactive')
            ->assertOk();

        $json = $response->json();
        $names = collect($json['data'])->pluck('name')->toArray();

        $this->assertContains('Inactive'.$unique, $names, 'Inactive country should appear in inactive filter');
        $this->assertNotContains('Active'.$unique, $names, 'Active country should NOT appear in inactive filter');
    }

    public function test_country_get_data_filters_default_by_name_unique()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $unique = '_DEF'.substr(microtime(true)*1000, -6);
        Country::create(['name' => 'Default'.$unique, 'code' => 'DC'.substr(microtime(true)*1000, -4), 'phonecode' => '1', 'status' => 1, 'flag' => null, 'is_default' => 1]);
        Country::create(['name' => 'NonDefault'.$unique, 'code' => 'ND'.substr(microtime(true)*1000, -4), 'phonecode' => '2', 'status' => 1, 'flag' => null, 'is_default' => 0]);

        $response = $this->get('/setup/location/country/get-data?draw=1&start=0&length=100&table=default&search[value]=Default'.$unique)
            ->assertOk();

        $json = $response->json();
        $names = collect($json['data'])->pluck('name')->toArray();

        $this->assertContains('Default'.$unique, $names, 'Default country should appear in default filter');
        $this->assertNotContains('NonDefault'.$unique, $names, 'Non-default country should NOT appear in default filter');
    }
}
