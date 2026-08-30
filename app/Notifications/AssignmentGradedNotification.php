<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentGradedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $submission;

    public function __construct(\App\Modules\Classroom\Models\Submission $submission)
    {
        $this->submission = $submission;
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
        return (new MailMessage)
            ->subject('Assignment Graded: ' . $this->submission->assignment->template->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your submission for **' . $this->submission->assignment->template->title . '** has been graded.')
            ->line('**Your Score:** ' . $this->submission->score . '/100')
            ->action('View Assignment', url('/classroom/assignments'))
            ->line('Keep up the good work!');
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
