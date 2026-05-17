<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitClubPointToOrderProductDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('order_product_details', function (Blueprint $table) {
            $table->unsignedInteger('unit_club_point')->default(0)->after('tax_amount');
        });
    }

    public function down()
    {
        Schema::table('order_product_details', function (Blueprint $table) {
            $table->dropColumn('unit_club_point');
        });
    }
}
