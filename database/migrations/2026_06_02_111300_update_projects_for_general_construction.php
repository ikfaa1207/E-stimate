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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('building_type')->default('residential')->after('notes'); // residential, commercial, industrial, institutional
            $table->string('structural_type')->default('concrete')->after('building_type'); // concrete, steel, mixed
            $table->string('foundation_type')->default('footing')->after('structural_type'); // footing, pile, raft
            $table->unsignedInteger('number_of_floors')->default(1)->after('foundation_type');
            $table->decimal('gross_floor_area', 12, 2)->default(0.00)->after('number_of_floors');
            $table->decimal('footprint_area', 12, 2)->default(0.00)->after('gross_floor_area');
            $table->string('finish_level')->default('standard')->after('footprint_area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'building_type',
                'structural_type',
                'foundation_type',
                'number_of_floors',
                'gross_floor_area',
                'footprint_area',
                'finish_level',
            ]);
        });
    }
};
