<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        Product::insert([
            ['name' => 'Sản phẩm 1', 'price' => 10000, 'cost_price' => 7000, 'quantity' => 50],
            ['name' => 'Sản phẩm 2', 'price' => 20000, 'cost_price' => 15000, 'quantity' => 30],
        ]);
    }
}
