<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BidSubscription extends Model
{
    use HasFactory;

    /**
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
        'frequency',
        'next_renewal_date',
        'start_date',
        'end_date',
        'max_renewals',
        'renewals_count',
        'is_active',
        'notes',
        'renewal_settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'next_renewal_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'max_renewals' => 'integer',
        'renewals_count' => 'integer',
        'is_active' => 'boolean',
        'renewal_settings' => 'array',
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
     * Get the merchant that owns the subscription.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'ulid');
    }

    /**
     * Get the junkshop that the subscription is directed to.
     */
    public function junkshop()
    {
        return $this->belongsTo(Junkshop::class, 'junkshop_id', 'ulid');
    }

    /**
     * Get the item being subscribed to.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the bids associated with this subscription.
     */
    public function bids()
    {
        return $this->hasMany(Bid::class, 'bid_subscription_id');
    }

    /**
     * Get the renewal history records for this subscription.
     */
    public function renewalHistory()
    {
        return $this->hasMany(BidRenewalHistory::class);
    }

    /**
     * Check if the subscription is due for renewal.
     *
     * @return bool
     */
    public function isDueForRenewal()
    {
        return $this->is_active && 
               $this->next_renewal_date <= Carbon::today() && 
               (!$this->max_renewals || $this->renewals_count < $this->max_renewals) &&
               (!$this->end_date || $this->end_date >= Carbon::today());
    }

    /**
     * Calculate the next renewal date based on the frequency.
     *
     * @param \Carbon\Carbon|null $fromDate
     * @return \Carbon\Carbon
     */
    public function calculateNextRenewalDate(Carbon $fromDate = null)
    {
        $fromDate = $fromDate ?? ($this->next_renewal_date ?? Carbon::today());
        
        switch ($this->frequency) {
            case 'weekly':
                return $fromDate->copy()->addWeek();
            case 'biweekly':
                return $fromDate->copy()->addWeeks(2);
            case 'monthly':
                return $fromDate->copy()->addMonth();
            default:
                return $fromDate->copy()->addMonth();
        }
    }

    /**
     * Update the next renewal date.
     *
     * @return void
     */
    public function updateNextRenewalDate()
    {
        $this->next_renewal_date = $this->calculateNextRenewalDate();
        $this->save();
    }

    /**
     * Increment the renewals count.
     *
     * @return void
     */
    public function incrementRenewalsCount()
    {
        $this->renewals_count++;
        
        // Check if max renewals reached
        if ($this->max_renewals && $this->renewals_count >= $this->max_renewals) {
            $this->is_active = false;
        }
        
        $this->save();
    }

    /**
     * Scope a query to only include active subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include subscriptions due for renewal.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueForRenewal($query)
    {
        return $query->where('is_active', true)
            ->where('next_renewal_date', '<=', Carbon::today())
            ->where(function ($query) {
                $query->whereNull('max_renewals')
                      ->orWhereRaw('renewals_count < max_renewals');
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', Carbon::today());
            });
    }

    /**
     * Get the total value of the subscription.
     *
     * @return float
     */
    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->price_per_kg;
    }
}
