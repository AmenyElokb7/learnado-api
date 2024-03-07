<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {


        for ($i = 0; $i < 10; $i++) {
            User::create([
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'testuser' . $i . '@example.com',
                'role' => 0,
                'password' => bcrypt('12345678'),
                'is_valid' => false,

            ]);
        }
    }
}
