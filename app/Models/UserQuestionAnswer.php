<?php

namespace App\Models;


use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserQuestionAnswer extends Model
{
    use HasFactory, SoftDeletes, ApplyQueryScopes;
    protected $dateFormat = 'U';

    protected $fillable = [
        'user_id', 'quiz_id', 'question_id', 'answers', 'binary_answer','open_answer',"is_validated","created_at", "updated_at"];

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

