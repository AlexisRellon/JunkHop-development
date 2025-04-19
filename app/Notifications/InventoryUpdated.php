<?php

namespace App\Notifications;

use App\Models\Item;
use App\Models\InventoryUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryUpdated extends Notification implements ShouldQueue
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
        $newQuantity = $this->update->new_quantity;
        
        return (new MailMessage)
            ->subject("Material Restocked: {$itemName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Good news! {$junkshopName} has restocked {$itemName}.")
            ->line("Current available quantity: {$newQuantity} kg")
            ->line("Price: ₱{$this->item->price} per kg")
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
        return [
            'item_id' => $this->item->id,
            'item_name' => $this->item->name,
            'junkshop_id' => $this->item->junkshop_id,
            'junkshop_name' => $this->item->junkshop->name,
            'quantity' => $this->update->new_quantity,
            'previous_quantity' => $this->update->previous_quantity,
            'type' => 'inventory_updated',
            'message' => "{$this->item->junkshop->name} has restocked {$this->item->name}. New quantity: {$this->update->new_quantity} kg."
        ];
    }
}
