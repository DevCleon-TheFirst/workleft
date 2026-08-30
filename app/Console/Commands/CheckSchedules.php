<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Tasks\Models\Task;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckSchedules extends Command
{
    protected $signature   = 'workleft:check-schedules';
    protected $description = 'Mark overdue tasks and past meetings so the dashboard stays accurate';

    public function handle(): int
    {
        $now = Carbon::now();

        // ── Tasks: mark overdue ───────────────────────────────────────────────
        $overdueCount = Task::whereIn('status', ['todo', 'in_progress'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now->toDateString())
            ->update(['status' => 'overdue']);

        if ($overdueCount > 0) {
            $this->info("[{$now}] Tasks marked overdue: {$overdueCount}");
            Log::info("CheckSchedules: {$overdueCount} task(s) marked overdue.");
        }

        // ── Meetings: mark past ───────────────────────────────────────────────
        $pastCount = Meeting::where('status', 'upcoming')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', $now)
            ->update(['status' => 'past']);

        if ($pastCount > 0) {
            $this->info("[{$now}] Meetings flipped to past: {$pastCount}");
            Log::info("CheckSchedules: {$pastCount} meeting(s) moved to past.");
        }

        // ── Tasks: auto-delete 24 hours after due ─────────────────────────────
        $deleteCount = Task::whereNotNull('due_date')
            ->where('due_date', '<=', $now->copy()->subHours(24))
            ->delete();

        if ($deleteCount > 0) {
            $this->info("[{$now}] Tasks auto-deleted (24h past due): {$deleteCount}");
            Log::info("CheckSchedules: {$deleteCount} task(s) auto-deleted.");
        }

        if ($overdueCount === 0 && $pastCount === 0 && $deleteCount === 0) {
            $this->line("[{$now}] Nothing to update.");
        }

        return self::SUCCESS;
    }
}
