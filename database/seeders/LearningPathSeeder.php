<?php

namespace Database\Seeders;

use App\Enum\UserRoleEnum;
use App\Models\LearningPath;
use App\Models\Language;
use App\Models\Category;
use App\Models\User;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\Question;
use App\Repositories\Media\MediaRepository;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LearningPathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws Exception
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $languages = Language::all();
        $categories = Category::all();
        $designers = User::where('role', UserRoleEnum::DESIGNER->value)->pluck('id')->toArray();
        $courses = Course::all();

        if ($languages->isEmpty() || $categories->isEmpty() || empty($designers) || $courses->isEmpty()) {
            throw new Exception('Make sure languages, categories, designers, and courses are seeded before running the LearningPathSeeder.');
        }

        $learningPaths = [
            [
                'title' => 'Web Development Masterclass',
                'description' => 'Learn the basics of web development and beyond.',
                'price' => 0.00,
                'is_public' => true,
                'is_active' => true,
                'is_offline' => false,
                'has_forum' => true,
                'media_file' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/course/course-04.jpg',
            ],
            [
                'title' => 'Data Science Bootcamp',
                'description' => 'Become a data science expert with hands-on training.',
                'price' => 299.99,
                'is_public' => true,
                'is_active' => true,
                'is_offline' => false,
                'has_forum' => false,
                'media_file' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/course/course-05.jpg',
            ],
            [
                'title' => 'Mobile App Development',
                'description' => 'Master mobile app development for iOS and Android.',
                'price' => 249.99,
                'is_public' => true,
                'is_active' => true,
                'is_offline' => false,
                'has_forum' => false,
                'media_file' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/course/course-08.jpg',
            ],
            [
                'title' => 'Cybersecurity Essentials',
                'description' => 'Learn how to protect systems and networks from cyber threats.',
                'price' => 299.99,
                'is_public' => true,
                'is_active' => true,
                'is_offline' => false,
                'has_forum' => false,
                'media_file' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/course/course-03.jpg',
            ],
            [
                'title' => 'Cloud Computing',
                'description' => 'Get started with cloud computing and AWS.',
                'price' => 199.99,
                'is_public' => true,
                'is_active' => true,
                'is_offline' => false,
                'has_forum' => false,
                'media_file' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/course/course-02.jpg',
            ],
            [
                'title' => 'Machine Learning',
                'description' => 'Learn the fundamentals of machine learning and AI.',
                'price' => 349.99,
                'is_public' => true,
                'is_active' => true,
                'is_offline' => false,
                'has_forum' => false,
                'media_file' => 'https://dreamslms.dreamstechnologies.com/html/assets/img/course/course-07.jpg',
            ],
        ];

        foreach ($learningPaths as $pathData) {
            $language = $languages->random();
            $category = $categories->random();
            $addedById = $faker->randomElement($designers);
            $mediaFile = $pathData['media_file'];
            unset($pathData['media_file']);

            $learningPath = LearningPath::create(array_merge($pathData, [
                'language_id' => $language->id,
                'category_id' => $category->id,
                'added_by' => $addedById,
            ]));

            $filteredCourses = $courses->where('is_public', $pathData['is_public'])
                ->where('price', '<=', $pathData['price'])
                ->values();

            $randomCourseIds = $faker->randomElements($filteredCourses->pluck('id')->toArray(), $faker->numberBetween(1, min($filteredCourses->count(), 5)));
            $learningPath->courses()->attach($randomCourseIds);
            Log::info("Attached courses to learning path: " . implode(", ", $randomCourseIds));

            $fileName = basename($mediaFile);
            $fileContents = @file_get_contents($mediaFile);
            if ($fileContents !== false) {
                $filePath = "media_learning_paths/$fileName";
                Storage::disk('public')->put($filePath, $fileContents);
                $file = new UploadedFile(storage_path("app/public/$filePath"), $fileName, null, null, true);
                MediaRepository::attachOrUpdateMediaForModel($learningPath, $file);
            }

            $quiz = Quiz::create([
                'learning_path_id' => $learningPath->id,
                'is_exam' => true,
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp,
            ]);

            $questionTypes = ['OPEN', 'QCM', 'BINARY'];
            foreach ($questionTypes as $type) {
                $questionData = [
                    'quiz_id' => $quiz->id,
                    'type' => $type,
                    'question' => "Sample question of type $type",
                    'created_at' => now()->timestamp,
                    'updated_at' => now()->timestamp,
                ];

                if ($type === 'BINARY') {
                    $questionData['is_valid'] = $faker->boolean;
                }

                $question = Question::create($questionData);

                if ($type === 'QCM') {
                    for ($i = 0; $i < 4; $i++) {
                        $question->answers()->create([
                            'answer' => "Sample answer $i for question of type $type",
                            'is_valid' => $faker->boolean,
                            'created_at' => now()->timestamp,
                            'updated_at' => now()->timestamp,
                        ]);
                    }
                }
            }
        }
    }
}
