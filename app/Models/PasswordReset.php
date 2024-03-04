<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'email', 'token', 'expires_at', 'created_at'
    ];
}
