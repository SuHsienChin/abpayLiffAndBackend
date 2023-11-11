<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use app\Models\CustomerData;

class CustomerDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        CustomerData::create([
            'sid' => '12345',
            'currency' => 'USD',
            'current_money' => 1000.00,
            'name' => 'John Doe',
            'vip' => 'Gold',
            'num5' => '54321',
            'update_time' => now()->format('YmdHis'),
            'line' => 'john_doe_line_id',
        ]);
    }
}
