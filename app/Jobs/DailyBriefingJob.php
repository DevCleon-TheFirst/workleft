<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Tasks\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DailyBriefingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $this->processTasksDueToday($user);
            $this->processOverdueTasks($user);
            $this->processUpcomingMeetings($user);
        }
    }

    private function processTasksDueToday(User $user)
    {
        $tasks = Task::where('assignee_id', $user->id)
            ->whereDate('due_date', today())
            ->where('status', '!=', 'done')
            ->get();

        foreach ($tasks as $task) {
            $title = "Task Due Today";
            $body = "Don't forget to complete '{$task->title}' today.";
            $this->createNotificationIfNotExists($user, 'task_due', $title, $body, "/tasks/{$task->id}");
        }
    }

    private function processOverdueTasks(User $user)
    {
        $tasks = Task::where('assignee_id', $user->id)
            ->whereDate('due_date', '<', today())
            ->where('status', '!=', 'done')
            ->get();

        foreach ($tasks as $task) {
            $title = "Overdue Task";
            $days = today()->diffInDays($task->due_date);
            $body = "'{$task->title}' is overdue by {$days} day(s).";
            $this->createNotificationIfNotExists($user, 'task_overdue', $title, $body, "/tasks/{$task->id}");
        }
    }

    private function processUpcomingMeetings(User $user)
    {
        // For simplicity, just meetings that are today and in the future
        $meetings = Meeting::whereDate('scheduled_at', today())
            ->whereTime('scheduled_at', '>', now())
            ->get();

        foreach ($meetings as $meeting) {
            $title = "Upcoming Meeting";
            $time = $meeting->scheduled_at->format('g:i A');
            $body = "You have '{$meeting->title}' scheduled for today at {$time}.";
            // Check if we already notified for this meeting
            $this->createNotificationIfNotExists($user, 'meeting_soon', $title, $body, "/meetings/{$meeting->id}");
        }
    }

    private function createNotificationIfNotExists(User $user, string $type, string $title, string $body, string $url)
    {
        // Avoid spamming the exact same notification body on the same day
        $exists = AppNotification::where('user_id', $user->id)
            ->where('type', $type)
            ->where('body', $body)
            ->whereDate('created_at', today())
            ->exists();

        if (!$exists) {
            AppNotification::create([
                'user_id'    => $user->id,
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'action_url' => $url,
            ]);
        }
    }
}
