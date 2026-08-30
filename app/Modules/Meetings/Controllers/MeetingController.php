<?php

namespace App\Modules\Meetings\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Meetings\Models\Meeting;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $meetings = Meeting::with('project')
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15);

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'projects' => Project::select('id', 'title')->get(),
            'filters'  => $request->only('project_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Meetings/Create', [
            'projects' => Project::select('id', 'title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'title'        => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'transcript'   => 'nullable|string',
        ]);

        $meeting = Meeting::create($data);
        return redirect()->route('meetings.show', $meeting)->with('success', 'Meeting scheduled.');
    }

    public function show(Meeting $meeting)
    {
        return Inertia::render('Meetings/Show', ['meeting' => $meeting->load('project')]);
    }

    public function edit(Meeting $meeting)
    {
        return Inertia::render('Meetings/Edit', [
            'meeting'  => $meeting,
            'projects' => Project::select('id', 'title')->get(),
        ]);
    }

    public function update(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'title'        => 'required|string|max:255',
            'scheduled_at' => 'required|date',
            'transcript'   => 'nullable|string',
        ]);

        $meeting->update($data);
        return redirect()->route('meetings.show', $meeting)->with('success', 'Meeting updated.');
    }

    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Meeting deleted.');
    }
}
