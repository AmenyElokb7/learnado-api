<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory,ApplyQueryScopes;
    protected $dateFormat = "U";
    const QUIZ_COOLDOWN_TIME = 120; // 2 hours


    protected $fillable = ['user_id', 'quiz_id', 'score', 'total_score_possible', 'needs_review', 'passed', 'created_at','updated_at' ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // search by quiz section title or section course title
    public function scopeByKeyword($query, $title)
    {
        if($title){
            return
            $query->whereHas('quiz', function($query) use ($title){
                $query->whereHas('steps', function($query) use ($title){
                    $query->where('title','like','%'.$title.'%');
                })->orWhereHas('steps.course', function($query) use ($title){
                    $query->where('title','like','%'.$title.'%');
                });
            });
        }
        return $query;
    }
}
