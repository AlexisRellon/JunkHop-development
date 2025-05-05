<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QualityStandard extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ulid',
        'item_id',
        'grade',
        'description',
        'criteria',
        'minimum_purity',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'criteria' => 'array',
        'minimum_purity' => 'decimal:2',
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
     * Get the item that this quality standard belongs to.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get all verifications that used this quality standard.
     */
    public function verifications()
    {
        return $this->hasMany(QualityVerification::class, 'grade', 'grade')
            ->where('item_id', $this->item_id);
    }

    /**
     * Scope a query to only include active standards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include standards for a specific item.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $itemId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope a query to only include standards for a specific grade.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $grade
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    /**
     * Check if a given purity level meets this standard.
     *
     * @param float $purityLevel
     * @return bool
     */
    public function meetsPurityRequirement($purityLevel)
    {
        if ($this->minimum_purity === null) {
            return true;
        }

        return $purityLevel >= $this->minimum_purity;
    }
}
