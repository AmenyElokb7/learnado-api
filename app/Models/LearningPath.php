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

    protected $fillable = ['title', 'description', 'language_id', 'category_id', 'added_by', 'is_public', 'is_active', 'price', 'is_offline', 'created_at', 'updated_at'];

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
    public function discussion()
    {
        return $this->morphMany(Discussion::class, 'discussable');
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
    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category_id', $category);
        }
        return $query;
    }
    public function scopeByKeyWord($query, $keyword)
    {
        if ($keyword) {
            return $query->where('title', 'like', '%' . $keyword . '%');
        }
        return $query;
    }
    public function scopeByPrice($query, $price)
    {
        if ($price) {
            return $query->where('price', $price);
        }
        return $query;
    }
    public function scopeByOffline($query, $offline)
    {
        if ($offline) {
            return $query->where('is_offline', 1);
        }
        return $query;
    }
    public function scopeByPublic($query, $public)
    {
        if ($public) {
            return $query->where('is_public', 1);
        }
        return $query;
    }
    public function scopeByActive($query, $active)
    {
        if ($active) {
            return $query->where('is_active', 1);
        }
        return $query;
    }
    public function usersInCart()
    {
        return $this->belongsToMany(User::class, 'cart');
    }

    public function delteWithRelations(){
        // detach courses from learning path , subscribed users, quiz with its questions and answers and media
        $this->courses()->detach();
        $this->subscribedUsersLearningPath()->detach();
        $this->quiz()->delete();
        $this->media()->delete();
        $this->delete();
    }

}
