<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$tasks = \App\Modules\Tasks\Models\Task::where('assignee_id', $user->id)
            ->whereNotNull('due_date')
            ->where('status', '!=', 'done')
            ->get();
$meetings = \App\Modules\Meetings\Models\Meeting::whereNotNull('scheduled_at')
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

foreach ($tasks as $task) {
    $dtstart = $task->due_date->copy()->subMinutes(30)->timezone('UTC')->format('Ymd\THis\Z');
    $dtend = $task->due_date->timezone('UTC')->format('Ymd\THis\Z');
    
    $ics .= "BEGIN:VEVENT\r\n";
    $ics .= "UID:task-{$task->id}@cleon.local\r\n";
    $ics .= "DTSTAMP:{$now}\r\n";
    $ics .= "DTSTART:{$dtstart}\r\n";
    $ics .= "DTEND:{$dtend}\r\n";
    $ics .= "SUMMARY:Task: {$task->title}\r\n";
    $ics .= "STATUS:CONFIRMED\r\n";
    
    $ics .= "BEGIN:VALARM\r\n";
    $ics .= "TRIGGER;RELATED=END:-PT0M\r\n";
    $ics .= "ACTION:DISPLAY\r\n";
    $ics .= "DESCRIPTION:Task time is up: {$task->title}\r\n";
    $ics .= "END:VALARM\r\n";
    
    $ics .= "BEGIN:VALARM\r\n";
    $ics .= "TRIGGER;RELATED=END:-PT10M\r\n";
    $ics .= "ACTION:DISPLAY\r\n";
    $ics .= "DESCRIPTION:Task time is up in 10 minutes: {$task->title}\r\n";
    $ics .= "END:VALARM\r\n";

    $ics .= "END:VEVENT\r\n";
}
$ics .= "END:VCALENDAR\r\n";
echo $ics;
