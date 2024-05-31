<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\LearningPath;
use App\Models\Discussion;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $courses = Course::with('subscribers', 'facilitator')->get();
        $learningPaths = LearningPath::with('subscribedUsersLearningPath', 'courses.facilitator')->get();

        $messages = [
            'I have a question about the last lecture.',
            'Can anyone explain the assignment?',
            'Great course so far!',
            'I am having trouble with the project.',
            'Is there a study group for this course?',
        ];

        foreach ($courses as $course) {
            $facilitatorId = $course->facilitator_id;
            $subscribers = $course->subscribers;

            // Create discussions for each subscriber
            foreach ($subscribers as $subscriber) {
                foreach ($messages as $msg) {
                    Discussion::create([
                        'discussable_id' => $course->id,
                        'discussable_type' => Course::class,
                        'user_id' => $subscriber->id,
                        'message' => $msg,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ]);

                    // Facilitator response
                    Discussion::create([
                        'discussable_id' => $course->id,
                        'discussable_type' => Course::class,
                        'user_id' => $facilitatorId,
                        'message' => $faker->sentence,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ]);
                }
            }
        }

        // Seed discussions for learning paths
        foreach ($learningPaths as $learningPath) {
            $facilitators = $learningPath->courses->pluck('facilitator_id')->unique();
            $subscribers = $learningPath->subscribedUsersLearningPath;

            // Create discussions for each subscriber
            foreach ($subscribers as $subscriber) {
                foreach ($messages as $msg) {
                    Discussion::create([
                        'discussable_id' => $learningPath->id,
                        'discussable_type' => LearningPath::class,
                        'user_id' => $subscriber->id,
                        'message' => $msg,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ]);

                    // Facilitator responses
                    foreach ($facilitators as $facilitatorId) {
                        Discussion::create([
                            'discussable_id' => $learningPath->id,
                            'discussable_type' => LearningPath::class,
                            'user_id' => $facilitatorId,
                            'message' => $faker->sentence,
                            'created_at' => now()->timestamp,
                            'updated_at' => now()->timestamp,
                        ]);
                    }
                }
            }
        }
    }
}
