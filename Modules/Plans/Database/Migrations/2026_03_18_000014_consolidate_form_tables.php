<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Create form_sections ───────────────────────────────────────────
        Schema::create('form_sections', function (Blueprint $table) {
            $table->id();
            $table->string('owner_key', 100);
            $table->json('section_label');
            $table->string('section_key', 100);
            $table->integer('section_order')->default(1);
            $table->boolean('is_repeatable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 2. Create form_fields ─────────────────────────────────────────────
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_section_id');
            $table->foreign('form_section_id')->references('id')->on('form_sections');
            $table->json('field_label');
            $table->string('field_key', 100);
            $table->enum('field_type', ['number', 'select', 'boolean', 'text', 'currency', 'multiselect'])->default('text');
            $table->boolean('is_required')->default(true);
            $table->json('help_text')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 3. Create form_options ────────────────────────────────────────────
        Schema::create('form_options', function (Blueprint $table) {
            $table->id();
            $table->json('option_label');
            $table->string('option_key', 50)->unique();
            $table->json('help_text')->nullable();
            $table->timestamps();
        });

        // ── 4. Data migration – rule form sections (preserve IDs) ─────────────
        DB::statement("
            INSERT INTO form_sections (id, owner_key, section_label, section_key, section_order, is_repeatable, is_active, created_at, updated_at)
            SELECT rfs.id, rc.`key` AS owner_key, rfs.section_label, rfs.section_key, rfs.section_order, rfs.is_repeatable, rfs.is_active, rfs.created_at, rfs.updated_at
            FROM rule_form_sections rfs
            JOIN rule_category rc ON rc.id = rfs.rule_category_id
        ");

        // ── 5. Data migration – benefit form sections (auto-increment, build map) ──
        $benefitSectionMap = [];
        $benefitSections = DB::table('benefit_form_sections')
            ->join('benefit_category', 'benefit_category.id', '=', 'benefit_form_sections.benefit_category_id')
            ->select('benefit_form_sections.*', 'benefit_category.key as category_key')
            ->orderBy('benefit_form_sections.id')
            ->get();

        foreach ($benefitSections as $row) {
            $newId = DB::table('form_sections')->insertGetId([
                'owner_key'     => $row->category_key,
                'section_label' => $row->section_label,
                'section_key'   => $row->section_key,
                'section_order' => $row->section_order,
                'is_repeatable' => $row->is_repeatable ?? false,
                'is_active'     => $row->is_active,
                'created_at'    => $row->created_at,
                'updated_at'    => $row->updated_at,
            ]);
            $benefitSectionMap[$row->id] = $newId;
        }

        // ── 6. Data migration – rule form fields (preserve IDs) ───────────────
        DB::statement("
            INSERT INTO form_fields (id, form_section_id, field_label, field_key, field_type, is_required, help_text, validation_rules, is_active, created_at, updated_at)
            SELECT id, rule_form_section_id AS form_section_id, field_label, field_key, field_type, is_required, help_text, validation_rules, is_active, created_at, updated_at
            FROM rule_form_fields
        ");

        // ── 7. Data migration – benefit form fields (auto-increment, build map) ─
        $benefitFieldMap = [];
        $benefitFields = DB::table('benefit_form_fields')
            ->orderBy('id')
            ->get();

        foreach ($benefitFields as $row) {
            $newSectionId = $benefitSectionMap[$row->benefit_form_section_id] ?? $row->benefit_form_section_id;
            $newId = DB::table('form_fields')->insertGetId([
                'form_section_id'  => $newSectionId,
                'field_label'      => $row->field_label,
                'field_key'        => $row->field_key,
                'field_type'       => $row->field_type,
                'is_required'      => $row->is_required,
                'help_text'        => $row->help_text,
                'validation_rules' => $row->validation_rules,
                'is_active'        => $row->is_active,
                'created_at'       => $row->created_at,
                'updated_at'       => $row->updated_at,
            ]);
            $benefitFieldMap[$row->id] = $newId;
        }

        // ── 8. Data migration – form_options from rule_form_options (preserve IDs) ─
        DB::statement("
            INSERT INTO form_options (id, option_label, option_key, help_text, created_at, updated_at)
            SELECT id, option_label, option_key, help_text, created_at, updated_at
            FROM rule_form_options
        ");

        // ── 9. Create form_answers (unified polymorphic answer table) ────────────
        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();
            $table->string('formable_type', 100);  // 'rule' | 'benefit' | any future type
            $table->unsignedBigInteger('formable_id');
            $table->index(['formable_type', 'formable_id']);
            $table->unsignedBigInteger('form_field_id');
            $table->foreign('form_field_id')->references('id')->on('form_fields');
            $table->text('answer')->nullable();
            $table->integer('repeat_index')->nullable();
            $table->timestamps();
        });

        // ── 10. Migrate rule_form_answers → form_answers ──────────────────────
        // rule_form_field_id maps 1:1 to form_fields (IDs preserved for rule fields)
        DB::statement("
            INSERT INTO form_answers (formable_type, formable_id, form_field_id, answer, repeat_index, created_at, updated_at)
            SELECT 'rule', rule_id, rule_form_field_id, answer, repeat_index, created_at, updated_at
            FROM rule_form_answers
        ");

        // ── 11. Migrate benefit_form_answers → form_answers ──────────────────
        // benefit_form_field_id needs remapping via $benefitFieldMap (new IDs assigned in step 7)
        $benefitAnswers = DB::table('benefit_form_answers')->get();
        foreach ($benefitAnswers as $row) {
            DB::table('form_answers')->insert([
                'formable_type' => 'benefit',
                'formable_id'   => $row->benefit_id,
                'form_field_id' => $benefitFieldMap[$row->benefit_form_field_id] ?? $row->benefit_form_field_id,
                'answer'        => $row->answer,
                'repeat_index'  => $row->repeat_index,
                'created_at'    => $row->created_at,
                'updated_at'    => $row->updated_at,
            ]);
        }

        // ── 12. Drop old answer tables ────────────────────────────────────────
        Schema::dropIfExists('rule_form_answers');
        Schema::dropIfExists('benefit_form_answers');

        // ── 13. Drop old catalog tables in safe order ─────────────────────────
        Schema::dropIfExists('rule_form_fields');
        Schema::dropIfExists('rule_form_sections');
        Schema::dropIfExists('benefit_form_fields');
        Schema::dropIfExists('benefit_form_sections');
        Schema::dropIfExists('rule_form_options');
        Schema::dropIfExists('benefit_form_options');
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'Migration 2026_03_18_000014_consolidate_form_tables is irreversible due to data transformation complexity. Manual rollback required.'
        );
    }
};
