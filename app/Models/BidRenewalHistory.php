<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BidRenewalHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ulid',
        'bid_subscription_id',
        'new_bid_ulid',
        'renewal_date',
        'status',
        'status_details',
        'renewal_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'renewal_date' => 'date',
        'renewal_data' => 'array',
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
     * Get the subscription this renewal history belongs to.
     */
    public function subscription()
    {
        return $this->belongsTo(BidSubscription::class, 'bid_subscription_id');
    }

    /**
     * Get the new bid created from this renewal.
     */
    public function newBid()
    {
        return $this->belongsTo(Bid::class, 'new_bid_ulid', 'ulid');
    }

    /**
     * Scope to get successful renewals.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope to get failed renewals.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get skipped renewals.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }
}
