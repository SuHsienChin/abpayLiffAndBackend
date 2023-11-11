<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    protected $table = 'game_categories';

    protected $fillable = [
        'game_name',
        'game_id',
        'game_number',
    ];

    public function gameProducts()
    {
        return $this->hasMany(GameProduct::class, 'game_category_id');
    }
}
