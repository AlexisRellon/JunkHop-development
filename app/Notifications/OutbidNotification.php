<?php

namespace App\Notifications;

use App\Models\Bid;
use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OutbidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bid;
    protected $newBidder;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bid $bid, Merchant $newBidder)
    {
        $this->bid = $bid;
        $this->newBidder = $newBidder;
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
        $itemName = $this->bid->item->name ?? 'the item';
        
        return (new MailMessage)
            ->subject("You've Been Outbid on {$itemName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Someone has placed a higher bid on {$itemName}.")
            ->line("New highest bid: ₱{$this->bid->current_bid}")
            ->line("This bid will end on: " . $this->bid->end_date->format('M d, Y, h:i A'))
            ->action('Place a New Bid', url("/dashboard/merchant/material-marketplace?bid={$this->bid->ulid}"))
            ->line('Act quickly to secure this item!')
            ->line('Thank you for using JunkHop!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $itemName = $this->bid->item->name ?? 'the item';
        
        return [
            'bid_id' => $this->bid->ulid,
            'item_name' => $itemName,
            'bid_amount' => $this->bid->current_bid,
            'message' => "You've been outbid on {$itemName}. Current highest bid: ₱{$this->bid->current_bid}"
        ];
    }
}
