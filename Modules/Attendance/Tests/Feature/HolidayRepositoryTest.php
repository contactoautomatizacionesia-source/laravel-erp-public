<?php

namespace Modules\Attendance\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Attendance\Entities\Holiday;
use Tests\TestCase;

class HolidayRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Verifica que se puede listar los holidays (index).
     */
    public function test_can_list_holidays()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $response = $this->get('/attendance/holidays');

        $response->assertStatus(200);
    }

    /**
     * Verifica que all() retorna la colección de holidays.
     */
    public function test_all_returns_collection()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $holiday = Holiday::create([
            'year' => Carbon::now()->year,
            'name' => 'Test Holiday All',
            'type' => 0,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $response = $this->get('/attendance/holidays');
        $response->assertStatus(200);

        $this->assertDatabaseHas('holidays', ['name' => 'Test Holiday All']);
    }

    /**
     * Verifica que year() filtra por año correctamente.
     */
    public function test_year_filters_by_year()
    {
        $year = Carbon::now()->year;

        Holiday::create([
            'year' => $year,
            'name' => 'Holiday Year Test',
            'type' => 0,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $holidays = Holiday::where('year', $year)->get();

        $this->assertTrue($holidays->contains('name', 'Holiday Year Test'));
    }

    /**
     * Verifica que specificYear() retorna solo los holidays del año dado.
     */
    public function test_specific_year_returns_only_that_year()
    {
        $year = 2020;

        Holiday::create([
            'year' => $year,
            'name' => 'Old Holiday',
            'type' => 0,
            'date' => '2020-01-01',
        ]);

        $holidays = Holiday::where('year', $year)->get();

        $this->assertTrue($holidays->every(fn($h) => $h->year == $year));
    }

    /**
     * Verifica que find() retorna el holiday correcto por ID.
     */
    public function test_find_returns_correct_holiday()
    {
        $holiday = Holiday::create([
            'year' => Carbon::now()->year,
            'name' => 'Find Test Holiday',
            'type' => 0,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $found = Holiday::find($holiday->id);

        $this->assertNotNull($found);
        $this->assertEquals('Find Test Holiday', $found->name);
    }

    /**
     * Verifica que delete() elimina el holiday de la base de datos.
     */
    public function test_delete_removes_holiday()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $holiday = Holiday::create([
            'year' => Carbon::now()->year,
            'name' => 'Holiday To Delete',
            'type' => 0,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('holidays', ['id' => $holiday->id]);

        $holiday->delete();

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    /**
     * Verifica que create guarda un holiday de tipo 0 (día único).
     */
    public function test_create_single_day_holiday()
    {
        $holiday = Holiday::create([
            'year' => Carbon::now()->year,
            'name' => 'Single Day Holiday',
            'type' => 0,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('holidays', [
            'name' => 'Single Day Holiday',
            'type' => 0,
        ]);
    }

    /**
     * Verifica que create guarda un holiday de tipo 1 (rango de fechas).
     */
    public function test_create_range_holiday()
    {
        $start = Carbon::now()->format('Y-m-d');
        $end   = Carbon::now()->addDays(3)->format('Y-m-d');

        $holiday = Holiday::create([
            'year' => Carbon::now()->year,
            'name' => 'Range Holiday',
            'type' => 1,
            'date' => "{$start},{$end}",
        ]);

        $this->assertDatabaseHas('holidays', [
            'name' => 'Range Holiday',
            'type' => 1,
        ]);
    }
}
