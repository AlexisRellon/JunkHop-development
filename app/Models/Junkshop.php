<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Junkshop extends Model
{
    use HasFactory, HasUlids;

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

    /**
     * Find a junkshop by its ULID
     *
     * @param string $ulid
     * @return static|null
     */
    public static function findByUlid(string $ulid)
    {
        return static::where('ulid', $ulid)->first();
    }
}
