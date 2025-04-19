<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryUpdate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ulid',
        'item_id',
        'previous_quantity',
        'new_quantity',
        'previous_price',
        'new_price',
        'update_type',
        'source',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'previous_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
        'previous_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * Get the item associated with this inventory update.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calculate the quantity difference between new and previous values.
     *
     * @return float|null
     */
    public function getQuantityDifferenceAttribute()
    {
        if ($this->previous_quantity !== null && $this->new_quantity !== null) {
            return $this->new_quantity - $this->previous_quantity;
        }

        return null;
    }

    /**
     * Calculate the price difference between new and previous values.
     *
     * @return float|null
     */
    public function getPriceDifferenceAttribute()
    {
        if ($this->previous_price !== null && $this->new_price !== null) {
            return $this->new_price - $this->previous_price;
        }

        return null;
    }

    /**
     * Determine if this update represents a price increase.
     *
     * @return bool
     */
    public function isPriceIncrease()
    {
        return $this->price_difference > 0;
    }

    /**
     * Determine if this update represents a price decrease.
     *
     * @return bool
     */
    public function isPriceDecrease()
    {
        return $this->price_difference < 0;
    }

    /**
     * Determine if this update represents a quantity increase.
     *
     * @return bool
     */
    public function isQuantityIncrease()
    {
        return $this->quantity_difference > 0;
    }

    /**
     * Determine if this update represents a quantity decrease.
     *
     * @return bool
     */
    public function isQuantityDecrease()
    {
        return $this->quantity_difference < 0;
    }
}
