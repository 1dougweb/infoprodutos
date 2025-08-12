<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'digital_product_id',
        'mercadopago_payment_id',
        'mercadopago_preference_id',
        'mercadopago_order_id',
        'amount',
        'status',
        'payment_method',
        'paid_at',
        'payer_first_name',
        'payer_last_name',
        'payer_email',
        'payer_identification_type',
        'payer_identification_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function digitalProduct(): BelongsTo
    {
        return $this->belongsTo(DigitalProduct::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
