<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QualityVerification extends Model
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
        'junkshop_id',
        'item_id',
        'verification_code',
        'status',
        'quantity',
        'grade',
        'purity_level',
        'bid_id',
        'notes',
        'verification_results',
        'verified_at',
        'verified_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'purity_level' => 'decimal:2',
        'verification_results' => 'array',
        'verified_at' => 'datetime',
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
            
            if (empty($model->verification_code)) {
                $model->verification_code = self::generateVerificationCode();
            }
        });
    }

    /**
     * Generate a unique verification code.
     *
     * @return string
     */
    public static function generateVerificationCode()
    {
        $prefix = 'VRF';
        $unique = strtoupper(Str::random(6));
        $code = $prefix . '-' . $unique;
        
        // Ensure code is unique
        while (self::where('verification_code', $code)->exists()) {
            $unique = strtoupper(Str::random(6));
            $code = $prefix . '-' . $unique;
        }
        
        return $code;
    }

    /**
     * Get the merchant that requested this verification.
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'ulid');
    }

    /**
     * Get the junkshop that this verification is for.
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
     * Get the bid associated with this verification.
     */
    public function bid()
    {
        return $this->belongsTo(Bid::class);
    }

    /**
     * Get the verification images.
     */
    public function images()
    {
        return $this->hasMany(VerificationImage::class);
    }

    /**
     * Get the verification history.
     */
    public function history()
    {
        return $this->hasMany(VerificationHistory::class);
    }

    /**
     * Get the quality standard for this verification's grade and item.
     */
    public function qualityStandard()
    {
        return QualityStandard::where('item_id', $this->item_id)
            ->where('grade', $this->grade)
            ->first();
    }

    /**
     * Add a record to verification history.
     *
     * @param string $action
     * @param string|null $details
     * @param string|null $performedBy
     * @return \App\Models\VerificationHistory
     */
    public function addToHistory($action, $details = null, $performedBy = null)
    {
        return $this->history()->create([
            'action' => $action,
            'details' => $details,
            'performed_by' => $performedBy,
        ]);
    }

    /**
     * Check if the verification has passed.
     *
     * @return bool
     */
    public function isPassed()
    {
        return $this->status === 'passed';
    }

    /**
     * Check if the verification has failed.
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the verification is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the verification is in progress.
     *
     * @return bool
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Mark the verification as passed.
     *
     * @param string|null $verifiedBy
     * @param array|null $results
     * @param string|null $notes
     * @return bool
     */
    public function markAsPassed($verifiedBy = null, $results = null, $notes = null)
    {
        $this->status = 'passed';
        $this->verified_at = now();
        $this->verified_by = $verifiedBy;
        
        if ($results) {
            $this->verification_results = $results;
        }
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $saved = $this->save();
        
        if ($saved) {
            $this->addToHistory('marked_as_passed', $notes, $verifiedBy);
        }
        
        return $saved;
    }

    /**
     * Mark the verification as failed.
     *
     * @param string|null $verifiedBy
     * @param array|null $results
     * @param string|null $notes
     * @return bool
     */
    public function markAsFailed($verifiedBy = null, $results = null, $notes = null)
    {
        $this->status = 'failed';
        $this->verified_at = now();
        $this->verified_by = $verifiedBy;
        
        if ($results) {
            $this->verification_results = $results;
        }
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $saved = $this->save();
        
        if ($saved) {
            $this->addToHistory('marked_as_failed', $notes, $verifiedBy);
        }
        
        return $saved;
    }

    /**
     * Mark the verification as in progress.
     *
     * @param string|null $verifiedBy
     * @param string|null $notes
     * @return bool
     */
    public function markAsInProgress($verifiedBy = null, $notes = null)
    {
        $this->status = 'in_progress';
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $saved = $this->save();
        
        if ($saved) {
            $this->addToHistory('marked_as_in_progress', $notes, $verifiedBy);
        }
        
        return $saved;
    }

    /**
     * Scope a query to only include pending verifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include in-progress verifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include passed verifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }

    /**
     * Scope a query to only include failed verifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include verifications for a specific merchant.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $merchantId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Scope a query to only include verifications for a specific junkshop.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $junkshopId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForJunkshop($query, $junkshopId)
    {
        return $query->where('junkshop_id', $junkshopId);
    }
}
