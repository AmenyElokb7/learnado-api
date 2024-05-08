<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property  string $first_name
 * @property  string $last_name
 * @property  string $email
 * @property  string $password
 * @property  bool $is_valid
 * @property  string $email_verified_at
 * @property  string $remember_token
 * @property  string $created_at
 * @property  string $updated_at
 * @property  string $deleted_at
 * @property  Media $media
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes, ApplyQueryScopes;
    protected $dateFormat= "U";


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'role', 'password', 'is_valid', "created_at", "updated_at"];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }


    public function courses()
    {
        return $this->hasMany(Course::class, 'facilitator_id');
    }

    public function certificates()
    {
        return $this->hasMany(CourseCertificate::class);
    }
    public function cart()
    {
        return $this->belongsToMany(Course::class, 'cart');
    }


    /**
     * @param Builder $query
     * @param string|null $firstName
     * @return Builder
     */

    public function scopeByFirstName(Builder $query, ?string $firstName): Builder
    {
        if (!is_null($firstName)) {
            return $query->where('first_name', 'like', "%$firstName%");
        }
        return $query;
    }

    /**
     * @param Builder $query
     * @param string|null $lastName
     * @return Builder
     */
    public function scopeByLastName(Builder $query, ?string $lastName): Builder
    {
        if (!is_null($lastName)) {
            return $query->where('last_name', 'like', "%$lastName%");
        }
        return $query;
    }

    /**
     * @param Builder $query
     * @param string|null $email
     * @return Builder
     */
    public function scopeByEmail(Builder $query, ?string $email): Builder
    {
        if (!is_null($email)) {
            return $query->where('email', 'like', "%$email%");
        }
        return $query;
    }

    /**
     * @return BelongsToMany
     */
    public function subscribedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_subscription_users', 'user_id', 'course_id');
    }

    public function subscribedLearningPaths()
    {
        return $this->belongsToMany(LearningPath::class, 'learning_path_subscriptions', 'user_id', 'learning_path_id');
    }

    public function scopeByKeyword($query, $keyword)
    {

        if ($keyword) {
            return $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', '%' . $keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }

    public function scopeByRole($query, $role)
    {
        if (!is_null($role)) {
            return $query->whereIn('role', $role);
        }
        return $query;
    }
    public function scopeByIsValid($query, $isValid)
    {
        if (!is_null($isValid)) {
            return $query->where('is_valid', $isValid);
        }
        return $query;
    }


}
