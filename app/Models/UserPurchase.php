<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'digital_product_id',
        'purchased_at',
        'transaction_id',
        'amount',
        'status'
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function digitalProduct(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class);
    }
}
