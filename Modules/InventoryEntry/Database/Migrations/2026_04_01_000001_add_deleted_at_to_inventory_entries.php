<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToInventoryEntries extends Migration
{
    public function up()
    {
        Schema::table('product_inventory_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('product_inventory_entries', 'deleted_at')) {
                $table->softDeletes();
                $table->index('deleted_at');
            }
        });
    }

    public function down()
    {
        Schema::table('product_inventory_entries', function (Blueprint $table) {
            if (Schema::hasColumn('product_inventory_entries', 'deleted_at')) {
                $table->dropIndex(['deleted_at']);
                $table->dropSoftDeletes();
            }
        });
    }
}
