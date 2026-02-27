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
        Schema::create('project_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number_of_floors')->default(1);
            $table->unsignedInteger('bedrooms')->default(0);
            $table->unsignedInteger('bathrooms')->default(0);
            $table->unsignedInteger('garage_count')->default(0);
            $table->unsignedInteger('living_rooms')->default(0);
            $table->unsignedInteger('kitchen_count')->default(0);
            $table->enum('finish_level', ['economy', 'standard', 'premium'])->default('standard');
            $table->timestamps();

            $table->unique('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_requirements');
    }
};
