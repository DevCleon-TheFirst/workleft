<?php

namespace App\Modules\Classroom\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Classroom\Models\Material;
use App\Models\User;

class MaterialsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Material::with('students')->orderBy('module')->orderBy('created_at');

        if ($user->role === 'student') {
            $query->where(function ($q) use ($user) {
                $q->where('visibility', 'all')
                  ->orWhereHas('students', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }

        $materials = $query->get();

        // Group by module for frontend display
        $grouped = $materials->groupBy('module');
        
        $data = [
            'groupedMaterials' => $grouped,
        ];

        if ($user->role === 'teacher') {
            $data['students'] = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email']);
        } else {
            $data['students'] = [];
        }

        return Inertia::render('Classroom/Materials/Index', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'module'      => 'required|string|max:100',
            'type'        => 'required|in:link,pdf,video',
            'content_url' => 'required|url',
            'description' => 'nullable|string|max:1000',
            'target'      => 'required|in:all,specific',
            'student_ids'   => 'nullable|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $visibility = $request->target;

        if ($visibility === 'specific' && empty($request->student_ids)) {
            return redirect()->back()->withErrors(['student_ids' => 'You must select at least one student.']);
        }

        $material = Material::create([
            'teacher_id'  => $request->user()->id,
            'title'       => $request->title,
            'module'      => $request->module,
            'type'        => $request->type,
            'content_url' => $request->content_url,
            'description' => $request->description,
            'visibility'  => $visibility,
        ]);

        if ($visibility === 'specific') {
            $material->students()->sync($request->student_ids);
        }

        return redirect()->back()->with('success', 'Material added to vault!');
    }

    public function destroy(Request $request, Material $material)
    {
        if ($material->teacher_id !== $request->user()->id) {
            abort(403);
        }
        $material->delete();
        return redirect()->back()->with('success', 'Material removed.');
    }
}
