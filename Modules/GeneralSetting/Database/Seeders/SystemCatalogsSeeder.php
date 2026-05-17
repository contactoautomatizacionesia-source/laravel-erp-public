<?php

namespace Modules\GeneralSetting\Database\Seeders;

use App\Seeders\Contracts\DeployableSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\GeneralSetting\Entities\Catalogs\Gender;
use Modules\GeneralSetting\Entities\Catalogs\LeadSource;
use Modules\GeneralSetting\Entities\Catalogs\Profession;
use Modules\GeneralSetting\Entities\Catalogs\CivilStatus;
use Illuminate\Support\Facades\DB;
use Modules\GeneralSetting\Entities\Catalogs\Bank;
use Modules\GeneralSetting\Entities\Catalogs\BankAccountType;
use Modules\GeneralSetting\Entities\Catalogs\ContractType;
use Modules\GeneralSetting\Entities\Catalogs\CountryPhoneCode;
use Modules\GeneralSetting\Entities\Catalogs\PaymentForm;
use Modules\GeneralSetting\Entities\Catalogs\CostCenterMovementType;
use Modules\GeneralSetting\Entities\Catalogs\InventoryOutReason;
use Modules\GeneralSetting\Entities\Catalogs\Novelty;
use Modules\GeneralSetting\Entities\Catalogs\CashDiscrepancyType;

