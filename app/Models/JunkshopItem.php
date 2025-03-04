<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JunkshopItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'junkshop_id',
        'item_id',
    ];

    /**
     * Get the junkshop that owns this item.
     */
    public function junkshop()
    {
        return $this->belongsTo(Junkshop::class, 'junkshop_id', 'ulid');
    }

    /**
     * Get the item that this junkshop item references.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
