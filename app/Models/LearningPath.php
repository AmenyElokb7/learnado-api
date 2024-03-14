<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningPath extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'language_id', 'category_id', 'added_by'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'learning_path_course');
    }

    public function quiz()
    {
        return $this->hasOne(Quiz::class);
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

}
