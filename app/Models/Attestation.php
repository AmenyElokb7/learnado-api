<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attestation extends Model
{
    use HasFactory, ApplyQueryScopes;

    protected $dateFormat = 'U';
    protected $fillable = [
        'user_id', 'learning_path_id', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
    }
    public function scopeByKeyword($query, $title)
    {
        return $query->whereHas('learningPath', function ($query) use ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        });
    }
}
