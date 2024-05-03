<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCertificate extends Model
{
    use HasFactory, ApplyQueryScopes;
    protected $fillable = ['user_id', 'course_id', 'certificate_path'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeByKeyword($query, $title)
    {
        return $query->whereHas('course', function ($query) use ($title) {
            $query->where('title', 'like', "%$title%");
        });
    }
}
