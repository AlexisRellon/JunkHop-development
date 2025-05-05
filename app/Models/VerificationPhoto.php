<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size'
    ];

    /**
     * Get the material verification that owns the photo.
     */
    public function verification()
    {
        return $this->belongsTo(MaterialVerification::class, 'verification_id');
    }

    /**
     * Get the URL for the photo.
     */
    public function getUrlAttribute()
    {
        return url('storage/' . $this->file_path);
    }
}
