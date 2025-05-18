<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

class Bid extends Model
{
    use HasFactory;/**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ulid',
        'merchant_id',
        'junkshop_id',
        'item_id',
        'wanted_material_id',
        'quantity',
        'price_per_kg',
        'starting_bid',
        'current_bid',
        'current_bidder_id',
        'notes',
        'expiry_date',
        'start_date',
        'end_date',
        'status',
        'rejection_reason',
        'is_bulk_order',
        'is_bidding_enabled',
        'bidding_processed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'float',
        'price_per_kg' => 'float',
        'starting_bid' => 'float',
        'current_bid' => 'float',
        'expiry_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_bulk_order' => 'boolean',
        'is_bidding_enabled' => 'boolean',
        'bidding_processed' => 'boolean',
    ];    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Don't auto-generate ULIDs since we're providing them from the frontend
        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = \Illuminate\Support\Str::ulid()->toBase32();
            }
        });

        static::creating(function ($model) {
            if (empty($model->ulid)) {
                // Use Symfony's ULID implementation to ensure proper format
                $model->ulid = (new Ulid())->toBase32();
            }
        });
    }

    /**
     * Get the merchant that owns the bid.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'ulid');
    }

    /**
     * Get the junkshop that the bid is directed to.
     */
    public function junkshop()
    {
        return $this->belongsTo(Junkshop::class, 'junkshop_id', 'ulid');
    }

    /**
     * Get the item being bid on.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the wanted material associated with this bid, if any.
     */
    public function wantedMaterial()
    {
        return $this->belongsTo(WantedMaterial::class);
    }
    
    /**
     * Get the bid history for this bid.
     */
    public function history(): HasMany
    {
        return $this->hasMany(BidHistory::class);
    }

    /**
     * Scope a query to only include pending bids.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include accepted bids.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include active bids (not expired, rejected or completed).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'accepted'])
                    ->where(function($query) {
                        $query->whereNull('expiration_date')
                            ->orWhere('expiration_date', '>=', now());
                    });
    }

    /**
     * Check if bidding is currently active for this bid.
     *
     * @return bool
     */
    public function isBiddingActive(): bool
    {
        if (!$this->is_bidding_enabled || !$this->start_date || !$this->end_date) {
            return false;
        }
        
        $now = now();
        return $now >= $this->start_date && $now <= $this->end_date;
    }

    /**
     * Calculate the minimum bid amount based on the JunkHop algorithm.
     *
     * @return float
     */
    public function calculateMinimumBid(): float
    {
        $startingBid = $this->starting_bid ?? $this->price_per_kg;
        $currentBid = $this->current_bid ?? 0;
        
        if ($currentBid == 0) {
            return $startingBid;
        }
        
        // JunkHop algorithm: Current bid + 5% increment
        return $currentBid * 1.05;
    }
}
