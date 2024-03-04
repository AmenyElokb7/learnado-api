<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::create([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'testadmin@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
            'is_valid' => true,
        ]);
    }
}
