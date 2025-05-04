<?php

namespace App\Notifications;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $activity;

    /**
     * Create a new notification instance.
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = "CleanSnap Notification: " . ucfirst($this->activity->type) . " " . ucfirst($this->activity->action);
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($this->activity->description)
            ->line("This update was made on " . $this->activity->created_at->format('M d, Y \a\t h:i A'))
            ->action('View Dashboard', url('/dashboard'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'activity_id' => $this->activity->id,
            'type' => $this->activity->type,
            'action' => $this->activity->action,
            'description' => $this->activity->description,
            'subject_type' => $this->activity->subject_type,
            'subject_id' => $this->activity->subject_id,
            'timestamp' => $this->activity->created_at->diffForHumans(),
        ];
    }
}
