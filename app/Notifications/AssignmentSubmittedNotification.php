<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentSubmittedNotification extends Notification
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
            ->subject('New Submission: ' . $this->submission->assignment->template->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->submission->student->name . ' has submitted their assignment for **' . $this->submission->assignment->template->title . '**.')
            ->action('View Submission', url('/classroom/assignments/' . $this->submission->assignment_id . '/submissions'))
            ->line('Please review and grade it when you have a moment.');
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
