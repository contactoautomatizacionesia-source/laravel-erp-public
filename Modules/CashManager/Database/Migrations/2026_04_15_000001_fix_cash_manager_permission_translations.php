<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixCashManagerPermissionTranslations extends Migration
{
    public function up()
    {
        $map = [
            'cash_manager.cash_management'  => 'cashmanager::cash_manager.cash_management',
            'cash_manager.operations'       => 'cashmanager::cash_manager.operations',
            'cash_manager.assignments'      => 'cashmanager::cash_manager.assignments',
            'cash_manager.settings'         => 'cashmanager::cash_manager.settings',
            'cash_manager.view_operations'  => 'cashmanager::cash_manager.view_operations',
            'cash_manager.manage_assignments' => 'cashmanager::cash_manager.manage_assignments',
            'cash_manager.admin_settings'   => 'cashmanager::cash_manager.admin_settings',
        ];

        foreach ($map as $old => $new) {
            DB::table('permissions')
                ->where('translation', $old)
                ->update(['translation' => $new]);
        }
    }

    public function down()
    {
        $map = [
            'cashmanager::cash_manager.cash_management'   => 'cash_manager.cash_management',
            'cashmanager::cash_manager.operations'        => 'cash_manager.operations',
            'cashmanager::cash_manager.assignments'       => 'cash_manager.assignments',
            'cashmanager::cash_manager.settings'          => 'cash_manager.settings',
            'cashmanager::cash_manager.view_operations'   => 'cash_manager.view_operations',
            'cashmanager::cash_manager.manage_assignments' => 'cash_manager.manage_assignments',
            'cashmanager::cash_manager.admin_settings'    => 'cash_manager.admin_settings',
        ];

        foreach ($map as $old => $new) {
            DB::table('permissions')
                ->where('translation', $old)
                ->update(['translation' => $new]);
        }
    }
}
