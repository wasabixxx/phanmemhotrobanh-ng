<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftsTableSeeder extends Seeder
{
    public function run()
    {
        Shift::insert([
            ['name' => 'Ca Sáng', 'start_time' => '08:00:00', 'end_time' => '12:00:00'],
            ['name' => 'Ca Chiều', 'start_time' => '13:00:00', 'end_time' => '17:00:00'],
            ['name' => 'Ca Tối', 'start_time' => '18:00:00', 'end_time' => '22:00:00'],
        ]);
    }
}
