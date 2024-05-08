<?php

namespace Database\Seeders;

use App\Enum\UserRoleEnum;
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
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,

            ]);
            User::create([
            'first_name' => 'Mariem',
            'last_name' => 'Saffar',
            'email' => 'mariemsaffar@gmail.com',
            'role' => UserRoleEnum::USER->value,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

            ]);
        User::create([
            'first_name' => 'Amal',
            'last_name' => 'Ben Mosbah',
            'email' => 'amalbenmosbah@gmail.com',
            'role' => UserRoleEnum::USER->value,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);
        User::create([
            'first_name' => 'Dorra',
            'last_name' => 'Chaouch',
            'email' => 'dorrachaouch@gmail.com',
            'role' => UserRoleEnum::USER->value,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);
            User::create([
            'first_name' => 'Rim',
            'last_name' => 'Zarrouk',
            'email' => 'rimzarrouk@gmail.com',
            'role' => UserRoleEnum::FACILITATOR->value,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);

        User::create([
            'first_name' => 'Dhia',
            'last_name' => 'Boudhraa',
            'email' => 'boudhraad@gmail.com',
            'role' => UserRoleEnum::FACILITATOR->value,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);
        User::create([
            'first_name' => 'Ameny',
            'last_name' => 'Elokb',
            'email' => 'amenyelokb@gmail.com',
            'role' => UserRoleEnum::DESIGNER->value,
            'password' => bcrypt('123456aA'),
            'is_valid' => true,

        ]);



    }
}
