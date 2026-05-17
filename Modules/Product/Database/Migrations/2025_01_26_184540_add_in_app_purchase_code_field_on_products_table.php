<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInAppPurchaseCodeFieldOnProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('product_sku','in_app_purchase'))
        {
            Schema::table('product_sku', function (Blueprint $table) {
                $table->string('in_app_purchase')->nullable();
            });
        }


        if(!Schema::hasColumn('seller_product_s_k_us','in_app_purchase'))
        {
            Schema::table('seller_product_s_k_us', function (Blueprint $table) {
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
        if(Schema::hasColumn('product_sku','in_app_purchase_code'))
        {
            Schema::table('product_sku', function (Blueprint $table) {
                $table->dropColumn('in_app_purchase');
            });
        }


        if(Schema::hasColumn('seller_product_s_k_us','in_app_purchase_code'))
        {
            Schema::table('seller_product_s_k_us', function (Blueprint $table) {
                $table->dropColumn('in_app_purchase');
            });
        }
    }
}
