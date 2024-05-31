<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'email', 'token', 'expires_at', 'created_at'
    ];
    protected $dateFormat = 'U';
}
