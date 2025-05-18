<?php

namespace App\Notifications;

use App\Models\Bid;
use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBidPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bid;
    protected $bidder;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bid $bid, Merchant $bidder)
    {
        $this->bid = $bid;
        $this->bidder = $bidder;
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
        $itemName = $this->bid->item->name ?? 'your item';
        
        return (new MailMessage)
            ->subject("New Bid Placed on {$itemName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("A new bid of ₱{$this->bid->current_bid} has been placed on your {$itemName} listing.")
            ->line("Current highest bid: ₱{$this->bid->current_bid}")
            ->line("This bid will end on: " . $this->bid->end_date->format('M d, Y, h:i A'))
            ->action('View Bid Details', url("/dashboard/bids/{$this->bid->ulid}"))
            ->line('Thank you for using JunkHop!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $itemName = $this->bid->item->name ?? 'your item';
        
        return [
            'bid_id' => $this->bid->ulid,
            'item_name' => $itemName,
            'bid_amount' => $this->bid->current_bid,
            'bidder_name' => $this->bidder->user->name ?? 'A merchant',
            'message' => "A new bid of ₱{$this->bid->current_bid} has been placed on your {$itemName} listing."
        ];
    }
}
