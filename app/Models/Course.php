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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'category', 'description', 'prerequisites', 'course_for', 'added_at', 'added_by', 'language', 'is_paid', 'price', 'discount', 'facilitator_id', 'isPublic', 'sequential'];

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

}
