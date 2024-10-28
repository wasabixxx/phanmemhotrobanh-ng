<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(YourSeederClass::class);
        $this->call(RolesTableSeeder::class); // Thêm seeder của bạn vào đây
    }
}
