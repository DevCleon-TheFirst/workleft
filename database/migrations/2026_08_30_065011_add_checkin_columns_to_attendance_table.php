<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            // Which session triggered this record (null = manually marked by teacher)
            $table->foreignId('session_id')
                  ->nullable()
                  ->constrained('attendance_sessions')
                  ->nullOnDelete()
                  ->after('note');

            // Student's reported GPS coordinates for audit
            $table->decimal('student_lat', 10, 7)->nullable()->after('session_id');
            $table->decimal('student_lng', 10, 7)->nullable()->after('student_lat');
            $table->unsignedInteger('distance_meters')->nullable()->after('student_lng');

            // How it was recorded
            $table->enum('method', ['manual', 'self_checkin'])->default('manual')->after('distance_meters');
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropColumn(['session_id', 'student_lat', 'student_lng', 'distance_meters', 'method']);
        });
    }
};
