<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderDetail;

class OrderDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 塞入示例資料
        OrderDetail::create([
            'orderId' => 'ORDER_001',
            'gameItems' => 'Item 1, Item 2',
            'gameItemCounts' => 2,
            'updateTime' => now()->format('YmdHis'),
        ]);

        // 可以繼續加入更多資料
    }
}
