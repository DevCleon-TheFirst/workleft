<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clients\Models\Client;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('client')
            ->withCount('tasks')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(12);

        return Inertia::render('Projects/Index', [
            'projects'       => $projects,
            'filters'        => $request->only('status'),
            'statusOptions'  => ['planning', 'in_progress', 'review', 'done'],
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create', [
            'clients' => Client::select('id', 'company_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'   => 'nullable|exists:clients,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,in_progress,review,done',
        ]);
        $project = Project::create($data);

        if ($request->has('return_back')) {
            return back()->with('success', 'Project created.');
        }
        
        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project)
    {
        $project->load([
            'client',
            'tasks' => fn($q) => $q->latest(),
            'meetings' => fn($q) => $q->orderBy('scheduled_at'),
            'documents' => fn($q) => $q->latest(),
        ]);
        return Inertia::render('Projects/Show', ['project' => $project]);
    }

    public function edit(Project $project)
    {
        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => Client::select('id', 'company_name')->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'client_id'   => 'nullable|exists:clients,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:planning,in_progress,review,done',
        ]);
        $project->update($data);
        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
