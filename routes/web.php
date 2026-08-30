<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('student.login');
});

// Dashboard — redirects students to classroom
Route::get('/dashboard', [\App\Modules\Dashboard\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ── Routes accessible to ALL authenticated users ─────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications (students also receive broadcasts)
    Route::get('/notifications', [\App\Modules\Notifications\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [\App\Modules\Notifications\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [\App\Modules\Notifications\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Calendar Sync (used internally)
    Route::get('calendar/link', [\App\Http\Controllers\CalendarSyncController::class, 'link'])->name('calendar.link');

    // ── Classroom Hub (shared — role checks done inside controllers/components) ─
    Route::get('classroom', [\App\Modules\Classroom\Controllers\ClassroomController::class, 'index'])->name('classroom.index');

    // Assignments — students submit, teachers release/grade (role logic in controller)
    Route::get('classroom/assignments', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'index'])->name('classroom.assignments.index');
    Route::post('classroom/assignments/{assignment}/submit', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'submit'])->name('classroom.assignments.submit');
    Route::get('classroom/submissions/{submission}/download', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'downloadAttachment'])->name('classroom.submissions.download');

    // Materials Vault — read-only for students
    Route::get('classroom/materials', [\App\Modules\Classroom\Controllers\MaterialsController::class, 'index'])->name('classroom.materials.index');

    // Active session poll (students call this to know if check-in is open)
    Route::get('classroom/active-session', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'activeSession'])->name('classroom.active-session');

    // Check-in submit (students POST their GPS here)
    Route::post('checkin/{token}', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'checkin'])->name('classroom.checkin.submit');
});

