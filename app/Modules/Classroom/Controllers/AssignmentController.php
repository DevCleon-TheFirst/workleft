<?php

namespace App\Modules\Classroom\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Modules\Classroom\Models\Assignment;
use App\Modules\Classroom\Models\AssignmentTemplate;
use App\Modules\Classroom\Models\Submission;
use App\Models\User;

class AssignmentController extends Controller
{
    // Teacher: Release an assignment (from a template) to all or specific students
    public function store(Request $request)
    {
        $request->validate([
            'assignment_template_id' => 'required|exists:assignment_templates,id',
            'due_date'               => 'nullable|date|after:now',
            'target'                 => 'required|in:all,specific',
            'student_ids'            => 'nullable|array',
            'student_ids.*'          => 'exists:users,id',
        ]);

        $visibility = $request->target;

        if ($visibility === 'specific' && empty($request->student_ids)) {
            return redirect()->back()->withErrors(['student_ids' => 'You must select at least one student.']);
        }

        $assignment = Assignment::create([
            'assignment_template_id' => $request->assignment_template_id,
            'due_date'               => $request->due_date,
            'status'                 => 'active',
            'visibility'             => $visibility,
        ]);

        if ($visibility === 'specific') {
            $assignment->students()->sync($request->student_ids);
            $students = User::whereIn('id', $request->student_ids)->get();
        } else {
            $students = User::where('role', 'student')->get();
        }

        \Illuminate\Support\Facades\Notification::send($students, new \App\Notifications\NewAssignmentNotification($assignment));

        $message = $visibility === 'all' 
            ? 'Assignment released to all students!' 
            : 'Assignment released to ' . count($request->student_ids) . ' students!';

        return redirect()->back()->with('success', $message);
    }

    // Teacher: Close an assignment
    public function close(Request $request, Assignment $assignment)
    {
        $assignment->update(['status' => 'closed']);
        return redirect()->back()->with('success', 'Assignment closed.');
    }

    // Teacher: View all submissions for an assignment
    public function submissions(Request $request, Assignment $assignment)
    {
        $submissions = $assignment->submissions()->with('student')->latest()->get();
        return Inertia::render('Classroom/Assignments/Submissions', [
            'assignment' => $assignment->load('template'),
            'submissions' => $submissions,
        ]);
    }

    // Teacher: Grade a submission
    public function grade(Request $request, Submission $submission)
    {
        $request->validate([
            'score' => 'required|integer|min:0|max:100',
        ]);

        $submission->update([
            'score' => $request->score,
            'status' => 'graded',
        ]);

        $submission->load('student', 'assignment.template');
        if ($submission->student) {
            \Illuminate\Support\Facades\Notification::send($submission->student, new \App\Notifications\AssignmentGradedNotification($submission));
        }

        return redirect()->back()->with('success', 'Submission graded!');
    }

    // Teacher: Delete a submission (only after deadline)
    public function deleteSubmission(Submission $submission)
    {
        $assignment = $submission->assignment;

        // Enforce: deadline must have passed
        if ($assignment->due_date && now()->lessThan($assignment->due_date)) {
            return redirect()->back()->withErrors([
                'delete' => 'You can only delete submissions after the assignment deadline has passed.'
            ]);
        }

        // Delete attached file from storage if present
        if ($submission->file_path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($submission->file_path);
        }

        $submission->delete();

        return redirect()->back()->with('success', 'Submission deleted.');
    }

    // Student: Submit an assignment
    public function submit(Request $request, Assignment $assignment)
    {
        $request->validate([
            'github_url' => 'nullable|url',
            'live_url' => 'nullable|url',
            'comments' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:10240|mimes:zip,rar,pdf,doc,docx,png,jpg,jpeg',
        ]);

        // Prevent double submission
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $request->user()->id)
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors(['already_submitted' => 'You have already submitted this assignment.']);
        }

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('submissions', 'local');
        }

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $request->user()->id,
            'github_url' => $request->github_url,
            'live_url' => $request->live_url,
            'file_path' => $filePath,
            'comments' => $request->comments,
        ]);

        $assignment->load('template.teacher');
        $teacher = $assignment->template->teacher ?? null;
        if ($teacher) {
            \Illuminate\Support\Facades\Notification::send($teacher, new \App\Notifications\AssignmentSubmittedNotification($submission));
        }

        return redirect()->back()->with('success', 'Assignment submitted successfully!');
    }

    public function downloadAttachment(Request $request, Submission $submission)
    {
        $user = $request->user();

        // Ensure user is authorized to download
        if ($user->role !== 'teacher' && $user->id !== $submission->student_id) {
            abort(403, 'Unauthorized.');
        }

        if (!$submission->file_path || !\Illuminate\Support\Facades\Storage::disk('local')->exists($submission->file_path)) {
            abort(404, 'File not found.');
        }

        return \Illuminate\Support\Facades\Storage::disk('local')->download($submission->file_path);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Assignment::with(['template', 'submissions' => function ($q) use ($user) {
                $q->where('student_id', $user->id);
            }, 'students'])
            ->withCount('submissions');

        // If it's a student, only show assignments meant for them
        if ($user->role === 'student') {
            $query->where(function ($q) use ($user) {
                $q->where('visibility', 'all')
                  ->orWhereHas('students', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }

        $allAssignments = $query->latest()->get()->map(function ($assignment) {
            $assignment->my_submission = $assignment->submissions->first();
            return $assignment;
        });
        $data = [
            'activeAssignments' => $allAssignments->where('status', 'active')->values(),
            'closedAssignments' => $allAssignments->where('status', 'closed')->values(),
        ];

        // Give teachers the template list and student list
        if ($user->role === 'teacher') {
            $data['templates'] = \App\Modules\Classroom\Models\AssignmentTemplate::where('teacher_id', $user->id)->get();
            $data['students']  = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email']);
        } else {
            $data['templates'] = [];
            $data['students']  = [];
        }

        return Inertia::render('Classroom/Assignments/Index', $data);
    }
}
