<?php

namespace App\Notifications;

use App\Models\QualityVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $verification;

    /**
     * Create a new notification instance.
     */
    public function __construct(QualityVerification $verification)
    {
        $this->verification = $verification;
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
        $itemName = $this->verification->item->name;
        $merchantName = $this->verification->merchant->name;
        $quantity = $this->verification->quantity;
        $code = $this->verification->verification_code;
        
        return (new MailMessage)
            ->subject("New Quality Verification Request: {$itemName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$merchantName} has requested a quality verification for {$quantity} kg of {$itemName}.")
            ->line("Verification Code: {$code}")
            ->line("Please review the material and update the verification status.")
            ->action('View Verification Details', url("/dashboard/junkshop/verifications/{$this->verification->ulid}"))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'verification_id' => $this->verification->id,
            'verification_ulid' => $this->verification->ulid,
            'verification_code' => $this->verification->verification_code,
            'merchant_id' => $this->verification->merchant_id,
            'merchant_name' => $this->verification->merchant->name,
            'item_id' => $this->verification->item_id,
            'item_name' => $this->verification->item->name,
            'quantity' => $this->verification->quantity,
            'type' => 'verification_requested',
            'message' => "New quality verification request from {$this->verification->merchant->name} for {$this->verification->quantity} kg of {$this->verification->item->name}."
        ];
    }
}
