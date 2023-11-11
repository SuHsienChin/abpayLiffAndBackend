<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameProduct extends Model
{
    protected $table = 'game_products';

    protected $fillable = [
        'product_name',
        'product_id',
        'product_number',
        'game_category_id',
    ];

    public function gameCategory()
    {
        return $this->belongsTo(GameCategory::class, 'game_category_id');
    }
}
