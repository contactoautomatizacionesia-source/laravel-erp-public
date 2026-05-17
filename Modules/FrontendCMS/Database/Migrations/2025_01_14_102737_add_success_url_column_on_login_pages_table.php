<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSuccessUrlColumnOnLoginPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('login_pages','success_url'))
        {
            Schema::table('login_pages', function (Blueprint $table) {
                $table->string('success_url')->nullable();
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
        if(Schema::hasColumn('login_pages','success_url'))
        {
            Schema::table('login_pages', function (Blueprint $table) {
                $table->dropColumn('success_url');
            });
        }
    }
}
