<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyAttendanceSummary extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $students;
    public $sessionCount;

    public function __construct(array $students, int $sessionCount)
    {
        $this->students = $students;
        $this->sessionCount = $sessionCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Weekly Attendance Summary')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Here is your attendance summary for the past 7 days.')
            ->line('**Total Sessions Hosted:** ' . $this->sessionCount);

        if (empty($this->students)) {
            $mail->line('No students attended your sessions this week.');
        } else {
            $mail->line('**Students who attended:**');
            foreach ($this->students as $student) {
                $mail->line('- ' . $student['name'] . ' (' . $student['email'] . ') - Attended ' . $student['count'] . ' times');
            }
        }

        return $mail->action('View Dashboard', url('/dashboard'))
                    ->line('Have a great weekend!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
