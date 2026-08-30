<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('marked_by')->constrained('users')->onDelete('cascade'); // teacher
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->string('session_label')->nullable(); // e.g. "Week 3 – Session 1", "Morning Class"
            $table->text('note')->nullable();
            $table->timestamps();

            // One record per student per date per session
            $table->unique(['student_id', 'date', 'session_label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
