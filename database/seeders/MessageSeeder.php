<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Message;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $courses = Course::with('subscribers', 'facilitator')->get();
        $messages = [
            'How can I improve my coding skills?',
            'What is the best way to debug code?',
            'Can you provide more resources?',
            'I am having trouble understanding the last topic.',
            'Thank you for the great course!',
            'Could you explain the homework assignment?',
            'What are the best practices for Python?',
            'I need help with the project.',
            'Can we schedule a one-on-one session?',
            'What other courses do you recommend?',
        ];

        foreach ($courses as $course) {
            $facilitatorId = $course->facilitator_id;
            $subscribers = $course->subscribers;

            foreach ($subscribers as $subscriber) {
                foreach ($messages as $msg) {
                    $message = Message::create([
                        'message' => $msg,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ]);

                    DB::table('user_messages')->insert([
                        'sender_id' => $subscriber->id,
                        'receiver_id' => $facilitatorId,
                        'message_id' => $message->id,
                        'is_read' => $faker->boolean,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ]);

                    $reply = Message::create([
                        'message' => $msg,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ]);

                    DB::table('user_messages')->insert([
                        'sender_id' => $facilitatorId,
                        'receiver_id' => $subscriber->id,
                        'message_id' => $reply->id,
                        'is_read' => $faker->boolean,
                        'created_at' => now()->timestamp,
                        'updated_at' => now()->timestamp,
                    ]);
                }
            }
        }
    }
}
