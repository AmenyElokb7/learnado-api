<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, ApplyQueryScopes;
    protected $dateFormat = 'U';

    protected $fillable = ['category', 'created_at', 'updated_at'];

    // course has one category and category belongs to many courses
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function learningPaths()
    {
        return $this->hasMany(LearningPath::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }


    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', 'like', '%' . $category . '%');
        }
        return $query;
    }


}
