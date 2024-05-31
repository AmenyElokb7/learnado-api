<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $dateFormat = 'U';

    protected $fillable = ['quiz_id', 'question', 'type', 'is_valid', 'created_at', 'updated_at'];

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
