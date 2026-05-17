<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find the Customer module
        $customerModule = DB::table('modules')->where('name', 'Customer')->first();
        $moduleId = $customerModule ? $customerModule->id : 5; // Default to 5 if not found

        // Find the customer parent permission
        $customerParent = DB::table('permissions')
            ->where('route', 'cusotmer.list_active')
            ->first();
        $parentId = $customerParent ? $customerParent->parent_id : 0;

        // Get max permission ID
        $maxId = DB::table('permissions')->orderBy('id', 'DESC')->first();
        $newId = $maxId ? $maxId->id + 1 : 1;

        // Insert file explorer permission
        DB::table('permissions')->insert([
            "id" => $newId,
            "module_id" => $moduleId,
            "parent_id" => $parentId,
            "name" => "File Explorer",
            "translation_key" => "file_explorer",
            "route" => 'admin.file-explorer.index',
            "type" => 2,
            "created_by" => 1,
            "updated_by" => 1,
            "status" => 1
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->where('route', 'admin.file-explorer.index')->delete();
    }
};
