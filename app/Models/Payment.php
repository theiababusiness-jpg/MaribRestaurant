<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'reference',
        'provider',
        'method',
        'status',
        'amount',
        'currency',
        'remote_invoice_id',
        'remote_payment_id',
        'transaction_id',
        'payload',
        'response_payload',
        'paid_at',
        'failed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'response_payload' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function scopeTerminal($query)
    {
        return $query->whereIn('status', ['paid', 'failed', 'cancelled']);
    }
}
