<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory, ApplyQueryScopes;

    protected $fillable = ['language'];


    // course has one language

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
