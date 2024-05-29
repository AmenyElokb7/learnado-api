<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Category;
use App\Models\Language;
use App\Models\User;
use Faker\Factory as Faker;
use App\Enum\UserRoleEnum;
use App\Enum\TeachingTypeEnum;
use App\Repositories\Media\MediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        $faker = Faker::create();
        $mediaLinks = [
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-13-5.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-15-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-20.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-19.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-18.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-17-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-16-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-14-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-15-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-12-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/course-1.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/course-2.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/course-3.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/02/course-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-6.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-7.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-8.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-11-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-10-4.jpg',
            'https://dreamslms-wp.dreamstechnologies.com/wp-content/uploads/2023/01/course-9.jpg'
        ];

        $titlesAndDescriptions = [
            ['title' => 'Mastering Angular', 'description' => 'Learn how to build dynamic web applications using Angular, a powerful framework maintained by Google.'],
            ['title' => 'Introduction to Python', 'description' => 'Get started with Python programming. Learn syntax, data types, and basic algorithms in this beginner-friendly course.'],
            ['title' => 'Node.js for Backend Development', 'description' => 'Explore the power of Node.js in building fast and scalable backend applications.'],
            ['title' => 'Docker Development Essentials', 'description' => 'Learn how to containerize applications using Docker and manage them efficiently.'],
            ['title' => 'Swift Programming for iOS', 'description' => 'Dive into iOS app development with Swift, Apple’s modern programming language.'],
            ['title' => 'Advanced React Techniques', 'description' => 'Enhance your React skills with advanced techniques and best practices for building complex applications.'],
            ['title' => 'Machine Learning with Python', 'description' => 'Understand the fundamentals of machine learning and how to implement algorithms using Python.'],
            ['title' => 'Full-Stack JavaScript Development', 'description' => 'Become proficient in both frontend and backend JavaScript technologies.'],
            ['title' => 'Kubernetes for Developers', 'description' => 'Learn how to deploy, scale, and manage containerized applications using Kubernetes.'],
            ['title' => 'Flutter for Mobile Development', 'description' => 'Build beautiful native mobile applications using Flutter, Google’s UI toolkit.'],
            ['title' => 'Data Science with R', 'description' => 'Explore data science concepts and tools with R programming language.'],
            ['title' => 'Cybersecurity Fundamentals', 'description' => 'Learn the basics of cybersecurity, including network security, cryptography, and risk management.'],
            ['title' => 'DevOps with AWS', 'description' => 'Implement DevOps practices using AWS services and automate your infrastructure.'],
            ['title' => 'Artificial Intelligence for Everyone', 'description' => 'Understand the principles of AI and how it’s transforming industries.'],
            ['title' => 'Building APIs with Django', 'description' => 'Create robust APIs using Django REST framework and enhance your backend development skills.'],
            ['title' => 'Cloud Computing with Azure', 'description' => 'Gain expertise in Microsoft Azure cloud services and architecture.'],
            ['title' => 'Game Development with Unity', 'description' => 'Learn to build interactive and engaging games using Unity game engine.'],
            ['title' => 'Big Data Analytics', 'description' => 'Analyze large datasets using Hadoop and Spark technologies.'],
            ['title' => 'Blockchain Development', 'description' => 'Get started with blockchain technology and build decentralized applications.'],
            ['title' => 'Internet of Things (IoT)', 'description' => 'Develop IoT solutions and understand the architecture of connected devices.'],
        ];

        $categories = Category::all()->pluck('id')->toArray();
        $languages = Language::all()->pluck('id')->toArray();
        $facilitators = User::where('role', UserRoleEnum::FACILITATOR->value)->pluck('id')->toArray();
        $students = User::where('role', UserRoleEnum::USER->value)->pluck('id')->toArray();
        $designers = User::where('role', UserRoleEnum::DESIGNER->value)->pluck('id')->toArray();

        if (empty($categories) || empty($languages) || empty($facilitators) || empty($students) || empty($designers)) {
            throw new Exception('Make sure categories, languages, facilitators, students, and designers are seeded before running the CourseSeeder.');
        }

        foreach ($titlesAndDescriptions as $index => $courseInfo) {
            $isPaid = $faker->boolean;
            $isPublic = $faker->boolean;
            $teachingType = $faker->randomElement([TeachingTypeEnum::ONLINE->value, TeachingTypeEnum::ON_A_PLACE->value]);

            $courseData = [
                'title' => $courseInfo['title'],
                'category_id' => $faker->randomElement($categories),
                'description' => $courseInfo['description'],
                'language_id' => $faker->randomElement($languages),
                'is_paid' => $isPaid,
                'price' => $isPaid ? $faker->randomFloat(2, config('constants.CURRENCY_MIN_VALUE'), 1000) : null,
                'discount' => $isPaid ? $faker->optional()->randomFloat(2, config('constants.CURRENCY_MIN_VALUE'), 100) : null,
                'facilitator_id' => $faker->randomElement($facilitators),
                'is_public' => $isPublic,
                'teaching_type' => $teachingType,
                'start_time' => $teachingType ? now()->addDays(1)->timestamp : null,
                'end_time' => $teachingType ?  now()->addDays(1)->timestamp : null ,
                'link' => $teachingType == TeachingTypeEnum::ONLINE->value ? $faker->url : null,
                'latitude' => $teachingType == TeachingTypeEnum::ON_A_PLACE->value ? $faker->latitude : null,
                'longitude' => $teachingType == TeachingTypeEnum::ON_A_PLACE->value ? $faker->longitude : null,
                'has_forum' => $faker->boolean,
                'added_by' => $faker->randomElement($designers),
                'is_active' => $index < 17,
            ];

            $course = Course::create($courseData);

            if (!$isPublic) {
                $selectedUserIds = $faker->randomElements($students, $faker->numberBetween(1, min(count($students), 5)));
                $course->subscribers()->attach($selectedUserIds);
            }

            $mediaLink = $mediaLinks[$index % count($mediaLinks)];
            $contents = file_get_contents($mediaLink);
            $fileName = basename($mediaLink);
            Storage::disk('public')->put($fileName, $contents);
            $filePath = storage_path('app/public/' . $fileName);
            $file = new UploadedFile($filePath, $fileName);

            MediaRepository::attachOrUpdateMediaForModel($course, $file, null, 'courses');
        }
    }
}
