<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRegisterPolicyToGeneralSettings extends Migration
{
    public function up()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->text('register_policy')->nullable()->after('invoice_footer_quote');
        });

        DB::table('general_settings')->where('id', 1)->update([
            'register_policy' => json_encode([
                'es' => [
                    'items' => [
                        'CERTIFICO QUE LA INFORMACIÓN SUMINISTRADA ES VERÍDICA Y AUTORIZO A LA EMPRESA PARA QUE LA VERIFIQUE SEGÚN LA NECESIDAD.',
                        'ESTOY INFORMADO DE MI OBLIGACIÓN DE ACTUALIZAR ANUALMENTE LA INFORMACIÓN; O CADA VEZ QUE LA EMPRESA LO SOLICITE POR CADA PRODUCTO O SERVICIO QUE UTILICE.',
                        'AUTORIZO A LA EMPRESA PARA QUE CONSULTE Y REPORTE INFORMACIÓN A LAS DIFERENTES CENTRALES DE RIESGO.',
                        'DECLARO QUE MIS INGRESOS Y BIENES PROVIENEN DEL DESARROLLO DE MI ACTIVIDAD ECONÓMICA PRINCIPAL.',
                        'DECLARO QUE EL ORIGEN DE LOS RECURSOS Y DEMÁS ACTIVOS, PROCEDEN DEL GIRO ORDINARIO DE ACTIVIDADES LICITAS.',
                        'AUTORIZO A LA EMPRESA PARA QUE SEA LA RESPONSABLE DEL TRATAMIENTO DE MIS DATOS PERSONALES, LOS CUALES SE RECOLECTAN Y RECOLECTARAN, EN EL CUMPLIMIENTO DE LAS DISPOSICIONES DE LA LEY 1581 DEL 2012.',
                        'DECLARO HABER LEÍDO Y ACEPTADO EL MANUAL DE EMPRESARIOS LIFEHUNI.',
                    ],
                    'footnote' => 'LA ORGANIZACIÓN PODRÁ USAR LOS MECANISMOS ELECTRÓNICOS ALTERNATIVOS QUE GARANTICEN LA VERIFICACIÓN Y AUTENTICACIÓN DE LA IDENTIDAD DE ACUERDO A LO SEÑALADO EN LA LEY 527 DE 1999.',
                ],
                'en' => [
                    'items' => [
                        'I CERTIFY THAT THE INFORMATION PROVIDED IS TRUTHFUL AND I AUTHORIZE THE COMPANY TO VERIFY IT AS NEEDED.',
                        'I AM INFORMED OF MY OBLIGATION TO ANNUALLY UPDATE THE INFORMATION; OR WHENEVER THE COMPANY REQUESTS IT FOR EACH PRODUCT OR SERVICE I USE.',
                        'I AUTHORIZE THE COMPANY TO CONSULT AND REPORT INFORMATION TO THE DIFFERENT CREDIT BUREAUS.',
                        'I DECLARE THAT MY INCOME AND ASSETS COME FROM THE DEVELOPMENT OF MY MAIN ECONOMIC ACTIVITY.',
                        'I DECLARE THAT THE ORIGIN OF THE RESOURCES AND OTHER ASSETS COMES FROM THE ORDINARY COURSE OF LAWFUL ACTIVITIES.',
                        'I AUTHORIZE THE COMPANY TO BE RESPONSIBLE FOR THE PROCESSING OF MY PERSONAL DATA, WHICH IS AND WILL BE COLLECTED IN COMPLIANCE WITH THE PROVISIONS OF LAW 1581 OF 2012.',
                        'I DECLARE THAT I HAVE READ AND ACCEPTED THE LIFEHUNI BUSINESSPEOPLE MANUAL.',
                    ],
                    'footnote' => 'THE ORGANIZATION MAY USE ALTERNATIVE ELECTRONIC MECHANISMS THAT GUARANTEE THE VERIFICATION AND AUTHENTICATION OF IDENTITY IN ACCORDANCE WITH LAW 527 OF 1999.',
                ],
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function down()
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('register_policy');
        });
    }
}