class SystemCatalogsSeeder extends Seeder implements DeployableSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void // NOSONAR
    {
        Model::unguard();

        // 1. GENDERS (Sexo)
        $genders = [
            ['es' => 'Mujer',  'en' => 'Female', 'code' => 'F'],
            ['es' => 'Hombre', 'en' => 'Male',   'code' => 'M'],
        ];

        foreach ($genders as $index => $gender) {
            Gender::firstOrCreate(
                ['code' => $gender['code']], // Evita duplicados si se corre 2 veces
                [
                    'name' => ['es' => $gender['es'], 'en' => $gender['en']],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }

        // 2. LEAD SOURCES (Clasificación / Cómo nos conoció)
        // Extraído de tu select: emp_clasificacion2_id
        $leads = [
            1 => "WWW.LIFEHUNI.COM",
            2 => "FACEBOOK",
            3 => "INSTAGRAM",
            4 => "TWITTER",
            5 => "EMPRESARIO",
            6 => "PUBLICIDAD IMPRESA",
            7 => "TESTIMONIO DE UN CONOCIDO",
            8 => "RADIO"
        ];

        foreach ($leads as $order => $name) {
            LeadSource::firstOrCreate(
                // Usamos el nombre como identificador único ya que no hay códigos externos
                ['name->es' => $name],
                [
                    'name' => ['es' => $name, 'en' => $name], // Mismo valor para EN por defecto
                    'sort_order' => $order,
                    'is_active' => true
                ]
            );
        }

        // 3. PROFESSIONS (Profesiones)
        // Extraído de tu select: emp_clasificacion1_id
        // Nota: He mantenido el orden alfabético para el sort_order
        $professions = [
            "ABOGADA FISCAL", "ABOGADO (A)", "ACTRIZ", "ADMINISTRADOR (A)", "AEROMECANICO",
            "AGENTE DE BIENES RAICES", "AGRICULTOR (A)", "AMA DE CASA", "ANALISTA", "ARQUITECTO (A)",
            "ARTESANA", "ASEADORA", "ASESOR (A) COMERCIAL", "ASESOR (A) DE IMAGEN", "ASESOR (A) DE SEGUROS",
            "ASESOR (A) DE VENTAS", "ASESOR (A) EN RIESGOS PROFESIONALES", "ASISTENTE", "ASISTENTE ADMINISTRATIVO",
            "AUXILIA R DE LABORATORIO", "AUXILIAR CONTABLE", "AUXILIAR DE CITAS", "AUXILIAR DE DISEÑO",
            "AUXILIAR DE ENFERMERIA", "AUXILIAR DE ODONTOLOGIA", "AUXILIAR DE OPTOMETRÍA", "AUXILIAR DE VUELO",
            "AUXILIAR SGSST", "AZAFATA", "BACTERIOLOGO (A)", "BIBLIOTECOLOGO (A)", "BIOCOSMETOLOGO (A)",
            "BIOLOGA", "CAJERO (A)", "CHEFF", "CIRUJANO (A)", "COBRADOR", "COMERCIANTE", "COMERCIO EXTERIOR",
            "COMUNICADORA SOCIAL", "CONDUCTOR (A)", "CONFECCIONISTA", "CONFERENCISTA", "CONSULTOR DE NEGOCIOS INTERNACIONALES",
            "CONTADOR (A)", "CONTRATISTA CONSTRUCCION", "CONTROL PÚBLICO", "CONTROLADOR DE TRÁNSITO AÉREO.",
            "COORDINADORA DE PRODUCCION", "COSMETOLOGA", "COSMIATRA", "CUIDADORA DE ADULTOS MAYORES",
            "DELINEANTE DE ARQUITECTURA", "DESEMPLEADO", "DIFUSOR", "DISEÑADOR (A)", "DOCENTE", "ECÓLOGO (A)",
            "ECONOMISTA", "EDUCADOR (A) FAMILIAR", "EJECUTIVO (A) DE VENTAS", "EMPLEADO", "EMPRESARIO (A)",
            "ENFERMERA", "ENFERMERO", "ENTRENADOR CLÍNICO", "ESTETICISTA FACIAL Y CORPORAL", "ESTETICISTA Y COSMETÓLOGA",
            "ESTILISTAS", "ESTUDIANTE", "FARMACEUTA", "FINANZAS Y COMERCIO EXTERIOR", "FISIOTERAPEUTA",
            "FONOAUDIOLOGO (A)", "FOTOGRAFO (A)", "GERENCIA RIESGO", "GERONTOLOGA", "GESTORA EMPRESARIAL",
            "GUARDA", "GUIA DE TURISMO", "HIGIENISTA ORAL", "HOGAR", "IMPULSADORA", "INDEPENDIENTE",
            "INGENÍERA ELECTRÓNICA", "INGENIERO AMBIENTAL", "INGENIERO CIVIL", "INGENIERO DE PETROLEOS",
            "INGENIERO DE SISTEMAS", "INGENIERO ELECTRICISTA", "INGENIERO QUIMICO", "INGENIERO X",
            "INSTRUCTORA", "INSTRUMENTADOR (A) QUIRURGICO", "IRRIDIOLOGO", "LICENCIADO (A)", "MANICURISTA",
            "MARKETING Y NEGOCIOS INTERNACIONALES", "MARKETING Y PUBLICIDAD", "MASAJISTA", "MECANICO",
            "MEDICO", "MENSAJERO", "MERCADEO", "MERCADERISTA", "MERCADÓLOGA", "MICROBILOGO (A)", "MILITAR",
            "MINERO", "MODISTA", "MUSICO", "NATURISTA", "NEGOCIOS INTERNACIONALES", "NUTRICIONISTA",
            "ODONTOLOGO (A)", "OFICIOS VARIOS", "OPERADOR (A) LOGISTICA", "OPERADOR (A) TURISTICO",
            "OPERARIA", "OPTOMETRA", "ORGANIZADOR DE EVENTOS", "ORIENTADOR DE EXPANSIÓN", "ORTOPEDISTA",
            "PANADERO (A)", "PARAMEDICO", "PASONIVELISTA", "PASTELERO (A)", "PASTOR", "PENSIONADO (A)",
            "PERIODISTA", "PILOTO", "PINTORA", "POLICIA", "PROFESOR (A)", "PROMOTOR (A) AMBIENTAL",
            "PROMOTOR (A) COMERCIAL", "PROMOTOR (A) DE SALUD", "PROMOTORA DE TURISMO", "PSICOLOGO (A)",
            "PSIQUIATRA", "PUBLICISTA", "QUIMICA FARMACEUTICA", "QUIMICO", "RADIOLOGO", "RECEPCIONISTA",
            "REFLEXOLOGA", "REGENTE FARMACIA", "RELACIONISTA PUBLICO (A)", "RELOJERO", "SALUD OCUPACIONAL",
            "SECRETARIA", "SERVIDORA PUBLICA", "SOLDADO PROFESIONAL", "SUPERVISOR", "TECNICO", "TECNICO DE SONIDO",
            "TECNICO ELECTRONICO", "TECNICO EN ADMINISTRACION DE EMPRESAS", "TECNICO EN SISTEMAS",
            "TECNICO QUIMICO", "TECNÓLOGO EN CONTABILIDAD Y FINANZAS", "TECNÓLOGO INDUSTRIAL",
            "TEGNOLOGO (A)  AGROINDUSTRIAL", "TEGNOLOGO (A) DE ALIMENTOS", "TEGNOLOGO (A) EN GESTION EMPRESARIAL",
            "TEGNOLOGO (A) EN PUBLICIDAD Y MERCADEO", "TEGNOLOGO (A) EN SISTEMAS", "TEGNOLOGO (A) FORESTAL",
            "TERAPEUTA", "TOPOGRAFO (A)", "TRABAJADORA SOCIAL", "TRANSFERENCISTA", "TRANSPORTADOR",
            "VENDEDOR DE SERVICOS", "VENDEDORA EXTERNA", "VENTAS", "VETERINARIO (A)", "VISITADOR (A) MEDICO",
            "ZOOTECNISTA"
        ];

        foreach ($professions as $index => $profName) {
            // Normalizamos texto (Capitalizar primera letra de cada palabra)
            $formattedName = mb_convert_case(mb_strtolower($profName), MB_CASE_TITLE, "UTF-8");

            // Verificamos si ya existe para no duplicar en el array
            if (!Profession::where('name->es', $formattedName)->exists()) {
                Profession::create([
                    'name' => ['es' => $formattedName, 'en' => $formattedName],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]);
            }
        }

        // 4. CIVIL STATUS (Estado Civil)
        $civilStatuses = [
            'Soltero(a)',
            'Casado(a)',
            'Unión libre',
            'Divorciado(a)',
            'Viudo(a)',
            'Separado(a)',
        ];

        foreach ($civilStatuses as $index => $status) {
            CivilStatus::firstOrCreate(
                ['name->es' => $status],
                [
                    'name' => ['es' => $status, 'en' => $status],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }

        // ==========================================
        // 5. BANKS (Bancos)
        // ==========================================
        $banks = [
            'Bancolombia',
            'Daviplata',
            'Banco Davivienda'
        ];

        foreach ($banks as $index => $bankName) {
            Bank::firstOrCreate(
                ['name->es' => $bankName],
                [
                    'name' => ['es' => $bankName, 'en' => $bankName],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }

        // ==========================================
        // 6. BANK ACCOUNT TYPES (Tipos de Cuenta Bancaria)
        // ==========================================
        $accountTypes = [
            'Ahorros',
            'Corriente'
        ];

        foreach ($accountTypes as $index => $type) {
            BankAccountType::firstOrCreate(
                ['name->es' => $type],
                [
                    'name' => ['es' => $type, 'en' => $type],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }

        // ==========================================
        // 7. CONTRACT TYPES (Tipos de Contrato)
        // ==========================================
        // Según tu sistema anterior, el único activo es "EMPRESARIO"
        $contractTypes = [
            'Empresario'
        ];

        foreach ($contractTypes as $index => $type) {
            ContractType::firstOrCreate(
                ['name->es' => $type],
                [
                    'name' => ['es' => $type, 'en' => $type],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }


        // ==========================================
        // 8. COUNTRY PHONE CODES (Indicativos Telefónicos)
        // ==========================================
        $phoneCodes = [
            '+57', // Colombia
            '+1',  // USA/Canada
            '+34', // España
            // Agrega aquí los que necesites
        ];

        foreach ($phoneCodes as $index => $code) {
            CountryPhoneCode::firstOrCreate(
                // Buscamos por el nombre en español para evitar duplicados
                ['name->es' => $code],
                [
                    // Al crearlo, llenamos ambos idiomas y el orden
                    'name' => ['es' => $code, 'en' => $code],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }

        // ==========================================
        // 9. PAYMENT FORMS (Formas de Pago)
        // ==========================================
        $paymentForms = [
            'Efectivo',
            'Tarjeta de Crédito',
            'Tarjeta de Débito',
            'Transferencia Bancaria',
            'Consignación Bancaria',
            'Credilife',
            'Promoción',
            'Ventas Web',
            'Crédito Interno',
        ];

        foreach ($paymentForms as $index => $form) {
            PaymentForm::firstOrCreate(
                ['name->es' => $form],
                [
                    'name' => ['es' => $form, 'en' => $form],
                    'sort_order' => $index + 1,
                    'is_active' => true
                ]
            );
        }

        // ==========================================
        // 10. COST CENTER MOVEMENT TYPES (Tipos de Movimiento de Centro de Costo)
        // ==========================================
        $movementTypes = [
            ['code' => 'transfer_in',       'name' => 'Entrada por Transferencia',          'en' => 'Transfer In'],
            ['code' => 'transfer_out',      'name' => 'Salida por Devolución',               'en' => 'Transfer Out'],
            ['code' => 'initial_stock',     'name' => 'Reabastecimiento Inicial',            'en' => 'Initial Stock'],
            ['code' => 'adjustment_in',     'name' => 'Ajuste Entrada (Pérdida Encontrada)', 'en' => 'Adjustment In'],
            ['code' => 'adjustment_out',    'name' => 'Ajuste Salida (Daño/Merma)',          'en' => 'Adjustment Out'],
            ['code' => 'weekly_order',      'name' => 'Pedido Semanal',                      'en' => 'Weekly Order'],
            ['code' => 'urgent_transfer',   'name' => 'Traslado Urgente',                    'en' => 'Urgent Transfer'],
            ['code' => 'return_to_main',    'name' => 'Devolución a Bodega Principal',       'en' => 'Return to Main'],
            ['code' => 'internal_transfer', 'name' => 'Traslado entre Centros',              'en' => 'Internal Transfer'],
            ['code' => 'cart_sale',         'name' => 'Venta por Carrito',                   'en' => 'Cart Sale'],
        ];

        foreach ($movementTypes as $index => $type) {
            CostCenterMovementType::updateOrCreate(
                ['name->es' => $type['name']],
                [
                    'code'       => $type['code'],
                    'name'       => ['es' => $type['name'], 'en' => $type['en']],
                    'sort_order' => $index + 1,
                    'is_active'  => true,
                ]
            );
        }

        // Tipos de salida configurables por el usuario (is_system = false = editables/eliminables)
        // Tipos del sistema (is_system = true) son fijos y no se pueden eliminar
        $reasons = [
            ['code' => 'sale',              'es' => 'Venta',              'en' => 'Sale',                'is_system' => false],
            ['code' => 'supplier_return',   'es' => 'Devolución a proveedor', 'en' => 'Return to Supplier', 'is_system' => false],
            ['code' => 'donation',          'es' => 'Donación',           'en' => 'Donation',            'is_system' => false],
            ['code' => 'internal_use',      'es' => 'Consumo interno',    'en' => 'Internal Use',        'is_system' => false],
            ['code' => 'loss',              'es' => 'Pérdida',            'en' => 'Loss',                'is_system' => false],
            ['code' => 'transit',           'es' => 'Tránsito',           'en' => 'Transit',             'is_system' => false],
            ['code' => 'courtesy',          'es' => 'Cortesía',           'en' => 'Courtesy',            'is_system' => false],
            ['code' => 'loan',              'es' => 'Préstamo',           'en' => 'Loan',                'is_system' => false],
            // Tipo reservado para ventas del carrito — no editable ni eliminable
            ['code' => 'cart_sale',         'es' => 'Venta por carrito',  'en' => 'Cart Sale',           'is_system' => true],
        ];

        foreach ($reasons as $index => $reason) {
            InventoryOutReason::updateOrCreate(
                ['code' => $reason['code']],
                [
                    'name'       => ['es' => $reason['es'], 'en' => $reason['en']],
                    'sort_order' => $index + 1,
                    'is_active'  => true,
                    'meta'       => ['is_system' => $reason['is_system']],
                ]
            );
        }


        $novelties = [
            ['code' => 'sinister',       'name' => 'Siniestro',      'is_system' => false, 'es' => 'Siniestro',          'en' => 'sinister'],
            ['code' => 'robbery',        'name' => 'Robo',           'is_system' => false, 'es' => 'Robo',               'en' => 'Robbery'],
            ['code' => 'damaged_package','name' => 'Paquete dañado', 'is_system' => false, 'es' => 'Paquete dañado',     'en' => 'Damaged package'],
            ['code' => 'broken',         'name' => 'Averiado',       'is_system' => false, 'es' => 'Averiado',           'en' => 'Broken'],
        ];

        foreach ($novelties as $index => $novelty) {
            Novelty::updateOrCreate(
                ['code' => $novelty['code']],
                [
                    'name'       => ['es' => $novelty['es'], 'en' => $novelty['en']],
                    'sort_order' => $index + 1,
                    'is_active'  => true,
                    'meta'       => ['is_system' => $novelty['is_system']],
                ]
            );
        }

        // ==========================================
        // 12. CASH DISCREPANCY TYPES (Tipos de Novedad de Caja)
        // ==========================================
        $discrepancyTypes = [
            ['code' => 'shortage',      'es' => 'Faltante',          'en' => 'Shortage'],
            ['code' => 'surplus',       'es' => 'Sobrante',          'en' => 'Surplus'],
            ['code' => 'counterfeit',   'es' => 'Billete falso',     'en' => 'Counterfeit bill'],
            ['code' => 'theft_report',  'es' => 'Reporte de hurto',  'en' => 'Theft report'],
            ['code' => 'system_error',  'es' => 'Error de sistema',  'en' => 'System error'],
            ['code' => 'other',         'es' => 'Otro',              'en' => 'Other'],
        ];

        foreach ($discrepancyTypes as $index => $type) {
            CashDiscrepancyType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name'       => ['es' => $type['es'], 'en' => $type['en']],
                    'sort_order' => $index + 1,
                    'is_active'  => true,
                ]
            );
        }
    }
}
