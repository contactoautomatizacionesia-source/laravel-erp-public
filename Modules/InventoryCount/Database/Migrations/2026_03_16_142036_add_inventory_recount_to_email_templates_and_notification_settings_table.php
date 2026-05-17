<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInventoryRecountToEmailTemplatesAndNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currentTimeStamp = now();

        DB::table('notification_settings')->insert([
            'event'   => json_encode(['en' => 'Inventory Recount', 'es' => 'Recuento de inventario']),
            'slug'    => 'inventory-recount',
            'type'    => 'system,email',
            'message' => json_encode([
                'en' => '{NOTES}',
                'es' => '{NOTES}'
            ]),
            'admin_msg' => json_encode([
                'en' => 'Administrator: The inventory with code {PRODUCT_NAME} requires a count. Notes: {NOTES}',
                'es' => 'Administrador: El inventario con código {PRODUCT_NAME} requiere un recuento. Notas: {NOTES}'
            ]),
            'user_access_status'   => 0,
            'seller_access_status' => 0,
            'admin_access_status'  => 1,
            'staff_access_status'  => 1,
            'module'               => null,
            'created_at'           => $currentTimeStamp,
            'updated_at'           => $currentTimeStamp,
        ]);

        DB::table('email_template_types')->insert([
            'type'       => 'inventory_recount_template',
            'module'     => null,
            'created_at' => $currentTimeStamp,
            'updated_at' => $currentTimeStamp
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('notification_settings')
            ->where('slug', 'inventory-recount')
            ->delete();

        DB::table('email_template_types')
            ->where('type', 'inventory_recount_template')
            ->delete();
    }
}
