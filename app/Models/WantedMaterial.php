<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WantedMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'merchant_id',
        'item_id',
        'quantity',
        'grade',
        'desired_price',
        'description',
        'expiry_date',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'desired_price' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
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
     * Get the merchant that owns this wanted material.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'ulid');
    }

    /**
     * Get the item that this wanted material references.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
