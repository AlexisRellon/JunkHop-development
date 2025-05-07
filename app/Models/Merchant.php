<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\TracksActivity;

class Merchant extends Model
{
    use HasFactory, TracksActivity;

    /**
     * Activity type for logging
     */
    const ACTIVITY_TYPE = 'merchant';

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
     * Get the items this merchant is interested in.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'merchant_item_interests', 'merchant_id', 'item_id')
            ->withTimestamps();
    }

    /**
     * Get the wanted materials posted by this merchant.
     */
    public function wantedMaterials()
    {
        return $this->hasMany(WantedMaterial::class, 'merchant_id', 'ulid');
    }
}
