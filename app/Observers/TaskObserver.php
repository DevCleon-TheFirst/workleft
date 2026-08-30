<?php

namespace App\Observers;

use App\Modules\Tasks\Models\Task;
use App\Jobs\DispatchStartingPing;
use App\Jobs\DispatchTimeUpChime;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->scheduleNotifications($task);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $this->scheduleNotifications($task);
    }

    protected function scheduleNotifications(Task $task): void
    {
        if (!in_array($task->status, ['todo', 'in_progress'])) return;

        if ($task->start_date) {
            // Dispatch warning 5 minutes before start date
            $startPingTime = $task->start_date->copy()->subMinutes(5);
            $delay = now()->diffInSeconds($startPingTime, false);
            if ($delay >= 0) {
                DispatchStartingPing::dispatch('task', $task->id, $task->title, $task->start_date->toIso8601String())
                    ->delay($delay);
            }
        }

        if ($task->due_date) {
            // Dispatch due time up exactly on due date
            $delay = now()->diffInSeconds($task->due_date, false);
            if ($delay >= 0) {
                DispatchTimeUpChime::dispatch('task', $task->id, $task->title, $task->due_date->toIso8601String())
                    ->delay($delay);
            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
