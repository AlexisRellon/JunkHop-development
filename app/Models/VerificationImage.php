<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quality_verification_id',
        'image_path',
        'image_type',
        'caption',
    ];

    /**
     * Get the verification that owns the image.
     */
    public function verification()
    {
        return $this->belongsTo(QualityVerification::class, 'quality_verification_id');
    }

    /**
     * Get the full URL for the image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Scope a query to only include images of a certain type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('image_type', $type);
    }

    /**
     * Scope a query to only include before images.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBefore($query)
    {
        return $query->where('image_type', 'before');
    }

    /**
     * Scope a query to only include after images.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAfter($query)
    {
        return $query->where('image_type', 'after');
    }

    /**
     * Scope a query to only include material images.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMaterial($query)
    {
        return $query->where('image_type', 'material');
    }

    /**
     * Scope a query to only include equipment images.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEquipment($query)
    {
        return $query->where('image_type', 'equipment');
    }
}
