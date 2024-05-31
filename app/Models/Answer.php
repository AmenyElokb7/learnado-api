<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use HasFactory, SoftDeletes;
    protected $dateFormat = 'U';

    protected $fillable = ['question_id', 'answer', 'is_valid', 'created_at', 'updated_at'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
