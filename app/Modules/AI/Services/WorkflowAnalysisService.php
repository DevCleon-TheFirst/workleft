<?php

namespace App\Modules\AI\Services;

use App\Modules\AI\Providers\DeepSeekProvider;
use App\Modules\Tasks\Models\Task;
use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class WorkflowAnalysisService
{
    private DeepSeekProvider $ai;

    public function __construct(DeepSeekProvider $ai)
    {
        $this->ai = $ai;
    }

    public function getAnalysis(Request $request)
    {
        $userId = $request->user()->id;

        return Cache::remember("ai_workflow_{$userId}", now()->addHours(6), function () use ($userId) {
            // Gather stats
            $totalTasks = Task::where('assignee_id', $userId)->count();
            $completedTasks = Task::where('assignee_id', $userId)->where('status', 'done')->count();
            
            $overdueCount = Task::where('assignee_id', $userId)
                ->where('status', '!=', 'done')
                ->whereDate('due_date', '<', today())
                ->count();

            $projects = Project::whereHas('tasks', function($q) use ($userId) {
                $q->where('assignee_id', $userId);
            })->pluck('title')->implode(', ');

            if ($totalTasks == 0) {
                return [
                    'stats' => ['total' => 0, 'completed' => 0, 'overdue' => 0],
                    'advice' => ["Create some tasks first so I can analyze your workflow!"],
                    'learning' => ["Start by exploring the platform."]
                ];
            }

            $completionRate = round(($completedTasks / $totalTasks) * 100);

            $prompt = "You are a productivity coach for a software developer. Here are their recent stats:
- Total tasks: {$totalTasks}
- Completed: {$completedTasks} ({$completionRate}%)
- Currently Overdue: {$overdueCount}
- Active Project Types: {$projects}

1. Give 3 short, personalized, actionable productivity tips based on this data.
2. Recommend 3 specific tech skills/tools they should learn next.
Format exactly as:
TIPS:
- Tip 1
- Tip 2
- Tip 3
LEARNING:
- Skill 1
- Skill 2
- Skill 3";

            $analysis = $this->ai->chat([['role' => 'user', 'content' => $prompt]]);
            
            // Parse response
            $tips = [];
            $learning = [];
            
            $sections = explode('LEARNING:', $analysis);
            if (count($sections) == 2) {
                $tipsRaw = str_replace('TIPS:', '', $sections[0]);
                $learningRaw = $sections[1];

                preg_match_all('/-\s*(.+)/', $tipsRaw, $tipMatches);
                if (!empty($tipMatches[1])) $tips = $tipMatches[1];

                preg_match_all('/-\s*(.+)/', $learningRaw, $learningMatches);
                if (!empty($learningMatches[1])) $learning = $learningMatches[1];
            }

            if (empty($tips)) $tips = ["Focus on clearing overdue tasks first.", "Time-block your deep work.", "Review projects weekly."];
            if (empty($learning)) $learning = ["Docker", "Vue.js Advanced", "AI Prompt Engineering"];

            return [
                'stats' => [
                    'total' => $totalTasks,
                    'completed' => $completedTasks,
                    'overdue' => $overdueCount,
                    'rate' => $completionRate
                ],
                'advice' => $tips,
                'learning' => $learning
            ];
        });
    }
}
