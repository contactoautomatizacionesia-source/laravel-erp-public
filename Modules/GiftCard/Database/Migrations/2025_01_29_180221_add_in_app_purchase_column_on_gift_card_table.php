<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInAppPurchaseColumnOnGiftCardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       if(!Schema::hasColumn('add_gift_cards','in_app_purchase'))
       {
            Schema::table('add_gift_cards',function($table){
                $table->string('in_app_purchase')->nullable();
            });
       }

       if(!Schema::hasColumn('gift_cards','in_app_purchase'))
       {
            Schema::table('gift_cards',function($table){
                $table->string('in_app_purchase')->nullable();
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
        if(Schema::hasColumn('add_gift_cards','in_app_purchase'))
        {
            Schema::table('add_gift_cards',function($table){
                $table->dropColumn('in_app_purchase');
            });
        }

        if(Schema::hasColumn('gift_cards','in_app_purchase'))
        {
            Schema::table('gift_cards',function($table){
                $table->dropColumn('in_app_purchase');
            });
        }
    }
}
