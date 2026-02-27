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
        Schema::create('estimate_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained()->cascadeOnDelete();
            $table->string('metric_name');
            $table->foreignId('assembly_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 14, 4);
            $table->string('unit');
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();

            $table->index('metric_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_lines');
    }
};
