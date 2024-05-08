<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory, ApplyQueryScopes;
    protected $dateFormat = 'U';

    protected $fillable = ['language', 'created_at', 'updated_at'];


    // course has one language
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function scopeByLanguage($query, $keyword)
    {
        return $query->where('language', 'like', "%$keyword%");
    }
}
