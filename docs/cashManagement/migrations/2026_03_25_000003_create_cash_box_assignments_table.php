<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_box_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cash_box_id')->constrained('cash_boxes');
            $table->foreignId('user_id')->constrained('users');
            
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();

            $table->index(['user_id', 'is_active'], 'idx_active_assignments');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_box_assignments');
    }
};