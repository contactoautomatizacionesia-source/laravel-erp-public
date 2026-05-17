<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryCountSettingsTable extends Migration
{
    private const ON_DELETE_SET_NULL = 'set null';

    public function up()
    {
        Schema::create('inventory_count_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cost_center_id')->unique();
            $table->unsignedBigInteger('count_role_id')->nullable();
            $table->unsignedTinyInteger('max_attempts')->default(0)->comment('0 = sin límite');
            $table->boolean('allow_history_view')->default(false);
            $table->json('notify_user_ids')->nullable()->comment('IDs de admins a notificar');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->onDelete('cascade');
            $table->foreign('count_role_id')->references('id')->on('roles')->onDelete(self::ON_DELETE_SET_NULL);
            $table->foreign('created_by')->references('id')->on('users')->onDelete(self::ON_DELETE_SET_NULL);
            $table->foreign('updated_by')->references('id')->on('users')->onDelete(self::ON_DELETE_SET_NULL);
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_count_settings');
    }
}
