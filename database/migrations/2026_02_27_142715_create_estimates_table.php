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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_requirement_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('finish_level', ['economy', 'standard', 'premium']);
            $table->decimal('gross_floor_area', 14, 2);
            $table->decimal('wall_area', 14, 2);
            $table->decimal('roof_area', 14, 2);
            $table->decimal('slab_area', 14, 2);
            $table->decimal('total_cost', 14, 2);
            $table->decimal('cost_per_sqm', 14, 2);
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['project_id', 'generated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
