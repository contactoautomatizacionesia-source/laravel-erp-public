<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixUnitTypesNestedJsonName extends Migration
{
    public function up()
    {
        $units = DB::table('unit_types')->get();

        foreach ($units as $unit) {
            $decoded = json_decode($unit->name, true);

            if (!is_array($decoded)) {
                continue;
            }

            $changed = false;
            foreach ($decoded as $lang => $value) {
                if (is_string($value) && str_starts_with($value, '{')) {
                    $inner = json_decode($value, true);
                    if (is_array($inner)) {
                        $decoded[$lang] = $inner[$lang] ?? reset($inner);
                        $changed = true;
                    }
                }
            }

            if ($changed) {
                DB::table('unit_types')
                    ->where('id', $unit->id)
                    ->update(['name' => json_encode($decoded)]);
            }
        }
    }

    public function down()
    {
        // No reversible — data cleanup
    }
}
