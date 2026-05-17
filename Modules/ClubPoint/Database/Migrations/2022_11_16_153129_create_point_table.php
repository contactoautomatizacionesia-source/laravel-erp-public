<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('products')){
            Schema::table('products', function (Blueprint $table) {
                $table->string('club_point')->default('0')->after('status');
                $table->string('club_point_type')->default('0')->after('club_point');
            });
        } 
        if(Schema::hasTable('orders')){
            Schema::table('orders', function (Blueprint $table) {
                $table->string('club_point')->default('0')->after('order_status');
                $table->string('point_convert')->default('0')->after('club_point');
            });
        } 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('club_point');
            $table->dropColumn('club_point_type');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('club_point');
            $table->dropColumn('point_convert');
        });
    }
}
