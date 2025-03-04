<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the junkshops that offer this item.
     */
    public function junkshops()
    {
        return $this->belongsToMany(Junkshop::class, 'junkshop_items');
    }
}
