<?php

namespace App\Models;

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
 * @property boolean is_offline
 * @property User admin
 * @property User facilitator
 * @property Media media
 * @property User[] subscribers
 */
class Course extends Model
{
    use SoftDeletes, ApplyQueryScopes, HasFactory, Notifiable;
    protected $dateFormat = 'U';

    protected $fillable = [
        'title',
        'category_id',
        'description',
        'added_by',
        'language_id',
        'is_paid',
        'price',
        'discount',
        'facilitator_id',
        'is_public',
        'is_active',
        'is_offline',
        'teaching_type',
        'has_forum',
        'link',
        'latitude',
        'longitude',
        'start_time',
        'end_time',
        'created_at',
        'updated_at',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }
    public function discussion()
    {
        return $this->morphMany(Discussion::class, 'discussable');
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

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'course_subscription_users', 'course_id', 'user_id');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
    public function certificates()
    {
        return $this->hasMany(CourseCertificate::class);
    }
    public function usersInCart()
    {
        return $this->belongsToMany(User::class, 'cart');
    }

    public function deleteWithRelations()
    {
        // Delete related media
        $this->media()->delete();

        // delete the course from cart
        $this->usersInCart()->detach();

        // Delete subscribed users associations

        $this->subscribers()->detach();

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

    public function scopeByAddedBy($query, $DesignerId)
    {
        if (!$DesignerId) {
            return $query->where('added_by', $DesignerId);
        }
        return $query;
    }

    public function scopeByIsPublic($query, $isPublic = null)
    {
        if (!is_null($isPublic)) {
            return $query->where('is_public', $isPublic);
        }
        return $query;
    }

    public function scopeByIsActive($query, $isActive = null)
    {
        if (!is_null($isActive)) {
            return $query->where('is_active', $isActive);
        }
        return $query;
    }
    public function scopeByIsOffline($query, $isOffline = null)
    {
        if (!is_null($isOffline)) {
            return $query->where('is_offline', $isOffline);
        }
        return $query;
    }

    public function scopeByStartTime($query, $startTime)
    {
        if ($startTime) {
            return $query->where('start_time', $startTime);
        }
        return $query;
    }

    public function scopeBySubscribedUser($query, $userId)
    {
        if ($userId) {
            return $query->whereHas('subscribers', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
        }
        return $query;
    }

    public function scopeByKeyWord($query, $keyword)
    {
        if ($keyword) {
            return $query->where(function($query) use ($keyword) {
                $query->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }

    public function scopeByIsPaid($query, $isPaid)
    {
        if ($isPaid !== null) {

            return $query->where('is_paid', $isPaid);
        }
        return $query;
    }

    public function scopeByTeachingType($query, $teachingType)
    {
        if ($teachingType !== null) {
            return $query->where('teaching_type', $teachingType);
        }
        return $query;
    }

    public function scopeByCategory($query, $categorieId)
    {
        if ($categorieId !== null) {
            return $query->where('category_id', $categorieId);
        }
        return $query;
    }


}
