<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPdfFooterFieldsToGeneralSettings extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            // Añadimos los campos para el footer del PDF
            $table->text('invoice_footer_text')->nullable();
            $table->text('invoice_footer_quote')->nullable();
        });

        // Insertamos o actualizamos los datos en el registro principal (ID 1 por defecto)
        DB::table('general_settings')->where('id', 1)->update([
            'invoice_footer_text' => json_encode([
                'en' => 'Beneficiary of the Special Economic and Social Zone. No withholding tax shall be applied in accordance with Article 1.2.1.23.2.6 of Decree 2112 of November 2019.',
                'es' => 'Beneficiario de la Zona Económica Especial y Social Especial. No realizar Retención en la Fuente según artículo 1.2.1.23.2.6 del Decreto 2112 de noviembre de 2019.'
            ], JSON_UNESCAPED_UNICODE),
            'invoice_footer_quote' => json_encode([
                'en' => "A man's stature is not measured in times of comfort, but in times of change and controversy. - Mart",
                'es' => 'Un hombre no mide su altura en los momentos de confort sino en los de cambio y controversia. - Mart'
            ], JSON_UNESCAPED_UNICODE)
        ]);
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['invoice_footer_text', 'invoice_footer_quote']);
        });
    }
}
