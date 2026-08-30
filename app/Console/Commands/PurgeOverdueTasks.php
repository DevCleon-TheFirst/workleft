<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Tasks\Models\Task;

class PurgeOverdueTasks extends Command
{
    protected $signature   = 'tasks:purge-overdue';
    protected $description = 'Delete tasks that have been overdue for more than 24 hours.';

    public function handle(): int
    {
        $cutoff = now()->subHours(24);

        $deleted = Task::query()
            ->whereNotIn('status', ['done'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $cutoff)
            ->delete();

        $this->info("Purged {$deleted} overdue task(s) older than 24 hours.");

        return self::SUCCESS;
    }
}
