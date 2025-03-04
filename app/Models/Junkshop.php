<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Junkshop extends Model
{
    use HasFactory, HasUlids;

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
        return $this->hasMany(JunkshopItem::class, 'junkshop_id', 'ulid');
    }

    /**
     * Get the owner of the junkshop.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'ulid');
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
