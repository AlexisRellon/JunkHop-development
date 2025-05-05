<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MaterialVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'merchant_id',
        'junkshop_id',
        'item_id',
        'quantity',
        'price',
        'grade',
        'verified_grade',
        'status',
        'notes',
        'is_high_value'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'is_high_value' => 'boolean',
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
            
            // Automatically mark transactions as high value if price * quantity exceeds threshold
            $totalValue = $model->price * $model->quantity;
            $model->is_high_value = $totalValue >= 5000; // 5000 PHP threshold (about $100 USD)
        });
    }

    /**
     * Get the merchant that owns the verification.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'ulid');
    }

    /**
     * Get the junkshop that owns the verification.
     */
    public function junkshop()
    {
        return $this->belongsTo(Junkshop::class, 'junkshop_id', 'ulid');
    }

    /**
     * Get the item being verified.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get all photos associated with this verification.
     */
    public function photos()
    {
        return $this->hasMany(VerificationPhoto::class, 'verification_id', 'id');
    }
}
