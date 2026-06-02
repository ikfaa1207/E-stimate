<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('permit'); // permit, clearance, code
            $table->string('status')->default('not_started'); // not_started, pending, approved, not_applicable
            $table->decimal('fee', 12, 2)->default(0.00);
            $table->date('target_date')->nullable();
            $table->date('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('project_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('sequence')->default(0);
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_phase_id')->constrained('project_phases')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('pending'); // pending, in_progress, completed
            $table->decimal('estimated_cost', 12, 2)->default(0.00);
            $table->decimal('actual_cost', 12, 2)->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_phases');
        Schema::dropIfExists('project_compliances');
    }
};
