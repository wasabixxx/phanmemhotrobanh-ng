<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::insert([
            [
                'username' => 'admin',
                'password' => bcrypt('password'),
                'name' => 'Admin',
                'phone' => '0123456789',
                'email' => 'admin@example.com',
                'role_id' => 1, // ID của admin trong bảng roles
                'shift_id' => 1, // ID của ca làm việc trong bảng shifts
            ],
            [
                'username' => 'manager',
                'password' => bcrypt('password'),
                'name' => 'Manager',
                'phone' => '0987654321',
                'email' => 'manager@example.com',
                'role_id' => 2, // ID của manager
                'shift_id' => 2,
            ],
        ]);
    }
}
