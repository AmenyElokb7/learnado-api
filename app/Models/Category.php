<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['category'];

    // course has one category and category begons to many courses
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

}
