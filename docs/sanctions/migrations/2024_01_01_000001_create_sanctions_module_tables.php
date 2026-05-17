<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Módulo de Reglamento y Sanciones - Lifehuni
 *
 * Crea todas las tablas del módulo disciplinario.
 * Orden de creación respeta dependencias de FK:
 *   1. Tablas paramétricas (sin dependencias entre sí)
 *   2. Tablas transaccionales (dependen de paramétricas y de users)
 *
 * Referencia: Manual del Empresario, secciones 2.17 a 2.19
 */
return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------------------------------
        // TIPOS ENUM
        // Se definen como strings con validación en la aplicación.
        // En PostgreSQL se pueden usar como tipos nativos; aquí usamos
        // string con comentario para compatibilidad MySQL/PostgreSQL.
        // ----------------------------------------------------------------

        // ----------------------------------------------------------------
        // ZONA 1: TABLAS PARAMÉTRICAS
        // Catálogos configurables. No tienen dependencias entre sí.
        // ----------------------------------------------------------------

        /**
         * Clasifica la gravedad de la infracción cometida por el EUI.
         * Valores de code: MINOR | MODERATE | SEVERE
         * Referencia: sec. 2.17.1 a 2.17.3
         */
        Schema::create('cat_offense_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique()
                ->comment('Código de severidad: MINOR | MODERATE | SEVERE');
            $table->string('name', 100)
                ->comment('Nombre descriptivo de la falta');
            $table->text('description')->nullable()
                ->comment('Conductas que generan esta falta según el manual');
            $table->unsignedTinyInteger('level')
                ->comment('Nivel numérico: 1=Minor | 2=Moderate | 3=Severe');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE si el tipo fue descontinuado');
            $table->timestamps();
        });

        /**
         * Define las sanciones aplicables según reincidencia.
         * Valores de code: WRITTEN_WARNING | SUSPENSION_* | CONTRACT_TERMINATION
         * Referencia: sec. 2.17.1 a 2.17.3
         */
        Schema::create('cat_sanction_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique()
                ->comment('Código de la sanción: WRITTEN_WARNING | SUSPENSION_1_5_DAYS | etc.');
            $table->string('name', 150)
                ->comment('Nombre legible de la sanción');
            $table->text('description')->nullable()
                ->comment('Alcance y efectos generales de la sanción');
            $table->text('first_offense_text')->nullable()
                ->comment('Consecuencia en la primera reincidencia');
            $table->text('second_offense_text')->nullable()
                ->comment('Consecuencia en la segunda reincidencia');
            $table->text('third_offense_text')->nullable()
                ->comment('Consecuencia en la tercera reincidencia');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE si la sanción ya no está disponible');
            $table->timestamps();
        });

        /**
         * Acciones concretas que ejecuta Lifehuni como consecuencia de la sanción.
         * Valores de code: FREEZE_EARNINGS | BLOCK_ORDERS | SUSPEND_MULTILEVEL | etc.
         * Referencia: sec. 2.18
         */
        Schema::create('cat_action_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique()
                ->comment('Código de la acción: FREEZE_EARNINGS | BLOCK_ORDERS | TERMINATE_CONTRACT | etc.');
            $table->string('name', 150)
                ->comment('Nombre legible de la acción disciplinaria');
            $table->text('description')->nullable()
                ->comment('Impacto operativo de aplicar esta acción al EUI');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE si la acción fue descontinuada');
            $table->timestamps();
        });

        /**
         * Origen que da inicio a la investigación.
         * Valores de code: OWN_INITIATIVE | THIRD_PARTY | NEWS | DATA_MESSAGE | OTHER
         * Referencia: sec. 2.17 párrafo inicial
         */
        Schema::create('cat_complaint_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique()
                ->comment('Código del origen: OWN_INITIATIVE | THIRD_PARTY | NEWS | DATA_MESSAGE | OTHER');
            $table->string('name', 100)
                ->comment('Nombre descriptivo de la fuente de la denuncia');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE si la fuente ya no está habilitada');
            $table->timestamps();
        });

        /**
         * Circunstancias que pueden reducir la sanción aplicada.
         * Valores de code: ACCEPTS_OFFENSE | COOPERATES | PREVENTED_DAMAGE | NO_MALICE
         * Referencia: sec. 2.17.5
         */
        Schema::create('cat_mitigating_factors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique()
                ->comment('Código del atenuante: ACCEPTS_OFFENSE | COOPERATES | PREVENTED_DAMAGE | NO_MALICE');
            $table->text('description')
                ->comment('Descripción del atenuante y cómo reduce la sanción');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE si el atenuante ya no puede ser invocado');
            $table->timestamps();
        });

        /**
         * Estados por los que atraviesa una investigación disciplinaria.
         * Valores de code: OPEN | AWAITING_DEFENSE | IN_RESOLUTION | APPEALED | CLOSED
         */
        Schema::create('cat_process_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique()
                ->comment('Código del estado: OPEN | AWAITING_DEFENSE | IN_RESOLUTION | APPEALED | CLOSED');
            $table->string('name', 100)
                ->comment('Nombre del estado visible en la interfaz');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE si el estado fue retirado del flujo');
            $table->timestamps();
        });

        // ----------------------------------------------------------------
        // ZONA 2: TABLAS TRANSACCIONALES
        // Registran los hechos reales del proceso disciplinario.
        // Orden: investigation → dependientes de investigation → dependientes de resolution
        // ----------------------------------------------------------------

        /**
         * Caso disciplinario raíz. Todo el proceso parte de aquí.
         * Referencia: sec. 2.17
         */
        Schema::create('investigations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // FK a tablas existentes
            $table->uuid('eui_id')
                ->comment('FK: EUI que está siendo investigado');
            $table->foreign('eui_id')
                ->references('id')->on('users')
                ->restrictOnDelete();

            $table->uuid('instructor_id')
                ->comment('FK: Usuario Lifehuni que instruye y gestiona el caso');
            $table->foreign('instructor_id')
                ->references('id')->on('users')
                ->restrictOnDelete();

            // FK a tablas paramétricas
            $table->uuid('offense_type_id')
                ->comment('FK: Clasificación de la falta (minor/moderate/severe)');
            $table->foreign('offense_type_id')
                ->references('id')->on('cat_offense_types')
                ->restrictOnDelete();

            $table->uuid('complaint_source_id')
                ->comment('FK: Cómo llegó la información a Lifehuni');
            $table->foreign('complaint_source_id')
                ->references('id')->on('cat_complaint_sources')
                ->restrictOnDelete();

            $table->uuid('process_status_id')
                ->comment('FK: Estado actual del proceso disciplinario');
            $table->foreign('process_status_id')
                ->references('id')->on('cat_process_statuses')
                ->restrictOnDelete();

            // Campos propios
            $table->text('facts_description')->nullable()
                ->comment('Narración detallada de los hechos presuntamente infractores');
            $table->string('origin_detail')->nullable()
                ->comment('Contexto o fuente adicional de información del caso');
            $table->date('opened_at')
                ->comment('Fecha en que Lifehuni abre formalmente la investigación');
            $table->date('closed_at')->nullable()
                ->comment('Fecha de cierre del proceso (null = en curso)');
            $table->unsignedSmallInteger('offense_count')->default(1)
                ->comment('Número de sanción del EUI. Determina la severidad de la pena');
            $table->boolean('is_active')->default(true)
                ->comment('FALSE si el caso fue archivado sin sanción');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('eui_id', 'idx_inv_eui');
            $table->index('process_status_id', 'idx_inv_status');
            $table->index('offense_type_id', 'idx_inv_offense');
            $table->index('instructor_id', 'idx_inv_instructor');
            $table->index(['eui_id', 'is_active'], 'idx_inv_eui_active');
            $table->index(['eui_id', 'offense_count'], 'idx_inv_recurrence');
            $table->index('opened_at', 'idx_inv_date');
        });

        /**
         * Documentos y archivos que soportan la investigación.
         * Pueden ser cargados por Lifehuni o por el propio EUI en sus descargos.
         */
        Schema::create('investigation_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('investigation_id')
                ->comment('FK: Caso disciplinario al que pertenece');
            $table->foreign('investigation_id')
                ->references('id')->on('investigations')
                ->cascadeOnDelete();

            $table->uuid('uploaded_by_id')->nullable()
                ->comment('FK: Usuario que subió la evidencia (Lifehuni o EUI)');
            $table->foreign('uploaded_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->string('file_type', 20)->nullable()
                ->comment('Tipo de archivo: PDF | IMAGE | VIDEO | AUDIO | LINK | OTHER');
            $table->text('file_url')->nullable()
                ->comment('URL o ruta de almacenamiento del archivo');
            $table->text('description')->nullable()
                ->comment('Contenido y relevancia de la evidencia en el caso');
            $table->date('uploaded_at')
                ->comment('Fecha en que se adjuntó la evidencia al expediente');

            $table->timestamps();

            // Índices
            $table->index('investigation_id', 'idx_evi_investigation');
            $table->index('uploaded_by_id', 'idx_evi_user');
        });

        /**
         * Registro de cada comunicación oficial enviada al EUI.
         * Garantiza la trazabilidad del debido proceso.
         * Referencia: sec. 2.17.4
         */
        Schema::create('process_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('investigation_id')
                ->comment('FK: Caso al que corresponde la notificación');
            $table->foreign('investigation_id')
                ->references('id')->on('investigations')
                ->cascadeOnDelete();

            $table->uuid('sent_by_id')->nullable()
                ->comment('FK: Usuario Lifehuni que generó y envió la notificación');
            $table->foreign('sent_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->string('type', 30)->nullable()
                ->comment('Tipo: OPENING | SUMMONS | DECISION | APPEAL_RESPONSE | CLOSURE');
            $table->string('channel', 30)->nullable()
                ->comment('Canal: PHYSICAL_MAIL | EMAIL | DATA_MESSAGE | IN_PERSON');
            $table->text('content')->nullable()
                ->comment('Texto completo del mensaje enviado al EUI');
            $table->date('sent_at')
                ->comment('Fecha en que se realizó el envío');
            $table->boolean('receipt_confirmed')->default(false)
                ->comment('TRUE si el EUI confirmó recepción');

            $table->timestamps();

            // Índices
            $table->index('investigation_id', 'idx_notif_investigation');
            $table->index(['investigation_id', 'type'], 'idx_notif_type');
            $table->index('receipt_confirmed', 'idx_notif_pending');
        });

        /**
         * Respuesta formal del EUI al proceso disciplinario.
         * Ejercicio del derecho a descargos.
         * Referencia: sec. 2.17.4
         */
        Schema::create('eui_defenses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('investigation_id')
                ->comment('FK: Caso al que responde el EUI');
            $table->foreign('investigation_id')
                ->references('id')->on('investigations')
                ->cascadeOnDelete();

            $table->text('defense_text')->nullable()
                ->comment('Texto de los descargos presentados por el EUI');
            $table->text('document_url')->nullable()
                ->comment('URL del documento formal firmado de descargos (si aplica)');
            $table->date('submitted_at')->nullable()
                ->comment('Fecha en que el EUI radicó sus descargos');
            $table->boolean('has_evidence')->default(false)
                ->comment('TRUE si el EUI adjuntó pruebas con sus descargos');

            $table->timestamps();

            // Índices
            $table->index('investigation_id', 'idx_def_investigation');
        });

        /**
         * Decisión formal de Lifehuni sobre el caso disciplinario.
         * Referencia: sec. 2.18.10
         */
        Schema::create('sanction_resolutions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('investigation_id')
                ->comment('FK: Caso que origina esta resolución');
            $table->foreign('investigation_id')
                ->references('id')->on('investigations')
                ->restrictOnDelete();

            $table->uuid('sanction_type_id')
                ->comment('FK: Tipo de sanción (según falta y reincidencia)');
            $table->foreign('sanction_type_id')
                ->references('id')->on('cat_sanction_types')
                ->restrictOnDelete();

            $table->uuid('action_type_id')
                ->comment('FK: Acción concreta que se ejecutará sobre el EUI');
            $table->foreign('action_type_id')
                ->references('id')->on('cat_action_types')
                ->restrictOnDelete();

            $table->uuid('resolved_by_id')->nullable()
                ->comment('FK: Usuario Lifehuni que firma y emite la resolución');
            $table->foreign('resolved_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->text('resolution_text')->nullable()
                ->comment('Texto completo de la resolución motivada y firmada');
            $table->date('resolved_at')
                ->comment('Fecha en que Lifehuni emite y notifica la resolución');
            $table->date('effect_start_date')->nullable()
                ->comment('Fecha desde la cual la sanción tiene efecto');
            $table->date('effect_end_date')->nullable()
                ->comment('Fecha en que la sanción expira (null = indefinida)');
            $table->boolean('is_appealable')->default(true)
                ->comment('TRUE si el EUI puede recurrir ante el Comité (sec. 2.19)');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('investigation_id', 'idx_res_investigation');
            $table->index('sanction_type_id', 'idx_res_sanction');
            $table->index('resolved_by_id', 'idx_res_instructor');
            $table->index('resolved_at', 'idx_res_date');
            $table->index(['effect_start_date', 'effect_end_date'], 'idx_res_validity');
        });

        /**
         * Atenuantes reconocidos en el caso para reducir la sanción.
         * Referencia: sec. 2.17.5
         */
        Schema::create('applied_mitigating_factors', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('investigation_id')
                ->comment('FK: Caso donde se reconoce el atenuante');
            $table->foreign('investigation_id')
                ->references('id')->on('investigations')
                ->cascadeOnDelete();

            $table->uuid('mitigating_factor_id')
                ->comment('FK: Tipo de atenuante reconocido');
            $table->foreign('mitigating_factor_id')
                ->references('id')->on('cat_mitigating_factors')
                ->restrictOnDelete();

            $table->text('justification')->nullable()
                ->comment('Justificación de por qué aplica este atenuante al caso concreto');

            $table->timestamps();

            // Índices
            $table->index('investigation_id', 'idx_mit_investigation');
            $table->index('mitigating_factor_id', 'idx_mit_type');
        });

        /**
         * Efectos operativos concretos activados con la sanción.
         * Cada flag representa una restricción real sobre el EUI en el sistema.
         * Referencia: sec. 2.18.4 a 2.18.6
         */
        Schema::create('sanction_enforcements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('resolution_id')
                ->comment('FK: Resolución que origina estos efectos');
            $table->foreign('resolution_id')
                ->references('id')->on('sanction_resolutions')
                ->cascadeOnDelete();

            $table->string('enforcement_type', 30)
                ->comment('Categoría: PARTIAL_SUSPENSION | FULL_SUSPENSION | TERMINATION');
            $table->boolean('suspend_multilevel')->default(false)
                ->comment('TRUE: bloquea representación en red multinivel');
            $table->boolean('freeze_earnings')->default(false)
                ->comment('TRUE: retiene utilidades, bonos, incentivos y calificaciones');
            $table->boolean('block_orders')->default(false)
                ->comment('TRUE: impide al EUI realizar nuevos pedidos');
            $table->boolean('block_qualification')->default(false)
                ->comment('TRUE: suspende avance en rangos y títulos');
            $table->boolean('terminate_contract')->default(false)
                ->comment('TRUE: termina definitivamente el contrato de vinculación');
            $table->date('applied_at')->nullable()
                ->comment('Fecha en que los efectos se activan en el sistema');
            $table->date('lifted_at')->nullable()
                ->comment('Fecha en que los efectos se levantan (null = vigente)');

            $table->timestamps();

            // Índices
            $table->index('resolution_id', 'idx_enf_resolution');
            $table->index(['applied_at', 'lifted_at'], 'idx_enf_validity');
            $table->index('terminate_contract', 'idx_enf_terminations');
        });

        /**
         * Apelación ante el Comité Disciplinario Matriz.
         * El EUI dispone de 6 meses desde la sanción para solicitar revisión.
         * Referencia: sec. 2.19
         */
        Schema::create('committee_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('investigation_id')
                ->comment('FK: Caso disciplinario objeto de la revisión');
            $table->foreign('investigation_id')
                ->references('id')->on('investigations')
                ->restrictOnDelete();

            $table->uuid('requested_by_id')->nullable()
                ->comment('FK: EUI que presentó la solicitud de revisión');
            $table->foreign('requested_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->string('review_status', 30)
                ->comment('Estado: REQUESTED | UNDER_REVIEW | DECIDED | ARCHIVED');
            $table->date('requested_at')
                ->comment('Fecha en que el EUI radicó la apelación por escrito');
            $table->date('decided_at')->nullable()
                ->comment('Fecha en que el comité emitió su decisión final');
            $table->string('decision', 30)->nullable()
                ->comment('Decisión: CONFIRMED | MODIFIED | REVOKED | CONTRACT_TERMINATED');
            $table->text('decision_description')->nullable()
                ->comment('Texto completo de la decisión del comité disciplinario');

            $table->timestamps();

            // Índices
            $table->index('investigation_id', 'idx_com_investigation');
            $table->index('review_status', 'idx_com_status');
            $table->index('requested_by_id', 'idx_com_eui');
            $table->index('requested_at', 'idx_com_date');
        });

        /**
         * Auditoría completa de cambios de estado del EUI generados por sanciones.
         * Permite reconstruir la línea de tiempo disciplinaria de cualquier EUI.
         */
        Schema::create('eui_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('eui_id')
                ->comment('FK: EUI cuyo estado cambió');
            $table->foreign('eui_id')
                ->references('id')->on('users')
                ->restrictOnDelete();

            $table->uuid('investigation_id')->nullable()
                ->comment('FK: Caso que originó el cambio de estado');
            $table->foreign('investigation_id')
                ->references('id')->on('investigations')
                ->nullOnDelete();

            $table->uuid('resolution_id')->nullable()
                ->comment('FK: Resolución que formalizó el cambio (null si es automático)');
            $table->foreign('resolution_id')
                ->references('id')->on('sanction_resolutions')
                ->nullOnDelete();

            $table->uuid('changed_by_id')->nullable()
                ->comment('FK: Usuario del sistema que ejecutó el cambio');
            $table->foreign('changed_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->string('previous_status', 20)
                ->comment('Estado previo del EUI: ACTIVE | SUSPENDED | EXPIRED | CANCELLED');
            $table->string('new_status', 20)
                ->comment('Estado nuevo asignado al EUI tras la sanción');
            $table->timestamp('changed_at')
                ->comment('Fecha y hora exacta en que ocurrió el cambio de estado');

            $table->timestamps();

            // Índices
            $table->index('eui_id', 'idx_log_eui');
            $table->index('investigation_id', 'idx_log_investigation');
            $table->index(['eui_id', 'changed_at'], 'idx_log_eui_date');
            $table->index('changed_at', 'idx_log_date');
        });
    }

    public function down(): void
    {
        // Eliminar en orden inverso para respetar FK
        Schema::dropIfExists('eui_status_logs');
        Schema::dropIfExists('committee_reviews');
        Schema::dropIfExists('sanction_enforcements');
        Schema::dropIfExists('applied_mitigating_factors');
        Schema::dropIfExists('sanction_resolutions');
        Schema::dropIfExists('eui_defenses');
        Schema::dropIfExists('process_notifications');
        Schema::dropIfExists('investigation_evidences');
        Schema::dropIfExists('investigations');

        Schema::dropIfExists('cat_process_statuses');
        Schema::dropIfExists('cat_mitigating_factors');
        Schema::dropIfExists('cat_complaint_sources');
        Schema::dropIfExists('cat_action_types');
        Schema::dropIfExists('cat_sanction_types');
        Schema::dropIfExists('cat_offense_types');
    }
};