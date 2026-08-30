<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\ScheduleStartingSoon;

class DispatchStartingPing implements ShouldQueue
{
    use Queueable;

    public string $type;
    public int $id;
    public string $title;
    public string $scheduledAt; // ISO8601 string of when it was scheduled

    /**
     * Create a new job instance.
     */
    public function __construct(string $type, int $id, string $title, string $scheduledAt)
    {
        $this->type = $type;
        $this->id = $id;
        $this->title = $title;
        $this->scheduledAt = $scheduledAt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->type === 'task') {
            $model = \App\Modules\Tasks\Models\Task::find($this->id);
            if (!$model || !$model->start_date || $model->start_date->toIso8601String() !== $this->scheduledAt) return;
        } else {
            $model = \App\Modules\Meetings\Models\Meeting::find($this->id);
            if (!$model || !$model->scheduled_at || $model->scheduled_at->toIso8601String() !== $this->scheduledAt) return;
        }

        event(new ScheduleStartingSoon($this->type, $this->id, $this->title));
    }
}
