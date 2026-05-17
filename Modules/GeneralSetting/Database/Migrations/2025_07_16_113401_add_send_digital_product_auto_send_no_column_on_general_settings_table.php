<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSendDigitalProductAutoSendNoColumnOnGeneralSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('general_settings','send_digital_product'))
        {
            Schema::table('general_settings',function($table){
                $table->tinyInteger('send_digital_product')->default(1);
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
        if(Schema::hasColumn('general_settings','send_digital_product'))
        {
            Schema::table('general_settings',function($table){
                $table->dropColumn('send_digital_product');
            });
        }
    }
}
