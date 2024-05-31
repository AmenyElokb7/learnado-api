<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Model
{

    use HasFactory, SoftDeletes;

    protected $dateFormat = 'U';

    protected $fillable = ['discussable_id', 'discussable_type', 'user_id', 'message', 'created_at', 'updated_at'];

    public function model()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
