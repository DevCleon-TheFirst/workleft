<?php

namespace App\Observers;

use App\Modules\Meetings\Models\Meeting;
use App\Jobs\DispatchStartingPing;
use App\Jobs\DispatchTimeUpChime;

class MeetingObserver
{
    /**
     * Handle the Meeting "created" event.
     */
    public function created(Meeting $meeting): void
    {
        $this->scheduleNotifications($meeting);
    }

    /**
     * Handle the Meeting "updated" event.
     */
    public function updated(Meeting $meeting): void
    {
        $this->scheduleNotifications($meeting);
    }

    protected function scheduleNotifications(Meeting $meeting): void
    {
        if ($meeting->status !== 'upcoming') return;

        if ($meeting->scheduled_at) {
            // Dispatch warning 5 minutes before start
            $startPingTime = $meeting->scheduled_at->copy()->subMinutes(5);
            $delay = now()->diffInSeconds($startPingTime, false);
            if ($delay >= 0) {
                DispatchStartingPing::dispatch('meeting', $meeting->id, $meeting->title, $meeting->scheduled_at->toIso8601String())
                    ->delay($delay);
            }

            // Dispatch due time up 1 hour after start (assumed duration)
            $endTime = $meeting->scheduled_at->copy()->addHour();
            $delay = now()->diffInSeconds($endTime, false);
            if ($delay >= 0) {
                DispatchTimeUpChime::dispatch('meeting', $meeting->id, $meeting->title, $meeting->scheduled_at->toIso8601String())
                    ->delay($delay);
            }
        }
    }

    /**
     * Handle the Meeting "deleted" event.
     */
    public function deleted(Meeting $meeting): void
    {
        //
    }

    /**
     * Handle the Meeting "restored" event.
     */
    public function restored(Meeting $meeting): void
    {
        //
    }

    /**
     * Handle the Meeting "force deleted" event.
     */
    public function forceDeleted(Meeting $meeting): void
    {
        //
    }
}
