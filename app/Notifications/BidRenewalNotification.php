<?php

namespace App\Notifications;

use App\Models\BidSubscription;
use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class BidRenewalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;
    protected $newBid;

    /**
     * Create a new notification instance.
     */
    public function __construct(BidSubscription $subscription, Bid $newBid)
    {
        $this->subscription = $subscription;
        $this->newBid = $newBid;
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
        $totalValue = number_format($this->newBid->quantity * $this->newBid->price_per_kg, 2);
        $nextRenewal = $this->subscription->next_renewal_date->format('M d, Y');
        
        return (new MailMessage)
            ->subject("Bid Automatically Renewed: {$itemName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your subscription bid for {$itemName} from {$junkshopName} has been automatically renewed.")
            ->line("**Bid Details:**")
            ->line("- Quantity: {$this->newBid->quantity} kg")
            ->line("- Price: ₱{$this->newBid->price_per_kg} per kg")
            ->line("- Total value: ₱{$totalValue}")
            ->line("- Next scheduled renewal: {$nextRenewal}")
            ->action('View Bid Details', url("/dashboard/merchant/bidding/{$this->newBid->ulid}"))
            ->line('You can manage your subscription settings at any time in your account dashboard.');
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
            'bid_ulid' => $this->newBid->ulid,
            'item_id' => $this->subscription->item_id,
            'item_name' => $this->subscription->item->name,
            'junkshop_name' => $this->subscription->junkshop->name,
            'quantity' => $this->newBid->quantity,
            'price_per_kg' => $this->newBid->price_per_kg,
            'total_value' => $this->newBid->quantity * $this->newBid->price_per_kg,
            'next_renewal_date' => $this->subscription->next_renewal_date->format('Y-m-d'),
            'renewals_count' => $this->subscription->renewals_count,
            'type' => 'bid_renewed',
            'message' => "Your subscription bid for {$this->subscription->item->name} has been automatically renewed."
        ];
    }
}
