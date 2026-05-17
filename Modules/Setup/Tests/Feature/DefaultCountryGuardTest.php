<?php

namespace Modules\Setup\Tests\Feature;

use App\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Setup\Entities\Country;
use Modules\Setup\Services\DefaultLocationGuard;
use Tests\TestCase;

class DefaultCountryGuardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cannot_deactivate_default_country_when_it_is_the_only_active()
    {
        $user = User::find(1);
        $this->actingAs($user);

        // First deactivate ALL existing active countries to isolate the test
        Country::where('status', 1)->update(['status' => 0]);

        $colombia = Country::create([
            'name' => 'Colombia',
            'code' => 'CO',
            'phonecode' => '57',
            'flag' => null,
            'status' => 1,
            'is_default' => true,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Operación denegada: La plataforma requiere al menos un país activo para operar');

        $service = app(DefaultLocationGuard::class);
        $service->guardDeactivation($colombia->id);
    }

    public function test_cannot_deactivate_last_active_country()
    {
        $user = User::find(1);
        $this->actingAs($user);

        // First deactivate ALL existing active countries to isolate the test
        Country::where('status', 1)->update(['status' => 0]);

        $country = Country::create([
            'name' => 'Brasil',
            'code' => 'BR',
            'phonecode' => '55',
            'flag' => null,
            'status' => 1,
            'is_default' => true, // Also set as default to pass the first guard check
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Operación denegada: La plataforma requiere al menos un país activo para operar');

        $service = app(DefaultLocationGuard::class);
        $service->guardDeactivation($country->id);
    }

    public function test_can_deactivate_country_when_others_are_active()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $countryOne = Country::create([
            'name' => 'Venezuela',
            'code' => 'VE',
            'phonecode' => '58',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);
        Country::create([
            'name' => 'Panama',
            'code' => 'PA',
            'phonecode' => '507',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $service = app(DefaultLocationGuard::class);
        $service->guardDeactivation($countryOne->id);

        $this->assertTrue(true);
    }

    public function test_set_default_clears_previous_default()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $first = Country::create([
            'name' => 'Honduras',
            'code' => 'HN',
            'phonecode' => '504',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);
        $second = Country::create([
            'name' => 'Nicaragua',
            'code' => 'NI',
            'phonecode' => '505',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $service = app(DefaultLocationGuard::class);
        $service->setDefault($first->id);

        $this->assertDatabaseHas('countries', ['id' => $first->id, 'is_default' => true]);

        $service->setDefault($second->id);

        $this->assertDatabaseHas('countries', ['id' => $second->id, 'is_default' => true]);
        $this->assertDatabaseHas('countries', ['id' => $first->id, 'is_default' => false]);
    }

    public function test_set_default_only_one_country_is_default()
    {
        $user = User::find(1);
        $this->actingAs($user);

        Country::create([
            'name' => 'El Salvador',
            'code' => 'SV',
            'phonecode' => '503',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);
        Country::create([
            'name' => 'Guatemala',
            'code' => 'GT',
            'phonecode' => '502',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);
        $third = Country::create([
            'name' => 'Paraguay',
            'code' => 'PY',
            'phonecode' => '595',
            'flag' => null,
            'status' => 1,
            'is_default' => false,
        ]);

        $service = app(DefaultLocationGuard::class);
        $service->setDefault($third->id);

        $this->assertDatabaseHas('countries', ['id' => $third->id, 'is_default' => true]);
        $this->assertDatabaseHas('countries', ['code' => 'SV', 'is_default' => false]);
        $this->assertDatabaseHas('countries', ['code' => 'GT', 'is_default' => false]);
    }
}
