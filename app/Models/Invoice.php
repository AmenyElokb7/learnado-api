<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    const SELLER_EMAIL = 'learnado@gmail.com';
    const SELLER_NAME = 'Learnado';
    use HasFactory;
    protected $dateFormat = 'U';
    protected $fillable = [
        'username',
        'email',
        'seller_name',
        'seller_email',
        'items',
        'total',
        'payment_id',
        'created_at',
        'updated_at'
    ];
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
   protected $casts = [
        'items' => 'array'
    ];
}
