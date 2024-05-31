<?php

namespace Database\Seeders;

use App\Enum\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Repositories\Media\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $users = [
            [
                'first_name' => 'Ameny',
                'last_name' => 'Elokb',
                'email' => 'ameny.elokb@polytechnicien.tn',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/students/student-01.jpg',
            ],
            [
                'first_name' => 'Mariem',
                'last_name' => 'Saffar',
                'email' => 'mariemsaffar@gmail.com',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/students/student-03.jpg',
            ],
            [
                'first_name' => 'Amal',
                'last_name' => 'Ben Mosbah',
                'email' => 'amalbenmosbah@gmail.com',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => false,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/user6.jpg',
            ],
            [
                'first_name' => 'Dorra',
                'last_name' => 'Chaouch',
                'email' => 'dorrachaouch@gmail.com',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => false,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/user14.jpg',
            ],
            [
                'first_name' => 'Rim',
                'last_name' => 'Zarrouk',
                'email' => 'rimzarrouk@gmail.com',
                'role' => UserRoleEnum::FACILITATOR->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/profile2.jpg',

            ],
            [
                'first_name' => 'Dhia',
                'last_name' => 'Boudhraa',
                'email' => 'boudhraad@gmail.com',
                'role' => UserRoleEnum::FACILITATOR->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/user2-3.jpg',
            ],
            [
                'first_name' => 'Ameny',
                'last_name' => 'Elokb',
                'email' => 'amenyelokb@gmail.com',
                'role' => UserRoleEnum::DESIGNER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/user5.jpg',
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'johndoe@example.com',
                'role' => UserRoleEnum::ADMIN->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/10/avatar-02.jpg',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'janesmith@example.com',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/user3-3.jpg',

            ],
            [
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alicejohnson@example.com',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/profile4.jpg',
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Williams',
                'email' => 'bobwilliams@example.com',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/profile3.jpg',
            ],
            [
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'email' => 'charliebrown@example.com',
                'role' => UserRoleEnum::USER->value,
                'password' => bcrypt('123456aA'),
                'is_valid' => true,
                'profile_picture' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/profile5.jpg',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => $userData['email'],
                'role' => $userData['role'],
                'password' => $userData['password'],
                'is_valid' => $userData['is_valid'],
            ]);

            $mediaLink = $userData['profile_picture'];
            $contents = file_get_contents($mediaLink);
            $fileName = basename($mediaLink);

            // Save the image to the storage
            Storage::disk('public')->put("profile_pictures/$fileName", $contents);

            $file = new UploadedFile(storage_path("app/public/profile_pictures/$fileName"), $fileName, null, null, true);

            MediaRepository::attachOrUpdateMediaForModel($user, $file, null, $userData['first_name'] . ' ' . $userData['last_name']);
        }
    }
}
