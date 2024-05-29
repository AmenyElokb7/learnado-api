<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Repositories\Media\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $categories = [
            ['category' => 'Angular', 'media' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/categories-icon.png'],
            ['category' => 'Python', 'media' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/categories-icon-04.png'],
            ['category' => 'Nodejs', 'media' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/categories-icon-02.png'],
            ['category' => 'Docker Development', 'media' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/categories-icon-01.png'],
            ['category' => 'Swift', 'media' => 'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/categories-icon-03.png'],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create([
                'category' => $categoryData['category']
            ]);

            $mediaLink = $categoryData['media'];
            $contents = file_get_contents($mediaLink);
            $fileName = basename($mediaLink);

            // Save the image to the storage
            Storage::disk('public')->put("media_categories/$fileName", $contents);

            $file = new UploadedFile(storage_path("app/public/media_categories/$fileName"), $fileName, null, null, true);

            // Attach the media to the category
            MediaRepository::attachOrUpdateMediaForModel($category, $file, null, $categoryData['category']);
        }
    }
}
