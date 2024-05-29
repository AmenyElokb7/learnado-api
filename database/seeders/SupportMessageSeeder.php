<?php

namespace Database\Seeders;

use App\Enum\UserRoleEnum;
use App\Models\SupportMessage;
use App\Models\User;
use Exception;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SupportMessageSeeder extends Seeder
{
    /**
     * @throws Exception
     */
    public function run()
    {
        $faker = Faker::create();
        $designers = User::where('role', UserRoleEnum::DESIGNER->value)->pluck('id')->toArray();
        $admins = User::where('role', UserRoleEnum::ADMIN->value)->pluck('id')->toArray();

        if (empty($designers) || empty($admins)) {
            throw new Exception('Make sure designers and admins are seeded before running the SupportMessageSeeder.');
        }

        foreach ($designers as $designerId) {
            for ($i = 0; $i < 10; $i++) {
                SupportMessage::create([
                    'user_id' => $designerId,
                    'subject' => $faker->sentence,
                    'message' => $faker->paragraph,
                    'is_read' => $faker->boolean,
                    'created_at' => now()->timestamp,
                    'updated_at' => now()->timestamp,
                ]);
            }
        }
    }
}
