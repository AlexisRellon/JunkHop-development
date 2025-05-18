<?php

namespace App\Notifications;

use App\Models\Bid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BidWonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bid;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
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
        $junkshopName = $this->bid->junkshop->name ?? 'the junkshop';
        $junkshopContact = $this->bid->junkshop->contact ?? 'Not available';
        
        return (new MailMessage)
            ->subject("Congratulations! You've Won the Bid on {$itemName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Congratulations! You've won the bid on {$itemName} from {$junkshopName}.")
            ->line("Your winning bid: ₱{$this->bid->current_bid}")
            ->line("Quantity: {$this->bid->quantity} kg")
            ->line("Total amount: ₱" . number_format($this->bid->current_bid * $this->bid->quantity, 2))
            ->line("You can contact the junkshop at: {$junkshopContact}")
            ->action('View Bid Details', url("/dashboard/merchant/bids/{$this->bid->ulid}"))
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
        $junkshopName = $this->bid->junkshop->name ?? 'the junkshop';
        
        return [
            'bid_id' => $this->bid->ulid,
            'item_name' => $itemName,
            'junkshop_name' => $junkshopName,
            'bid_amount' => $this->bid->current_bid,
            'message' => "Congratulations! You've won the bid on {$itemName} from {$junkshopName}."
        ];
    }
}
