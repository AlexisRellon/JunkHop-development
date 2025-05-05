<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use App\Traits\TracksActivity;

class Junkshop extends Model
{
    use HasFactory, HasUlids, TracksActivity;

    /**
     * Activity type for logging
     */
    const ACTIVITY_TYPE = 'junkshop';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ulid';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'ulid', 'name', 'contact', 'description', 'address', 'user_id',
    ];

    /**
     * Get the items for the junkshop.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'junkshop_items', 'junkshop_id', 'item_id')
            ->withPivot('id')
            ->withTimestamps();
    }

    /**
     * Get the owner of the junkshop.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'ulid');
    }

    /**
     * Get the merchants interested in this junkshop
     */
    public function merchants()
    {
        return $this->belongsToMany(Merchant::class, 'merchant_junkshop', 'junkshop_id', 'merchant_id')
            ->withTimestamps();
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
