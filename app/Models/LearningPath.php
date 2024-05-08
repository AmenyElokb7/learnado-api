<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningPath extends Model
{
    use HasFactory, SoftDeletes, ApplyQueryScopes;
    protected $dateFormat = 'U';

    protected $fillable = ['title', 'description', 'language_id', 'category_id', 'added_by', 'is_public', 'is_active', 'offline', 'created_at', 'updated_at'];

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

    public function subscribedUsersLearningPath()
    {
        return $this->belongsToMany(User::class, 'learning_path_subscriptions', 'learning_path_id', 'user_id');
    }

    public function scopeByAddedBy($query, $DesignerId)
    {
        if (!$DesignerId) {
            return $query->where('added_by', $DesignerId);
        }
        return $query;
    }

}
