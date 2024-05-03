<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['quiz_id', 'question', 'type', 'is_valid'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
