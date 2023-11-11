<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GameCategory;

class GameCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            GameCategory::create([
                'game_name' => '遊戲名稱 ' . $i,
                'game_id' => 'game_id_' . $i,
                'game_number' => 'game_number_' . $i,
            ]);
        }
    }
}
