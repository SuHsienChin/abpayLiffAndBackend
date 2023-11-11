<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 塞入示例資料
        Order::create([
            'orderId' => 'ORDER_001',
            'gameAccountSid' => 1, // 遊戲帳號的 ID
            'customerSid' => 1, // 客戶的 ID
            'updateTime' => now()->format('YmdHis'),
        ]);

        // 可以繼續加入更多資料
    }
}
