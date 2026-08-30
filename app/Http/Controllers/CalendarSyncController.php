<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Tasks\Models\Task;
use App\Modules\Meetings\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Response;

class CalendarSyncController extends Controller
{
    /**
     * Generate the webcal:// link for the authenticated user.
     */
    public function link(Request $request)
    {
        // Link to the setup page instead of directly to webcal
        $setupUrl = URL::signedRoute('calendar.setup', ['user' => $request->user()->id]);
        
        return response()->json(['url' => $setupUrl]);
    }

    /**
     * Render the setup landing page for mobile devices.
     */
    public function setup(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid or expired calendar link.');
        }

        $userId = $request->input('user') ?? $request->route('user');
        
        $icsUrl = URL::signedRoute('calendar.sync', ['user' => $userId]);
        $webcalUrl = str_replace(['http://', 'https://'], 'webcal://', $icsUrl);

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sync Cleon Calendar</title>
    <style>
        body { font-family: -apple-system, system-ui, sans-serif; text-align: center; padding: 2rem 1rem; background: #111827; color: #fff; margin: 0; }
        .card { background: #1f2937; padding: 2rem; border-radius: 1rem; max-width: 400px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .btn { display: block; padding: 1rem 1.5rem; margin: 1rem 0 0.5rem 0; border-radius: 0.75rem; text-decoration: none; font-weight: bold; font-size: 1.1rem; transition: opacity 0.2s; }
        .btn:active { opacity: 0.8; }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-secondary { background: #374151; color: white; border: 1px solid #4b5563; }
        .desc { font-size: 0.85rem; color: #9ca3af; margin-top: 0; }
        .divider { margin: 2rem 0; border-top: 1px solid #374151; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="margin-top: 0;">Your Calendar is Ready</h2>
        <p style="color: #d1d5db; margin-bottom: 2rem; line-height: 1.5;">Choose how you want to sync your Tasks and Meetings to your phone's native calendar.</p>
        
        <a href="{$webcalUrl}" class="btn btn-primary">Subscribe (Auto-Sync)</a>
        <p class="desc">Best option. New tasks will automatically appear on your phone.</p>
        
        <div class="divider"></div>
        
        <a href="{$icsUrl}" class="btn btn-secondary">Download File (One-Time)</a>
        <p class="desc">Use this if Subscribe fails on your local network. Only syncs current tasks.</p>
    </div>
</body>
</html>
HTML;

        return response($html);
    }

    /**
     * Generate the ICS feed.
     */
    public function sync(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Invalid or expired calendar link.');
        }

        // Fetch Tasks (that have a due_date and are not done)
        $tasks = Task::where('assignee_id', $user->id)
            ->whereNotNull('due_date')
            ->where('status', '!=', 'done')
            ->get();

        // Fetch Meetings (all upcoming)
        $meetings = Meeting::whereNotNull('scheduled_at')
            ->where('status', '!=', 'past')
            ->get();

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Cleon Innovations//Antigravity IDE//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "X-WR-CALNAME:Cleon Tasks & Meetings\r\n";
        $ics .= "X-WR-TIMEZONE:" . config('app.timezone') . "\r\n";

        $now = gmdate('Ymd\THis\Z');

        // Map Tasks
        foreach ($tasks as $task) {
            // Treat the due_date as the exact time of the event (0-duration event)
            $dtstart = $task->due_date->timezone('UTC')->format('Ymd\THis\Z');
            $dtend = $dtstart;
            
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:task-{$task->id}@cleon.local\r\n";
            $ics .= "DTSTAMP:{$now}\r\n";
            $ics .= "DTSTART:{$dtstart}\r\n";
            $ics .= "DTEND:{$dtend}\r\n";
            $ics .= "SUMMARY:Task Due: {$task->title}\r\n";
            $ics .= "STATUS:CONFIRMED\r\n";
            
            // Add Alarm exactly at due time
            $ics .= "BEGIN:VALARM\r\n";
            $ics .= "TRIGGER:-PT0M\r\n";
            $ics .= "ACTION:DISPLAY\r\n";
            $ics .= "DESCRIPTION:Task time is up: {$task->title}\r\n";
            $ics .= "END:VALARM\r\n";
            
            // Add Alarm 10 minutes before due time
            $ics .= "BEGIN:VALARM\r\n";
            $ics .= "TRIGGER:-PT10M\r\n";
            $ics .= "ACTION:DISPLAY\r\n";
            $ics .= "DESCRIPTION:Task time is up in 10 minutes: {$task->title}\r\n";
            $ics .= "END:VALARM\r\n";

            $ics .= "END:VEVENT\r\n";
        }

        // Map Meetings
        foreach ($meetings as $meeting) {
            $dtstart = $meeting->scheduled_at->timezone('UTC')->format('Ymd\THis\Z');
            $dtend = $meeting->scheduled_at->copy()->addHour()->timezone('UTC')->format('Ymd\THis\Z');
            
            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:meeting-{$meeting->id}@cleon.local\r\n";
            $ics .= "DTSTAMP:{$now}\r\n";
            $ics .= "DTSTART:{$dtstart}\r\n";
            $ics .= "DTEND:{$dtend}\r\n";
            $ics .= "SUMMARY:Meeting: {$meeting->title}\r\n";
            $ics .= "STATUS:CONFIRMED\r\n";
            
            $ics .= "BEGIN:VALARM\r\n";
            $ics .= "TRIGGER:-PT0M\r\n";
            $ics .= "ACTION:DISPLAY\r\n";
            $ics .= "DESCRIPTION:Meeting starting now: {$meeting->title}\r\n";
            $ics .= "END:VALARM\r\n";
            $ics .= "BEGIN:VALARM\r\n";
            $ics .= "TRIGGER:-PT10M\r\n";
            $ics .= "ACTION:DISPLAY\r\n";
            $ics .= "DESCRIPTION:Meeting starting in 10 minutes: {$meeting->title}\r\n";
            $ics .= "END:VALARM\r\n";

            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type'  => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="cleon-calendar.ics"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * API for iOS Shortcuts to fetch tasks and create real Alarms.
     */
    public function tasksToday(Request $request, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Unauthorized access.');
        }

        // Fetch tasks due today or in the future
        $tasks = Task::where('assignee_id', $user->id)
            ->whereNotNull('due_date')
            ->where('status', '!=', 'done')
            ->whereDate('due_date', '>=', now()->toDateString())
            ->orderBy('due_date', 'asc')
            ->get();

        $output = [];
        foreach ($tasks as $task) {
            $output[] = [
                'title' => $task->title,
                // Return an easy format for Apple Shortcuts to parse (ISO 8601)
                'due_date' => $task->due_date->toIso8601String(),
                // Or a human readable exact time for the alarm
                'time' => $task->due_date->format('g:i A'),
                'date' => $task->due_date->format('Y-m-d')
            ];
        }

        return response()->json([
            'success' => true,
            'count' => count($output),
            'tasks' => $output
        ]);
    }
}
