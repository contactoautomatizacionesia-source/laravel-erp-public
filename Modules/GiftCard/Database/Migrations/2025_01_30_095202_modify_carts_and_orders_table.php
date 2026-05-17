<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCartsAndOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('carts','gift_card_sku'))
        {
            Schema::table('carts',function($table){
                $table->integer('gift_card_sku')->nullable();
            });
        }

        if(!Schema::hasColumn('carts','gift_card_type'))
        {
            Schema::table('carts',function($table){
                $table->integer('gift_card_type')->nullable();
            });
        }

        if(!Schema::hasColumn('order_product_details','gift_card_sku'))
        {
            Schema::table('order_product_details',function($table){
                $table->integer('gift_card_sku')->nullable();
            });
        }

        if(!Schema::hasColumn('order_product_details','gift_card_type'))
        {
            Schema::table('order_product_details',function($table){
                $table->integer('gift_card_type')->nullable();
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
        if(Schema::hasColumn('carts','gift_card_sku'))
        {
            Schema::table('carts',function($table){
                $table->integer('gift_card_sku');
            });
        }

        if(Schema::hasColumn('carts','gift_card_type'))
        {
            Schema::table('carts',function($table){
                $table->integer('gift_card_type');
            });
        }

        if(Schema::hasColumn('order_product_details','gift_card_sku'))
        {
            Schema::table('order_product_details',function($table){
                $table->integer('gift_card_sku');
            });
        }

        if(Schema::hasColumn('order_product_details','gift_card_type'))
        {
            Schema::table('order_product_details',function($table){
                $table->dropColumn('gift_card_type');
            });
        }
    }
}
