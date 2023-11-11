<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use app\Models\GameAccount;

class GameAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            GameAccount::create([
                'sid' => 'SID_' . $i,
                'customer_id' => 'CUSTOMER_SID_' . $i,
                'game_sid' => 'GAME_SID_' . $i,
                'login_account' => 'account' . $i,
                'login_password' => 'password' . $i,
                'login_type' => 'Facebook',
                'characters' => 'Character ' . $i,
                'server_name' => 'Server ' . $i,
                'update_time' => now()->format('YmdHis'),
            ]);
        }
    }
}
