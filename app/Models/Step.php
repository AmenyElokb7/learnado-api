<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Step extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'duration', 'course_id'];

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
