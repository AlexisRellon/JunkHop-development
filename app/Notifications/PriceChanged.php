<?php

namespace App\Notifications;

use App\Models\Item;
use App\Models\InventoryUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $item;
    protected $update;

    /**
     * Create a new notification instance.
     */
    public function __construct(Item $item, InventoryUpdate $update)
    {
        $this->item = $item;
        $this->update = $update;
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
        $junkshopName = $this->item->junkshop->name;
        $itemName = $this->item->name;
        $previousPrice = $this->update->previous_price;
        $newPrice = $this->update->new_price;
        
        $priceAction = $newPrice < $previousPrice ? 'decreased' : 'increased';
        $priceDifference = abs($newPrice - $previousPrice);
        
        return (new MailMessage)
            ->subject("Price {$priceAction}: {$itemName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$junkshopName} has {$priceAction} the price of {$itemName}.")
            ->line("Previous price: ₱{$previousPrice} per kg")
            ->line("New price: ₱{$newPrice} per kg")
            ->line("Difference: ₱{$priceDifference} per kg")
            ->action('View Material', url("/dashboard/merchant/materials/{$this->item->id}"))
            ->line('This notification was sent based on your material interests.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $priceAction = $this->update->new_price < $this->update->previous_price ? 'decreased' : 'increased';
        
        return [
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'junkshop_id' => $this->item->junkshop_id,
            'junkshop_name' => $this->item->junkshop->name,
            'previous_price' => $this->update->previous_price,
            'new_price' => $this->update->new_price,
            'type' => 'price_changed',
            'message' => "{$this->item->junkshop->name} has {$priceAction} the price of {$this->item->name} from ₱{$this->update->previous_price} to ₱{$this->update->new_price} per kg."
        ];
    }
}
