<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'business_name',
        'contact',
        'address', 
        'description',
        'user_id'
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
     * Get the user that owns the merchant.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'ulid');
    }

    /**
     * Get the junkshops this merchant is interested in
     */
    public function interestedJunkshops()
    {
        return $this->belongsToMany(Junkshop::class, 'merchant_junkshop_interests', 'merchant_id', 'junkshop_id')
            ->withTimestamps();
    }

    /**
     * Get the materials this merchant is interested in
     */
    public function interestedItems()
    {
        return $this->belongsToMany(Item::class, 'merchant_item_interests', 'merchant_id', 'item_id')
            ->withTimestamps();
    }

    /**
     * Get the junkshops this merchant is connected with.
     */
    public function junkshops()
    {
        return $this->belongsToMany(Junkshop::class, 'merchant_junkshop', 'merchant_id', 'junkshop_id')
            ->withTimestamps();
    }

    /**
     * Get the items this merchant is interested in.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'merchant_item', 'merchant_id', 'item_id')
            ->withTimestamps();
    }
}