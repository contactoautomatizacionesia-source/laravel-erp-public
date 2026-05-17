<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendReferralInvitationToEmailTemplateTypesAndNotificationSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            // Cambiamos VARCHAR(191) a TEXT para soportar mensajes largos y JSON
            $table->text('message')->nullable()->change();
        });

        DB::table('email_template_types')->updateOrInsert([
            'type'       => 'referral_invitation_template',
            'module'     => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification_settings')->updateOrInsert([
            'event'                => json_encode([
                'en' => 'Send Referral Invitation',
                'es' => 'Enviar invitación de referido',
            ], JSON_UNESCAPED_UNICODE),
            'slug'                 => 'referral-invitation',
            'delivery_process_id'  => null,
            'type'                 => 'email,system',
            'message'              => json_encode([
                'en' => "Hi! I'm {CUSTOMER_NAME} 👋 I'm sharing my link so you can sign up for Lifehuni. When you join through this link, you'll be placed in my group, and we'll both receive benefits for your registration. Let me know if you have any questions — I'll be happy to help you through the process! 😊",
                'es' => "¡Hola!, Soy {CUSTOMER_NAME} 👋 Te paso mi link para que te registres en Lifehuni. Al entrar con este enlace, quedarás en mi grupo y así ambos ganamos beneficios por el registro. ¡Avísame si tienes dudas para ayudarte con el proceso! 😊",
            ], JSON_UNESCAPED_UNICODE),
            'admin_msg'            => json_encode([
                'en' => "The user {CUSTOMER_NAME} has shared their referral link.",
                'es' => "El usuario {CUSTOMER_NAME} ha compartido su enlace de referido.",
            ], JSON_UNESCAPED_UNICODE),
            'user_access_status'   => 0,
            'seller_access_status' => 0,
            'admin_access_status'  => 1,
            'staff_access_status'  => 1,
            'module'               => null,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            // Revertimos a VARCHAR(191) en caso de rollback
            $table->string('message', 191)->nullable()->change();
        });

        DB::table('email_template_types')->where('type', 'referral_invitation_template')->delete();
        DB::table('notification_settings')->where('slug', 'referral-invitation')->delete();
    }
}
