<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'equipment_required',
        'steps',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'steps' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the items that can be verified using this method.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_verification_method');
    }

    /**
     * Scope a query to only include active verification methods.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include methods applicable to a specific item.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $itemId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->whereHas('items', function ($q) use ($itemId) {
            $q->where('items.id', $itemId);
        });
    }
}
