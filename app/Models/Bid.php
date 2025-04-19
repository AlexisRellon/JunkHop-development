<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bid extends Model
{
    use HasFactory;    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ulid',
        'merchant_id',
        'junkshop_id',
        'item_id',
        'quantity',
        'price_per_kg',
        'grade',
        'notes',
        'expiration_date',
        'status',
        'accepted_at',
        'rejected_at',
        'wanted_material_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiration_date' => 'date',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
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
     * Calculate the total value of the bid.
     *
     * @return float
     */
    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->price_per_unit;
    }

    /**
     * Find a bid by its ULID
     *
     * @param string $ulid
     * @return static|null
     */
    public static function findByUlid(string $ulid)
    {
        return static::where('ulid', $ulid)->first();
    }
}
