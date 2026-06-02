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
        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_task_id')->nullable()->constrained('project_tasks')->cascadeOnDelete();
            $table->foreignId('estimate_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('author_name');
            $table->string('author_role');
            $table->text('content');
            $table->string('type')->default('comment'); // 'comment' or 'revision_request'
            $table->string('status')->nullable();       // 'pending', 'resolved', etc. for revision requests
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_comments');
    }
};
