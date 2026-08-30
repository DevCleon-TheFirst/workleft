<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $assignment;

    public function __construct(\App\Modules\Classroom\Models\Assignment $assignment)
    {
        $this->assignment = $assignment;
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
        $dueDate = $this->assignment->due_date ? $this->assignment->due_date->format('M d, Y h:i A') : 'No deadline';
        
        return (new MailMessage)
            ->subject('New Assignment: ' . $this->assignment->template->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new assignment has been posted for your class: **' . $this->assignment->template->title . '**.')
            ->line('**Due Date:** ' . $dueDate)
            ->action('View Assignments', url('/classroom/assignments'))
            ->line('Good luck!');
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
