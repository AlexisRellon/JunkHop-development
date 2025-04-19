<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quality_verification_id',
        'action',
        'details',
        'performed_by',
    ];

    /**
     * Get the verification that this history entry belongs to.
     */
    public function verification()
    {
        return $this->belongsTo(QualityVerification::class, 'quality_verification_id');
    }

    /**
     * Get the user who performed this action, if available.
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scope a query to only include history entries for a specific action.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include history entries performed by a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePerformedBy($query, $userId)
    {
        return $query->where('performed_by', $userId);
    }
}
