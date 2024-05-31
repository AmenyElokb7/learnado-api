<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;
    protected $dateFormat = 'U';

    protected $fillable = ['step_id', 'is_exam', 'created_at', 'updated_at'];

    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    public function latestAttempt()
    {
        return $this->hasOne(QuizAttempt::class)->latest();
    }


}
