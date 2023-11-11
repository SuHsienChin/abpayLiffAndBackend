<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GameCategory;
use App\Models\GameProduct;

class GameProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 100; $i++) {
            GameProduct::create([
                'product_name' => '商品名稱 ' . $i,
                'product_id' => 'product_id_' . $i,
                'product_number' => 'product_number_' . $i,
                'game_category_id' => GameCategory::inRandomOrder()->first()->id,
            ]);
        }
    }
}
