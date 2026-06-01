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
        Schema::table('project_requirements', function (Blueprint $table) {
            $table->string('finish_level')->default('standard')->change();
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->string('finish_level')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_requirements', function (Blueprint $table) {
            $table->enum('finish_level', ['economy', 'standard', 'premium'])->default('standard')->change();
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->enum('finish_level', ['economy', 'standard', 'premium'])->change();
        });
    }
};
