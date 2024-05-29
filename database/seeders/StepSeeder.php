<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Step;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Media;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Media\MediaRepository;

class StepSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $courses = Course::all();
        $localMediaPaths = [
            'dummy.pdf' => 'dummy.pdf',
            'sample_explain.pdf' => 'sample_explain.pdf',
        ];

        $youtubeLinks = [
            'https://www.youtube.com/watch?v=kqtD5dpn9C8',
            'https://www.youtube.com/watch?v=9OeznAkyQz4',
            'https://www.youtube.com/watch?v=w6hL_dszMxk',
            'https://www.youtube.com/watch?v=UdcPhnNjSEw',
            'https://www.youtube.com/watch?v=pTFZFxd4hOI',
        ];
        $steps = [
            [
                'title' => 'Introduction to Python',
                'description' => 'This step covers the basics of Python programming, including syntax and data types.',
                'duration' => 60,
                'media' => [$localMediaPaths['dummy.pdf'], $youtubeLinks[0]],
                'quiz' => [
                    [
                        'question' => 'Is Python a high-level programming language?',
                        'type' => 'BINARY',
                        'is_valid' => true,
                    ],
                    [
                        'question' => 'Which of the following are Python data types?',
                        'type' => 'QCM',
                        'answers' => [
                            ['answer' => 'Integer', 'is_valid' => true],
                            ['answer' => 'Float', 'is_valid' => true],
                            ['answer' => 'Character', 'is_valid' => false],
                            ['answer' => 'String', 'is_valid' => true],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Advanced Python Concepts',
                'description' => 'This step covers advanced concepts such as object-oriented programming and decorators.',
                'duration' => 90,
                'media' => [$localMediaPaths['sample_explain.pdf'], $youtubeLinks[1]],
                'quiz' => [
                    [
                        'question' => 'Is Python an interpreted language?',
                        'type' => 'BINARY',
                        'is_valid' => true,
                    ],
                    [
                        'question' => 'Which of the following are valid Python decorators?',
                        'type' => 'QCM',
                        'answers' => [
                            ['answer' => '@staticmethod', 'is_valid' => true],
                            ['answer' => '@classmethod', 'is_valid' => true],
                            ['answer' => '@instance_method', 'is_valid' => false],
                            ['answer' => '@property', 'is_valid' => true],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Python for Data Science',
                'description' => 'This step introduces libraries like NumPy and pandas for data analysis.',
                'duration' => 120,
                'media' => [$youtubeLinks[2]],
                'quiz' => [
                    [
                        'question' => 'Does NumPy support multi-dimensional arrays?',
                        'type' => 'BINARY',
                        'is_valid' => true,
                    ],
                    [
                        'question' => 'Which of the following are functions in the pandas library?',
                        'type' => 'QCM',
                        'answers' => [
                            ['answer' => 'DataFrame()', 'is_valid' => true],
                            ['answer' => 'Series()', 'is_valid' => true],
                            ['answer' => 'Panel()', 'is_valid' => false],
                            ['answer' => 'concat()', 'is_valid' => true],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Introduction to Web Development',
                'description' => 'This step introduces the basics of web development using HTML, CSS, and JavaScript.',
                'duration' => 75,
                'media' => [$youtubeLinks[3]],
                'quiz' => [
                    [
                        'question' => 'Is HTML a programming language?',
                        'type' => 'BINARY',
                        'is_valid' => false,
                    ],
                    [
                        'question' => 'Which of the following are CSS properties?',
                        'type' => 'QCM',
                        'answers' => [
                            ['answer' => 'color', 'is_valid' => true],
                            ['answer' => 'font-size', 'is_valid' => true],
                            ['answer' => 'background-color', 'is_valid' => true],
                            ['answer' => 'text-align', 'is_valid' => true],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'JavaScript Fundamentals',
                'description' => 'This step covers the fundamental concepts of JavaScript programming.',
                'duration' => 80,
                'media' => [$youtubeLinks[4]],
                'quiz' => [
                    [
                        'question' => 'Is JavaScript a compiled language?',
                        'type' => 'BINARY',
                        'is_valid' => false,
                    ],
                    [
                        'question' => 'Which of the following are JavaScript data types?',
                        'type' => 'QCM',
                        'answers' => [
                            ['answer' => 'String', 'is_valid' => true],
                            ['answer' => 'Number', 'is_valid' => true],
                            ['answer' => 'Boolean', 'is_valid' => true],
                            ['answer' => 'Float', 'is_valid' => false],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($courses as $course) {
            foreach ($steps as $stepData) {
                $step = Step::create([
                    'title' => $stepData['title'],
                    'description' => $stepData['description'],
                    'duration' => $stepData['duration'],
                    'course_id' => $course->id,
                ]);
                foreach ($stepData['media'] as $mediaLink) {
                    if (str_contains($mediaLink, 'youtube.com')) {
                        Media::create([
                            'file_name' => $mediaLink,
                            'mime_type' => 'video/youtube',
                            'model_id' => $step->id,
                            'model_type' => Step::class,
                            'title' => $stepData['title'],
                        ]);
                    } else {
                        $filePath = storage_path("app/public/media_steps/$mediaLink");
                        if (file_exists($filePath)) {
                            $file = new UploadedFile($filePath, $mediaLink, null, null, true);
                            MediaRepository::attachOrUpdateMediaForModel($step, $file, null, $stepData['title']);
                        }
                    }
                }

                if (isset($stepData['quiz'])) {
                    $quiz = Quiz::create([
                        'step_id' => $step->id,
                        'is_exam' => false,
                    ]);

                    $quizQuestions = array_merge(
                        $stepData['quiz'],
                        $this->generateAdditionalQuestions(28)
                    );

                    foreach ($quizQuestions as $quizQuestionData) {
                        $question = Question::create([
                            'quiz_id' => $quiz->id,
                            'question' => $quizQuestionData['question'],
                            'type' => $quizQuestionData['type'],
                            'is_valid' => $quizQuestionData['type'] === 'BINARY' ? $quizQuestionData['is_valid'] : null,
                        ]);

                        if ($quizQuestionData['type'] === 'QCM') {
                            foreach ($quizQuestionData['answers'] as $answerData) {
                                Answer::create([
                                    'question_id' => $question->id,
                                    'answer' => $answerData['answer'],
                                    'is_valid' => $answerData['is_valid'],
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }

    private function generateAdditionalQuestions($count)
    {
        $faker = Faker::create();
        $questions = [];

        for ($i = 0; $i < $count; $i++) {
            $type = $faker->randomElement(['BINARY', 'QCM']);
            $questionData = [
                'question' => $faker->sentence,
                'type' => $type,
            ];

            if ($type === 'BINARY') {
                $questionData['is_valid'] = $faker->boolean;
            } else {
                $questionData['answers'] = [
                    ['answer' => $faker->word, 'is_valid' => $faker->boolean],
                    ['answer' => $faker->word, 'is_valid' => $faker->boolean],
                    ['answer' => $faker->word, 'is_valid' => $faker->boolean],
                    ['answer' => $faker->word, 'is_valid' => $faker->boolean],
                ];
            }

            $questions[] = $questionData;
        }

        return $questions;
    }
}
