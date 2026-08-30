<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->string('session_label')->nullable();   // e.g. "Week 3 – Morning"

            // Geofence
            $table->decimal('latitude', 10, 7);            // classroom centre lat
            $table->decimal('longitude', 10, 7);           // classroom centre lng
            $table->unsignedInteger('radius_meters')->default(100);

            // Session window
            $table->timestamp('expires_at');               // auto-closes after this

            // Unique token for the student check-in URL
            $table->string('token', 64)->unique();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
