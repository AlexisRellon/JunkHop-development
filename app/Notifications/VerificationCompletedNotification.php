<?php

namespace App\Notifications;

use App\Models\QualityVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerificationCompletedNotification extends Notification implements ShouldQueue
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
        $junkshopName = $this->verification->junkshop->name;
        $quantity = $this->verification->quantity;
        $code = $this->verification->verification_code;
        $status = ucfirst($this->verification->status);
        $grade = $this->verification->grade ?? 'Not specified';
        
        $mailMessage = (new MailMessage)
            ->subject("Material Verification {$status}: {$itemName}")
            ->greeting("Hello {$notifiable->name},");
            
        if ($this->verification->isPassed()) {
            $mailMessage->line("Good news! Your material verification request has been approved.")
                ->line("**Verification Results:**")
                ->line("- Material: {$itemName}")
                ->line("- Quantity: {$quantity} kg")
                ->line("- Grade: {$grade}")
                ->line("- Verification Code: {$code}")
                ->line("- Verified by: {$junkshopName}");
                
            if ($this->verification->purity_level) {
                $mailMessage->line("- Purity Level: {$this->verification->purity_level}%");
            }
            
        } elseif ($this->verification->isFailed()) {
            $mailMessage->line("Your material verification request for {$itemName} did not meet the required quality standards.")
                ->line("**Verification Results:**")
                ->line("- Material: {$itemName}")
                ->line("- Quantity: {$quantity} kg")
                ->line("- Grade: {$grade}")
                ->line("- Verification Code: {$code}")
                ->line("- Verified by: {$junkshopName}");
                
            if ($this->verification->purity_level) {
                $mailMessage->line("- Purity Level: {$this->verification->purity_level}%");
            }
            
            if ($this->verification->notes) {
                $mailMessage->line("- Reason: {$this->verification->notes}");
            }
            
        } else {
            $mailMessage->line("Your material verification request for {$itemName} is now being processed by {$junkshopName}.")
                ->line("**Verification Details:**")
                ->line("- Material: {$itemName}")
                ->line("- Quantity: {$quantity} kg")
                ->line("- Verification Code: {$code}")
                ->line("- Status: In Progress");
        }
        
        return $mailMessage
            ->action('View Verification Details', url("/dashboard/merchant/verifications/{$this->verification->ulid}"))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = "";
        
        if ($this->verification->isPassed()) {
            $message = "Your material verification for {$this->verification->item->name} has been approved.";
        } elseif ($this->verification->isFailed()) {
            $message = "Your material verification for {$this->verification->item->name} did not meet the required quality standards.";
        } else {
            $message = "Your material verification for {$this->verification->item->name} is now being processed.";
        }
        
        return [
            'verification_id' => $this->verification->id,
            'verification_ulid' => $this->verification->ulid,
            'verification_code' => $this->verification->verification_code,
            'junkshop_id' => $this->verification->junkshop_id,
            'junkshop_name' => $this->verification->junkshop->name,
            'item_id' => $this->verification->item_id,
            'item_name' => $this->verification->item->name,
            'quantity' => $this->verification->quantity,
            'status' => $this->verification->status,
            'grade' => $this->verification->grade,
            'purity_level' => $this->verification->purity_level,
            'type' => 'verification_completed',
            'message' => $message
        ];
    }
}