// ── Teacher-only routes ───────────────────────────────────────────────────────
Route::middleware(['auth', 'teacher'])->group(function () {

    // Workspace
    Route::resource('clients', \App\Modules\Clients\Controllers\ClientController::class);
    Route::resource('projects', \App\Modules\Projects\Controllers\ProjectController::class);
    Route::resource('tasks', \App\Modules\Tasks\Controllers\TaskController::class);
    Route::resource('meetings', \App\Modules\Meetings\Controllers\MeetingController::class);
    Route::resource('documents', \App\Modules\Knowledge\Controllers\DocumentController::class);

    // AI Planner & Architect
    Route::get('ai/planner', function () { return Inertia::render('AI/Planner'); })->name('ai.planner');
    Route::get('ai/architect', function () { return Inertia::render('AI/Architect'); })->name('ai.architect');
    Route::post('ai/planner/interview', [\App\Modules\Planner\Controllers\AIPlannerController::class, 'interview'])->name('ai.planner.interview');
    Route::post('ai/planner/architecture', [\App\Modules\Planner\Controllers\AIPlannerController::class, 'generateArchitecture'])->name('ai.planner.architecture');
    Route::post('ai/analyze', [\App\Modules\Planner\Controllers\AIPlannerController::class, 'analyze'])->name('ai.analyze');
    Route::get('ai/blueprints', [\App\Modules\Planner\Controllers\BlueprintController::class, 'index'])->name('ai.blueprints.index');
    Route::post('ai/blueprints', [\App\Modules\Planner\Controllers\BlueprintController::class, 'store'])->name('ai.blueprints.store');
    Route::put('ai/blueprints/{blueprint}', [\App\Modules\Planner\Controllers\BlueprintController::class, 'update'])->name('ai.blueprints.update');
    Route::get('ai/blueprints/{blueprint}', [\App\Modules\Planner\Controllers\BlueprintController::class, 'show'])->name('ai.blueprints.show');

    // AI Intelligence Hub
    Route::get('ai/intelligence', [\App\Modules\AI\Controllers\IntelligenceController::class, 'index'])->name('ai.intelligence');
    Route::get('ai/intelligence/crypto', [\App\Modules\AI\Controllers\IntelligenceController::class, 'crypto'])->name('ai.intelligence.crypto');
    Route::get('ai/intelligence/news', [\App\Modules\AI\Controllers\IntelligenceController::class, 'news'])->name('ai.intelligence.news');
    Route::get('ai/intelligence/workflow', [\App\Modules\AI\Controllers\IntelligenceController::class, 'workflow'])->name('ai.intelligence.workflow');

    // Design Studio
    Route::get('ai/design', [\App\Modules\Planner\Controllers\DesignController::class, 'index'])->name('ai.design');
    Route::get('ai/design/{blueprint}', [\App\Modules\Planner\Controllers\DesignController::class, 'show'])->name('ai.design.show');
    Route::post('ai/design/{blueprint}/generate', [\App\Modules\Planner\Controllers\DesignController::class, 'generate'])->name('ai.design.generate');
    Route::get('ai/design/{blueprint}/generate', fn($blueprint) => redirect()->route('ai.design.show', $blueprint));
    Route::get('ai/design/{blueprint}/status', [\App\Modules\Planner\Controllers\DesignController::class, 'status'])->name('ai.design.status');

    // Assignment Bank (teacher manages templates)
    Route::get('classroom/bank', [\App\Modules\Classroom\Controllers\AssignmentBankController::class, 'index'])->name('classroom.bank.index');
    Route::post('classroom/bank', [\App\Modules\Classroom\Controllers\AssignmentBankController::class, 'store'])->name('classroom.bank.store');
    Route::delete('classroom/bank/{template}', [\App\Modules\Classroom\Controllers\AssignmentBankController::class, 'destroy'])->name('classroom.bank.destroy');

    // Assignment Release & Grading (teacher-only actions)
    Route::post('classroom/assignments', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'store'])->name('classroom.assignments.store');
    Route::patch('classroom/assignments/{assignment}/close', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'close'])->name('classroom.assignments.close');
    Route::get('classroom/assignments/{assignment}/submissions', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'submissions'])->name('classroom.assignments.submissions');
    Route::patch('classroom/submissions/{submission}/grade', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'grade'])->name('classroom.submissions.grade');
    Route::delete('classroom/submissions/{submission}', [\App\Modules\Classroom\Controllers\AssignmentController::class, 'deleteSubmission'])->name('classroom.submissions.delete');


    // Materials Vault write actions
    Route::post('classroom/materials', [\App\Modules\Classroom\Controllers\MaterialsController::class, 'store'])->name('classroom.materials.store');
    Route::delete('classroom/materials/{material}', [\App\Modules\Classroom\Controllers\MaterialsController::class, 'destroy'])->name('classroom.materials.destroy');

    // Student Roster
    Route::get('classroom/students', [\App\Modules\Classroom\Controllers\StudentsController::class, 'index'])->name('classroom.students.index');
    Route::post('classroom/students', [\App\Modules\Classroom\Controllers\StudentsController::class, 'store'])->name('classroom.students.store');
    Route::delete('classroom/students/{user}', [\App\Modules\Classroom\Controllers\StudentsController::class, 'destroy'])->name('classroom.students.destroy');

    // Attendance Register
    Route::get('classroom/attendance', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'index'])->name('classroom.attendance.index');
    Route::post('classroom/attendance', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'store'])->name('classroom.attendance.store');

    // Attendance Sessions (geofenced self-check-in)
    Route::post('classroom/attendance/sessions', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'openSession'])->name('classroom.attendance.sessions.open');
    Route::delete('classroom/attendance/sessions/{session}', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'closeSession'])->name('classroom.attendance.sessions.close');
    Route::get('classroom/attendance/sessions/{session}/status', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'sessionStatus'])->name('classroom.attendance.sessions.status');
});

// ── Student self check-in (requires student to be logged in) ────────────────
Route::middleware('auth')->group(function () {
    Route::get('checkin/{token}', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'checkinPage'])->name('classroom.checkin.page');
    Route::post('checkin/{token}', [\App\Modules\Classroom\Controllers\AttendanceController::class, 'checkin'])->name('classroom.checkin.submit');
});

// ── Public calendar endpoints ─────────────────────────────────────────────────
Route::get('calendar/setup', [\App\Http\Controllers\CalendarSyncController::class, 'setup'])->name('calendar.setup');
Route::get('calendar/{user}/sync.ics', [\App\Http\Controllers\CalendarSyncController::class, 'sync'])->name('calendar.sync');
Route::get('api/tasks/today/{user}', [\App\Http\Controllers\CalendarSyncController::class, 'tasksToday'])->name('api.tasks.today');

require __DIR__.'/auth.php';
