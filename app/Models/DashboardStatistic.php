<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'total_users', 'online_users', 'total_junkshops',
    ];
}
