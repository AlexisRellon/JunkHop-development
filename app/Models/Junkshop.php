<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Junkshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid', 'name', 'contact', 'description', 'address', 'user_id',
    ];

    /**
     * Get the items for the junkshop.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'junkshop_items');
    }

    /**
     * Get the user that owns the junkshop.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
