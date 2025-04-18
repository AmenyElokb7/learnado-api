<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;
    protected $dateFormat = 'U';

    const COMPLETED = 1;
    const PENDING = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'stripe_payment_id',
        'amount',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<string>
     */
    protected $hidden = [
        'stripe_payment_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that made the payment.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courses()
    {
        return $this->belongsTo(Course::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}

