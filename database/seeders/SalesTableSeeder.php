<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;

class SalesTableSeeder extends Seeder
{
    public function run()
    {
        // Thêm các bản ghi mẫu cho bảng sales
        Sale::insert([
            [
                'product_id' => 1, // Đảm bảo rằng product_id = 1 tồn tại trong bảng products
                'user_id' => 1, // Đảm bảo rằng user_id = 1 tồn tại trong bảng users
                'shift_id' => 1, // Đảm bảo rằng shift_id = 1 tồn tại trong bảng shifts
                'quantity' => 5,
                'total_price' => 50000,
                'profit' => 15000,
            ],
            [
                'product_id' => 2, // Đảm bảo rằng product_id = 2 tồn tại trong bảng products
                'user_id' => 2, // Đảm bảo rằng user_id = 2 tồn tại trong bảng users
                'shift_id' => 2, // Đảm bảo rằng shift_id = 2 tồn tại trong bảng shifts
                'quantity' => 2,
                'total_price' => 40000,
                'profit' => 10000,
            ],
        ]);
    }
}
