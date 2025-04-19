<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'price',
        'grade',
        'is_available',
        'inventory_updated_at',
        'junkshop_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'inventory_updated_at' => 'datetime',
    ];

    /**
     * Get the junkshops that offer this item.
     */
    public function junkshops()
    {
        return $this->belongsToMany(Junkshop::class, 'junkshop_items');
    }

    /**
     * Get the junkshop that directly owns this item.
     */
    public function junkshop()
    {
        return $this->belongsTo(Junkshop::class);
    }

    /**
     * Get the inventory updates for this item.
     */
    public function inventoryUpdates()
    {
        return $this->hasMany(InventoryUpdate::class);
    }

    /**
     * Get merchants who are interested in this item.
     */
    public function interestedMerchants()
    {
        return $this->belongsToMany(Merchant::class, 'merchant_interested_items');
    }

    /**
     * Update the item's inventory quantity and create a record of the change.
     *
     * @param float $newQuantity
     * @param string $updateType
     * @param string|null $source
     * @param string|null $notes
     * @return InventoryUpdate
     */
    public function updateInventory($newQuantity, $updateType, $source = null, $notes = null)
    {
        $previousQuantity = $this->quantity;
        $previousPrice = $this->price;

        // Update the item's quantity
        $this->quantity = $newQuantity;
        $this->is_available = $newQuantity > 0;
        $this->inventory_updated_at = Carbon::now();
        $this->save();

        // Create a record of the inventory update
        return InventoryUpdate::create([
            'item_id' => $this->id,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'previous_price' => $previousPrice,
            'new_price' => $this->price,
            'update_type' => $updateType,
            'source' => $source,
            'notes' => $notes,
        ]);
    }

    /**
     * Update the item's price and create a record of the change.
     *
     * @param float $newPrice
     * @param string|null $source
     * @param string|null $notes
     * @return InventoryUpdate
     */
    public function updatePrice($newPrice, $source = null, $notes = null)
    {
        $previousPrice = $this->price;

        // Update the item's price
        $this->price = $newPrice;
        $this->inventory_updated_at = Carbon::now();
        $this->save();

        // Create a record of the price update
        return InventoryUpdate::create([
            'item_id' => $this->id,
            'previous_quantity' => $this->quantity,
            'new_quantity' => $this->quantity,
            'previous_price' => $previousPrice,
            'new_price' => $newPrice,
            'update_type' => 'price_change',
            'source' => $source,
            'notes' => $notes,
        ]);
    }

    /**
     * Get recent inventory updates for this item.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentUpdates($limit = 10)
    {
        return $this->inventoryUpdates()
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Check if the inventory was recently updated (within the last hour).
     *
     * @return bool
     */
    public function wasRecentlyUpdated()
    {
        return $this->inventory_updated_at && $this->inventory_updated_at->gt(Carbon::now()->subHour());
    }

    /**
     * Scope a query to only include available items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('quantity', '>', 0);
    }
}
