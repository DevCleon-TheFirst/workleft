<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('raw_description');
            $table->json('agent_log');      // Full transcript of all 7 agent outputs
            $table->json('deliverable');    // The final structured JSON from the Tech Lead
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamps();

            // Indexes for common query patterns
            $table->index(['user_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprints');
    }
};
