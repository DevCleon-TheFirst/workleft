<?php

namespace App\Modules\Classroom\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Classroom\Models\AssignmentTemplate;

class AssignmentBankController extends Controller
{
    public function index(Request $request)
    {
        $templates = AssignmentTemplate::where('teacher_id', $request->user()->id)->latest()->get();
        return Inertia::render('Classroom/AssignmentBank/Index', [
            'templates' => $templates
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description_markdown' => 'required|string'
        ]);

        AssignmentTemplate::create([
            'teacher_id' => $request->user()->id,
            'title' => $request->title,
            'description_markdown' => $request->description_markdown,
        ]);

        return redirect()->back()->with('success', 'Assignment Template created!');
    }

    public function destroy(Request $request, AssignmentTemplate $template)
    {
        if ($template->teacher_id !== $request->user()->id) {
            abort(403);
        }
        $template->delete();
        return redirect()->back()->with('success', 'Template deleted!');
    }
}
