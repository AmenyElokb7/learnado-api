<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Step extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'duration', 'course_id', 'has_quiz'];

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function deleteWithRelations()
    {
        // Delete all media
        $this->media()->delete();

        // If there's a quiz, delete its questions and answers
        if ($this->quiz) {
            // Delete all answers of each question
            foreach ($this->quiz->questions as $question) {
                $question->answers()->delete();
            }
            // Delete all questions
            $this->quiz->questions()->delete();

            // delete the quiz
            $this->quiz->delete();
        }

        // Delete the step itself
        $this->delete();
    }


}
