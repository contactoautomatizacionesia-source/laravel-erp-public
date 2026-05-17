<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDigitalFolderMenu extends Migration
{
    public function up()
    {
        $parent = DB::table('backendmenus')
            ->where('name', 'common.system')
            ->first();

        $parentId = $parent ? $parent->id : null;

        DB::table('backendmenus')->insert([
            [
                'name' => 'general_settings.digital_folder',
                'route' => 'digital_folder.index',
                'parent_id' => $parentId,
                'icon' => 'fas fa-folder-open',
                'position' => 10,
                'is_admin' => 1,
                'is_seller' => 0,
                'module' => 'DigitalFolder',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        DB::table('backendmenus')
            ->where('module', 'DigitalFolder')
            ->delete();
    }
}
