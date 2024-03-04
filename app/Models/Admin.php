<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property  string $first_name
 * @property  string $last_name
 * @property  string $email
 * @property  string $password
 * @property  string $role
 * @property  bool $is_valid
 * @property  string $email_verified_at
 * @property  string $remember_token
 * @property  string $created_at
 * @property  string $updated_at
 * @property  string $deleted_at
 * @property  Media $media
 * @property  Course $courses
 * @property  Course $teachingCourses
 */
class Admin extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes, ApplyQueryScopes, HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'role', 'is_valid',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

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

    public function refreshTokens()
    {
        return $this->morphMany(RefreshToken::class, 'tokenable');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function teachingCourses()
    {
        return $this->hasMany(Course::class, 'facilitateur_id');
    }

    public function scopeByFirstName(Builder $query, ?string $firstName): Builder
    {
        if (!is_null($firstName)) {
            return $query->where('first_name', 'like', "%$firstName%");
        }
        return $query;
    }

    /**
     * @param Builder $query
     * @param ?string $lastName
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
     * @param string $email
     * @return Builder
     */
    public function scopeByEmail(Builder $query, ?string $email): Builder
    {
        if (!is_null($email)) {
            return $query->where('email', 'like', "%$email%");
        }
        return $query;
    }

}
