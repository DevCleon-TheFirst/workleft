<?php

namespace App\Modules\Classroom\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Classroom\Models\Attendance;
use App\Modules\Classroom\Models\AttendanceSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // TEACHER: Attendance register view
    // ──────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $date    = $request->get('date', now()->toDateString());
        $session = $request->get('session', '');

        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $records = Attendance::where('date', $date)
            ->when($session, fn($q) => $q->where('session_label', $session))
            ->when(!$session, fn($q) => $q->whereNull('session_label'))
            ->get()
            ->keyBy('student_id');

        $roster = $students->map(fn($s) => [
            'id'     => $s->id,
            'name'   => $s->name,
            'email'  => $s->email,
            'status' => $records->get($s->id)?->status ?? null,
            'note'   => $records->get($s->id)?->note ?? '',
            'method' => $records->get($s->id)?->method ?? null,
            'distance_meters' => $records->get($s->id)?->distance_meters ?? null,
        ]);

        // Active session for today (if any)
        $activeSession = AttendanceSession::where('date', $date)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $pastSessions = Attendance::selectRaw(
            'date, session_label,
             COUNT(*) as total,
             SUM(status = "present") as present_count,
             SUM(status = "absent") as absent_count,
             SUM(status = "late") as late_count'
        )
            ->groupBy('date', 'session_label')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        return Inertia::render('Classroom/Attendance/Index', [
            'roster'        => $roster,
            'date'          => $date,
            'session'       => $session,
            'pastSessions'  => $pastSessions,
            'activeSession' => $activeSession ? [
                'id'             => $activeSession->id,
                'token'          => $activeSession->token,
                'expires_at'     => $activeSession->expires_at->toIso8601String(),
                'radius_meters'  => $activeSession->radius_meters,
                'session_label'  => $activeSession->session_label,
                'latitude'       => $activeSession->latitude,
                'longitude'      => $activeSession->longitude,
                'checkin_url'    => url("/checkin/{$activeSession->token}"),
            ] : null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TEACHER: Manual bulk-save
    // ──────────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'date'                 => 'required|date',
            'session'              => 'nullable|string|max:100',
            'records'              => 'required|array|min:1',
            'records.*.student_id' => 'required|exists:users,id',
            'records.*.status'     => 'required|in:present,absent,late',
            'records.*.note'       => 'nullable|string|max:500',
        ]);

        foreach ($request->records as $row) {
            Attendance::updateOrCreate(
                [
                    'student_id'    => $row['student_id'],
                    'date'          => $request->date,
                    'session_label' => $request->session ?: null,
                ],
                [
                    'marked_by' => $request->user()->id,
                    'status'    => $row['status'],
                    'note'      => $row['note'] ?? null,
                    'method'    => 'manual',
                ]
            );
        }

        return back()->with('success', 'Attendance saved.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TEACHER: Open a self-check-in session with geofence
    // ──────────────────────────────────────────────────────────────────────────

    public function openSession(Request $request)
    {
        $request->validate([
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:20|max:1000',
            'duration_mins' => 'required|integer|min:5|max:240',
            'session_label' => 'nullable|string|max:100',
            'date'          => 'required|date',
        ]);

        // Close any previous open session for today
        AttendanceSession::where('teacher_id', $request->user()->id)
            ->where('date', $request->date)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $session = AttendanceSession::create([
            'teacher_id'    => $request->user()->id,
            'date'          => $request->date,
            'session_label' => $request->session_label,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'radius_meters' => $request->radius_meters,
            'expires_at'    => now()->addMinutes($request->duration_mins),
            'token'         => Str::random(32),
            'is_active'     => true,
        ]);

        return response()->json([
            'id'          => $session->id,
            'token'       => $session->token,
            'expires_at'  => $session->expires_at->toIso8601String(),
            'checkin_url' => url("/checkin/{$session->token}"),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TEACHER: Close session early
    // ──────────────────────────────────────────────────────────────────────────

    public function closeSession(AttendanceSession $session)
    {
        $session->update(['is_active' => false]);
        return response()->json(['ok' => true]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TEACHER: Poll live check-in count
    // ──────────────────────────────────────────────────────────────────────────

    public function sessionStatus(AttendanceSession $session)
    {
        $checkedIn = Attendance::where('session_id', $session->id)->get(['student_id', 'status']);
        $students  = User::where('role', 'student')->get(['id', 'name']);

        return response()->json([
            'is_open'     => $session->isOpen(),
            'expires_at'  => $session->expires_at->toIso8601String(),
            'checked_in'  => $checkedIn->count(),
            'total'       => $students->count(),
            'roster'      => $students->map(fn($s) => [
                'id'     => $s->id,
                'name'   => $s->name,
                'status' => $checkedIn->firstWhere('student_id', $s->id)?->status ?? null,
            ]),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STUDENT: Poll for active session (called from StudentDashboard every 10s)
    // ──────────────────────────────────────────────────────────────────────────

    public function activeSession(Request $request)
    {
        $session = AttendanceSession::where('is_active', true)
            ->where('expires_at', '>', now())
            ->where('date', now()->toDateString())
            ->latest()
            ->first();

        if (!$session) {
            return response()->json(['active' => false]);
        }

        $alreadyCheckedIn = Attendance::where('session_id', $session->id)
            ->where('student_id', $request->user()->id)
            ->exists();

        return response()->json([
            'active'        => true,
            'token'         => $session->token,
            'session_label' => $session->session_label,
            'radius_meters' => $session->radius_meters,
            'expires_at'    => $session->expires_at->toIso8601String(),
            'already_done'  => $alreadyCheckedIn,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STUDENT: Check-in page
    // ──────────────────────────────────────────────────────────────────────────

    public function checkinPage(string $token)
    {
        $session = AttendanceSession::where('token', $token)->firstOrFail();

        // Check if student already checked in
        $existing = null;
        if (auth()->check()) {
            $existing = Attendance::where('session_id', $session->id)
                ->where('student_id', auth()->id())
                ->first();
        }

        return Inertia::render('Classroom/Attendance/CheckIn', [
            'sessionToken'  => $token,
            'sessionLabel'  => $session->session_label,
            'date'          => $session->date->toDateString(),
            'isOpen'        => $session->isOpen(),
            'radiusMeters'  => $session->radius_meters,
            'expiresAt'     => $session->expires_at->toIso8601String(),
            'alreadyDone'   => $existing !== null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STUDENT: Submit GPS and record attendance
    // ──────────────────────────────────────────────────────────────────────────

    public function checkin(Request $request, string $token)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric',
        ]);

        $session = AttendanceSession::where('token', $token)->firstOrFail();

        if (!$session->isOpen()) {
            return response()->json(['ok' => false, 'error' => 'This check-in session has expired or been closed.'], 422);
        }

        // Reject very poor accuracy (> 100 meters)
        if ($request->filled('accuracy') && $request->accuracy > 100) {
            return response()->json([
                'ok'    => false, 
                'error' => "Your phone's GPS signal is too weak (Accuracy: {$request->accuracy}m). Please connect to Wi-Fi or step outside briefly and try again."
            ], 422);
        }

        // Reject suspiciously perfect accuracy (<= 1.0 meters) - common in Fake GPS apps
        if ($request->filled('accuracy') && $request->accuracy <= 1.0) {
            return response()->json([
                'ok'    => false, 
                'error' => "Suspicious GPS signal detected. Please disable any location-spoofing software and try again."
            ], 422);
        }

        $student = $request->user();

        // Already checked in?
        if (Attendance::where('session_id', $session->id)->where('student_id', $student->id)->exists()) {
            return response()->json(['ok' => false, 'error' => 'You have already checked in for this session.'], 422);
        }

        // Geofence check using Haversine
        $distance = AttendanceSession::distanceMeters(
            $session->latitude, $session->longitude,
            $request->latitude, $request->longitude
        );

        if ($distance > $session->radius_meters) {
            return response()->json([
                'ok'       => false,
                'error'    => "You are {$distance}m away from class. You must be within {$session->radius_meters}m to check in.",
                'distance' => $distance,
            ], 422);
        }

        Attendance::updateOrCreate(
            [
                'student_id'    => $student->id,
                'date'          => $session->date->toDateString(),
                'session_label' => $session->session_label,
            ],
            [
                'marked_by'       => $session->teacher_id,
                'status'          => 'present',
                'session_id'      => $session->id,
                'student_lat'     => $request->latitude,
                'student_lng'     => $request->longitude,
                'distance_meters' => $distance,
                'method'          => 'self_checkin',
                'note'            => "Self check-in ({$distance}m from class)",
            ]
        );

        return response()->json([
            'ok'       => true,
            'distance' => $distance,
            'message'  => "You're checked in! You were {$distance}m from the classroom.",
        ]);
    }
}
