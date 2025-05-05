<?php

namespace App\Notifications;

use App\Models\BidSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewalUpcomingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;

    /**
     * Create a new notification instance.
     */
    public function __construct(BidSubscription $subscription)
    {
        $this->subscription = $subscription;
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
        $itemName = $this->subscription->item->name;
        $junkshopName = $this->subscription->junkshop->name;
        $renewalDate = $this->subscription->next_renewal_date->format('M d, Y');
        $totalValue = number_format($this->subscription->quantity * $this->subscription->price_per_kg, 2);
        
        return (new MailMessage)
            ->subject("Upcoming Bid Renewal: {$itemName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder that your subscription bid for {$itemName} from {$junkshopName} is scheduled for renewal on {$renewalDate}.")
            ->line("**Upcoming Bid Details:**")
            ->line("- Quantity: {$this->subscription->quantity} kg")
            ->line("- Price: ₱{$this->subscription->price_per_kg} per kg")
            ->line("- Total value: ₱{$totalValue}")
            ->line("- Frequency: " . ucfirst($this->subscription->frequency))
            ->action('Manage Subscription', url("/dashboard/merchant/subscriptions/{$this->subscription->ulid}"))
            ->line('If you wish to modify or cancel this subscription, please do so before the renewal date.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'subscription_ulid' => $this->subscription->ulid,
            'item_id' => $this->subscription->item_id,
            'item_name' => $this->subscription->item->name,
            'junkshop_name' => $this->subscription->junkshop->name,
            'quantity' => $this->subscription->quantity,
            'price_per_kg' => $this->subscription->price_per_kg,
            'total_value' => $this->subscription->quantity * $this->subscription->price_per_kg,
            'renewal_date' => $this->subscription->next_renewal_date->format('Y-m-d'),
            'type' => 'upcoming_renewal',
            'message' => "Your subscription bid for {$this->subscription->item->name} is scheduled for renewal on {$this->subscription->next_renewal_date->format('M d, Y')}."
        ];
    }
}
