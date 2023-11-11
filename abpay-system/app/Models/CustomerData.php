<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerData extends Model
{
    protected $table = 'customer_data';

    protected $fillable = [
        'sid',
        'currency',
        'current_money',
        'name',
        'vip',
        'num5',
        'update_time',
        'line',
    ];
}
