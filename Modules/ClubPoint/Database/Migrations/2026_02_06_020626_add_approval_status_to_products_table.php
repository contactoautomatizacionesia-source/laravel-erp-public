<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalStatusToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $column) {
            // 1: Approved, 2: Pending, 3: Rejected
            $column->tinyInteger('approval_status')->default(1)->after('id'); 
            $column->unsignedBigInteger('pending_staff_id')->nullable()->after('approval_status');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $column) {
            $column->dropColumn(['approval_status', 'pending_staff_id']);
        });
    }
}