<?php

namespace App\Modules\Knowledge\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Knowledge\Models\Document;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::with('project')
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(15);

        return Inertia::render('Knowledge/Index', [
            'documents' => $documents,
            'projects'  => Project::select('id', 'title')->get(),
            'filters'   => $request->only('project_id', 'type'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Knowledge/Create', [
            'projects' => Project::select('id', 'title')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'type'             => 'required|string|max:50',
            'content_markdown' => 'required|string',
        ]);

        $document = Document::create($data);
        return redirect()->route('documents.show', $document)->with('success', 'Document created.');
    }

    public function show(Document $document)
    {
        return Inertia::render('Knowledge/Show', ['document' => $document->load('project')]);
    }

    public function edit(Document $document)
    {
        return Inertia::render('Knowledge/Edit', [
            'document' => $document,
            'projects' => Project::select('id', 'title')->get(),
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'type'             => 'required|string|max:50',
            'content_markdown' => 'required|string',
        ]);

        $document->update($data);
        return redirect()->route('documents.show', $document)->with('success', 'Document updated.');
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return redirect()->route('documents.index')->with('success', 'Document deleted.');
    }
}
