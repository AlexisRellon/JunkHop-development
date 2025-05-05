<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WantedMaterial extends Model
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
        'item_id',
        'quantity',
        'desired_price',
        'grade',
        'description',
        'deadline',
        'is_public',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'desired_price' => 'decimal:2',
        'deadline' => 'date',
        'is_public' => 'boolean',
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
     * Get the merchant that owns the wanted material listing.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'ulid');
    }

    /**
     * Get the related item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope query to only include active listings.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only include public listings.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope query to only include listings that haven't reached their deadline.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where(function($query) {
            $query->whereNull('deadline')
                  ->orWhere('deadline', '>=', now()->toDateString());
        });
    }

    /**
     * Find a wanted material by its ULID
     *
     * @param string $ulid
     * @return static|null
     */
    public static function findByUlid(string $ulid)
    {
        return static::where('ulid', $ulid)->first();
    }
}
