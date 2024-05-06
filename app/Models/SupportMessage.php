<?php

namespace App\Models;

use App\Traits\ApplyQueryScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class SupportMessage extends Model
{
    use HasFactory, ApplyQueryScopes;

    protected $fillable = [
        'user_id',
        'message',
        'subject'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public final function scopeBySubject($query, $subject)
    {
        return $query->where('subject', 'like', "%$subject%");
    }
    public function scopeByIsRead($query, $isRead)
    {
        return $query->where('is_read', $isRead);
    }
}
