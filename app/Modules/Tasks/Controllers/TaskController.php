<?php

namespace App\Modules\Tasks\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Tasks\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::with('project')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->latest()
            ->get()
            ->groupBy('status');

        return Inertia::render('Tasks/Index', [
            'tasksByStatus' => $tasks,
            'projects'      => Project::select('id', 'title')->get(),
            'filters'       => $request->only('status', 'project_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tasks/Create', [
            'projects' => Project::select('id', 'title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title'      => 'required|string|max:255',
            'status'     => 'required|in:todo,in_progress,done',
            'start_date' => 'nullable|date',
            'due_date'   => 'nullable|date|after_or_equal:start_date',
        ]);
        
        $data['assignee_id'] = $request->user()->id;
        
        Task::create($data);
        return redirect()->route('tasks.index')->with('success', 'Task created.');
    }

    public function show(Task $task)
    {
        return Inertia::render('Tasks/Show', ['task' => $task->load('project', 'assignee')]);
    }

    public function edit(Task $task)
    {
        return Inertia::render('Tasks/Edit', [
            'task'     => $task,
            'projects' => Project::select('id', 'title')->get(),
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title'      => 'required|string|max:255',
            'status'     => 'required|in:todo,in_progress,done',
            'start_date' => 'nullable|date',
            'due_date'   => 'nullable|date|after_or_equal:start_date',
        ]);
        $task->update($data);
        return redirect()->route('tasks.index')->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }
}
