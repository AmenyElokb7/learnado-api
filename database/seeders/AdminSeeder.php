<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'testadmin@example.com',
            'password' => bcrypt('123456aA'),
            'role' => 1,
            'is_valid' => true,
        ]);
    }
}
