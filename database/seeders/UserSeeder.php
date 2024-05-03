<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {

            User::create([
                'first_name' => 'Ameny',
                'last_name' => 'Elokb',
                'email' => 'ameny.elokb@polytechnicien.tn',
                'role' => 0,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,

            ]);
            User::create([
            'first_name' => 'Mariem',
            'last_name' => 'Saffar',
            'email' => 'mariemsaffar@gmail.com',
            'role' => 0,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

            ]);
        User::create([
            'first_name' => 'Amal',
            'last_name' => 'Ben Mosbah',
            'email' => 'amalbenmosbah@gmail.com',
            'role' => 0,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);
        User::create([
            'first_name' => 'Dorra',
            'last_name' => 'Chaouch',
            'email' => 'dorrachaouch@gmail.com',
            'role' => 0,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);
            User::create([
            'first_name' => 'Rim',
            'last_name' => 'Zarrouk',
            'email' => 'rimzarrouk@gmail.com',
            'role' => 2,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);

        User::create([
            'first_name' => 'Dhia',
            'last_name' => 'Boudhraa',
            'email' => 'boudhraad@gmail.com',
            'role' => 2,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);
        User::create([
            'first_name' => 'Ameny',
            'last_name' => 'Elokb',
            'email' => 'amenyelokb@gmail.com',
            'role' => 3,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);



    }
}
