<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('attendance:weekly-summary')]
#[Description('Send a weekly attendance summary to teachers for their sessions over the past 7 days.')]
class SendWeeklyAttendance extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = now()->subDays(7);

        // Get all teachers who have had sessions in the last 7 days
        $teachers = \App\Models\User::where('role', 'teacher')
            ->whereHas('attendanceSessions', function ($query) use ($cutoff) {
                $query->where('created_at', '>=', $cutoff);
            })
            ->with(['attendanceSessions' => function ($query) use ($cutoff) {
                $query->where('created_at', '>=', $cutoff)
                      ->with('attendanceRecords.student');
            }])
            ->get();

        foreach ($teachers as $teacher) {
            $studentCounts = [];
            $sessionCount = $teacher->attendanceSessions->count();

            foreach ($teacher->attendanceSessions as $session) {
                foreach ($session->attendanceRecords as $record) {
                    $student = $record->student;
                    if ($student) {
                        if (!isset($studentCounts[$student->id])) {
                            $studentCounts[$student->id] = [
                                'name' => $student->name,
                                'email' => $student->email,
                                'count' => 0,
                            ];
                        }
                        $studentCounts[$student->id]['count']++;
                    }
                }
            }

            // Sort students by attendance count (descending)
            usort($studentCounts, fn($a, $b) => $b['count'] <=> $a['count']);

            \Illuminate\Support\Facades\Notification::send($teacher, new \App\Notifications\WeeklyAttendanceSummary($studentCounts, $sessionCount));
        }

        $this->info("Weekly attendance summaries sent to " . $teachers->count() . " teacher(s).");
        return self::SUCCESS;
    }
}
