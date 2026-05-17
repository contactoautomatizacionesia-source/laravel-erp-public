<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixDoubleApprovalParentId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $clubPointMenu = DB::table('backendmenus')->where('name', 'clubpoint.club_point')->first();

        if ($clubPointMenu) {
            DB::table('backendmenus')
                ->where('name', 'common.double_approval')
                ->update(['parent_id' => $clubPointMenu->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('backendmenus')
            ->where('name', 'common.double_approval')
            ->update(['parent_id' => 53]);
    }
}
