<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAccount extends Model
{
    protected $table = 'game_account';

    protected $fillable = [
            'customer_id',
            'game_sid',
            'login_account',
            'login_password',
            'login_type',
            'characters',
            'server_name',
            'update_time',
    ];

}
