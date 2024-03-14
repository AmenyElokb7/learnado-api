<?php

namespace App\Models;
/**
 * @property string title
 * @property string category
 * @property string description
 * @property string prerequisites
 * @property string course_for
 * @property string added_at
 * @property int added_by
 * @property string language
 * @property string duration
 * @property boolean is_paid
 * @property double price
 * @property double discount
 * @property int facilitator_id
 * @property User admin
 * @property User facilitator
 * @property Media media
 */

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * @property string title
 * @property int category
 * @property string description
 * @property int added_by
 * @property int language
 * @property boolean is_paid
 * @property double price
 * @property double discount
 * @property int facilitator_id
 * @property boolean is_public
 * @property boolean sequential
 * @property boolean is_active
 * @property string teaching_type
 * @property string link
 * @property double latitude
 * @property double longitude
 * @property string start_time
 * @property string end_time
 * @property User admin
 * @property User facilitator
 * @property Media media
 * @property User[] subscribedUsers
 */
class Course extends Model
{
    use SoftDeletes, ApplyQueryScopes, HasFactory, Notifiable;

    protected $fillable = [
        'title',
        'category',
        'description',
        'added_by',
        'language',
        'is_paid',
        'price',
        'discount',
        'facilitator_id',
        'is_public',
        'sequential',
        'is_active',
        'teaching_type',
        'link',
        'latitude',
        'longitude',
        'start_time',
        'end_time',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function admin()
    {
        return $this->belongsTo(User::class);
    }

    public function facilitator()
    {
        return $this->belongsTo(User::class);
    }

    public function learningPaths()
    {
        return $this->belongsToMany(LearningPath::class, 'learning_path_course');
    }

    public function steps()
    {
        return $this->hasMany(Step::class);
    }

    // App\Models\Course.php

    public function subscribedUsers()
    {
        return $this->belongsToMany(User::class, 'course_subscription_users', 'course_id', 'user_id');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'id');
    }

    public function deleteWithRelations()
    {
        // Delete related media
        $this->media()->delete();

        // Delete subscribed users associations

        $this->subscribedUsers()->detach();

        // Delete related steps and their quizzes, questions, and answers
        foreach ($this->steps as $step) {
            $step->deleteWithRelations();
        }


        $this->delete();
    }

    public function scopeByFacilitator($query, $facilitatorId)
    {
        if ($facilitatorId) {
            return $query->where('facilitator_id', $facilitatorId);
        }
        return $query;
    }

    public function scopeByTitle($query, $title)
    {
        if ($title) {
            return $query->where('title', 'like', '%' . $title . '%');
        }
        return $query;
    }

    public function scopeByCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', 'like', '%' . $category . '%');
        }
        return $query;
    }

    public function scopeByLanguage($query, $language)
    {
        if ($language) {
            return $query->where('language', 'like', '%' . $language . '%');
        }
        return $query;
    }

    public function scopeByIsPaid($query, $isPaid)
    {
        if ($isPaid) {
            return $query->where('is_paid', $isPaid);
        }
        return $query;
    }

    public function scopeByPrice($query, $price)
    {
        if ($price) {
            return $query->where('price', 'like', '%' . $price . '%');
        }
        return $query;
    }

    public function scopeByDiscount($query, $discount)
    {
        if ($discount) {
            return $query->where('discount', 'like', '%' . $discount . '%');
        }
        return $query;
    }

    public function scopeByTeachingType($query, $teachingType)
    {
        if ($teachingType) {
            return $query->where('teaching_type', 'like', '%' . $teachingType . '%');
        }
        return $query;
    }

}
