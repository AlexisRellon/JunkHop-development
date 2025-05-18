<?php

namespace App\Notifications;

use App\Models\Bid;
use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BidEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bid;
    protected $winner;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bid $bid, ?Merchant $winner)
    {
        $this->bid = $bid;
        $this->winner = $winner;
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
        
        $mail = (new MailMessage)
            ->subject("Bidding Has Ended for {$itemName}");
            
        if ($this->winner) {
            $winnerName = $this->winner->user->name ?? 'A merchant';
            
            $mail->greeting("Hello {$notifiable->name}!")
                ->line("The bidding period for {$itemName} has ended.")
                ->line("Winning bid: ₱{$this->bid->current_bid} by {$winnerName}")
                ->line("Quantity: {$this->bid->quantity} kg")
                ->line("Total amount: ₱" . number_format($this->bid->current_bid * $this->bid->quantity, 2))
                ->action('View Bid Details', url("/dashboard/junkshop/bids/{$this->bid->ulid}"))
                ->line("Please contact the winning bidder to complete the transaction.");
        } else {
            $mail->greeting("Hello {$notifiable->name}!")
                ->line("The bidding period for {$itemName} has ended.")
                ->line("Unfortunately, no one placed a bid on this item.")
                ->line("You may want to create a new listing with an adjusted price or conditions.")
                ->action('View Bid Details', url("/dashboard/junkshop/bids/{$this->bid->ulid}"));
        }
        
        return $mail->line('Thank you for using JunkHop!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $itemName = $this->bid->item->name ?? 'your item';
        
        if ($this->winner) {
            $winnerName = $this->winner->user->name ?? 'A merchant';
            $message = "Bidding ended for {$itemName}. Winning bid: ₱{$this->bid->current_bid} by {$winnerName}.";
        } else {
            $message = "Bidding ended for {$itemName}. No bids were placed.";
        }
        
        return [
            'bid_id' => $this->bid->ulid,
            'item_name' => $itemName,
            'bid_amount' => $this->bid->current_bid,
            'winner_id' => $this->winner ? $this->winner->ulid : null,
            'winner_name' => $this->winner ? $this->winner->user->name : null,
            'message' => $message
        ];
    }
}
