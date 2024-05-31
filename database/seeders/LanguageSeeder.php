<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            ['language' => 'En'],
            ['language' => 'Fr'],
            ['language' => 'Es'],
            ['language' => 'Tr'],
            ['language' => 'De'],
            ['language' => 'It'],
            ['language' => 'Pt'],
            ['language' => 'Ru'],
            ['language' => 'Ar'],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }
    }
}
