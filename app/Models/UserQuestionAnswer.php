<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserQuestionAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'quiz_id', 'question_id', 'answers', 'binary_answer', 'open_answer'];

    protected $casts = [
        'answers' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}

