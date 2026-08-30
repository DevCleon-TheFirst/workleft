<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clients\Models\Client;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Students don't have a workspace — send them to the classroom
        if ($user->role === 'student') {
            return redirect()->route('classroom.index');
        }

        $projectCount = Project::count();
        $taskCount    = Task::count();
        $clientCount  = Client::count();
        $doneCount    = Task::where('status', 'done')->count();
        $focusScore   = $taskCount > 0 ? (int) round(($doneCount / $taskCount) * 100) : 0;

        $recentProjects = Project::with('client')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->title,
                'client'   => $p->client?->company_name ?? 'No Client',
                'status'   => ucfirst($p->status),
                'progress' => $p->progress,
                'tasks'    => $p->tasks()->count(),
                'color'    => '#6366f1',
            ]);

        $todayTasks = Task::with('project')
            ->whereDate('due_date', today())
            ->orWhere(function ($q) {
                $q->whereNull('due_date')->where('status', '!=', 'done');
            })
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'id'       => $t->id,
                'title'    => $t->title,
                'project'  => $t->project?->title ?? 'No Project',
                'priority' => 'medium',
                'done'     => $t->status === 'done',
            ]);

        $upcomingMeetings = Meeting::with('project')
            ->whereDate('scheduled_at', '>=', today())
            ->orderBy('scheduled_at')
            ->take(3)
            ->get()
            ->map(fn($m) => [
                'id'        => $m->id,
                'title'     => $m->title,
                'time'      => $m->scheduled_at?->format('g:i A') ?? 'TBD',
                'duration'  => '1h',
                'attendees' => 3,
                'type'      => 'internal',
            ]);

        return Inertia::render('Dashboard', [
            'recentProjects'   => $recentProjects,
            'todayTasks'       => $todayTasks,
            'upcomingMeetings' => $upcomingMeetings,
            'aiMessage'        => $this->buildAiMessage($projectCount, $taskCount, $doneCount),
        ]);
    }

    private function buildAiMessage(int $projects, int $tasks, int $done): string
    {
        $open = $tasks - $done;
        if ($tasks === 0) {
            return "No tasks yet. Start by creating a project and adding tasks — I'll help you plan and prioritize as you go.";
        }
        return "You have {$open} open task" . ($open !== 1 ? 's' : '') . " across {$projects} project" . ($projects !== 1 ? 's' : '') . ". {$done} tasks completed so far. Keep the momentum going!";
    }
}
